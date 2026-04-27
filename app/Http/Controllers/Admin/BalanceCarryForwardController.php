<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterOpeningBalance;
use App\Models\BankAccount;
use App\Models\Committee;
use App\Models\HulayatiMaster;
use App\Models\MachineryMaster;
use App\Models\LoanMaster;
use App\Models\FactoryHeadMaster;
use App\Models\DiscountMaster;
use App\Models\SalaryMaster;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\SalesAgent;
use App\Models\PurchaseAgent;
use Illuminate\Support\Facades\DB;

class BalanceCarryForwardController extends Controller
{
    public function carryForward(Request $request)
    {
        $financialYear = MasterOpeningBalance::getCurrentFinancialYear();
        
        // Check if already carried forward for this year
        $exists = \App\Models\CarryForwardLog::where('financial_year', $financialYear)->exists();
        if ($exists) {
            return redirect()->back()->withError("Balances have already been carried forward for the Financial Year $financialYear. This operation can only be performed once per year.");
        }
        
        try {
            DB::beginTransaction();

            $masters = [
                'bank_account' => BankAccount::class,
                'committee' => Committee::class,
                'hulayati' => HulayatiMaster::class,
                'machinery' => MachineryMaster::class,
                'loan' => LoanMaster::class,
                'factory_head' => FactoryHeadMaster::class,
                'discount' => DiscountMaster::class,
                'salary' => SalaryMaster::class,
                'vendor' => Vendor::class,
                'customer' => MasterCustomer::class,
            ];

            foreach ($masters as $type => $modelClass) {
                $records = $modelClass::where('status', '!=', 3)->get();
                foreach ($records as $record) {
                    MasterOpeningBalance::updateOrCreate(
                        [
                            'master_type' => $type,
                            'master_id' => $record->id,
                            'financial_year' => $financialYear,
                        ],
                        [
                            'amount' => abs($record->balance ?? 0),
                            'balance_type' => ($record->balance >= 0) ? 'Credit' : 'Debit',
                        ]
                    );
                }
            }

            // Handle Sales Agents (Balance is sum of shops)
            $salesAgents = SalesAgent::where('status', '!=', 3)->withSum('shops as total_balance', 'balance')->get();
            foreach ($salesAgents as $agent) {
                $balance = $agent->total_balance ?? 0;
                MasterOpeningBalance::updateOrCreate(
                    [
                        'master_type' => 'sales_agent',
                        'master_id' => $agent->id,
                        'financial_year' => $financialYear,
                    ],
                    [
                        'amount' => abs($balance),
                        'balance_type' => ($balance >= 0) ? 'Credit' : 'Debit',
                    ]
                );
            }

            // Handle Purchase Agents (Balance is sum of vendors)
            $purchaseAgents = PurchaseAgent::where('status', '!=', 3)->withSum('vendors as total_balance', 'balance')->get();
            foreach ($purchaseAgents as $agent) {
                $balance = $agent->total_balance ?? 0;
                MasterOpeningBalance::updateOrCreate(
                    [
                        'master_type' => 'purchase_agent',
                        'master_id' => $agent->id,
                        'financial_year' => $financialYear,
                    ],
                    [
                        'amount' => abs($balance),
                        'balance_type' => ($balance >= 0) ? 'Credit' : 'Debit',
                    ]
                );
            }

            // Log the carry forward
            \App\Models\CarryForwardLog::create([
                'financial_year' => $financialYear,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->back()->withSuccess("Balances carried forward successfully for Financial Year $financialYear.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withError("Error carrying forward balances: " . $e->getMessage());
        }
    }
}
