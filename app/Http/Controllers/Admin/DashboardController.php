<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService as Service;
use App\Models\User;

class DashboardController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function dashboard(Request $request)
    {

        $year = $request->input('year') ?? date('Y');

        $total_users = User::whereYear('created_at', $year)->count();


        // Grouped data for chart
        $chartData = [
            'users' => User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', $year)
                ->groupBy('month')->pluck('count', 'month')->toArray(),
        ];

        // Pending Payments Totals
        $agentOrders = \App\Models\AgentOrder::get()->filter(function ($order) {
            return $order->balance_amount > 0;
        });
        $corporateDispatches = \App\Models\OrderDispatch::whereHas('orderMain', function ($q) {
            $q->where('order_type', 'corporate');
        })->get()->filter(function ($dispatch) {
            return $dispatch->balance_amount > 0;
        });
        $total_receivable = $agentOrders->sum('balance_amount') + $corporateDispatches->sum('balance_amount');

        $fabricReceipts = \App\Models\FabricReceipt::get()->filter(function ($receipt) {
            return $receipt->balance_amount > 0;
        });
        $total_payable = $fabricReceipts->sum('balance_amount');

        return view('admin.dashboard', compact(
            'total_users',
            'year',
            'chartData',
            'total_receivable',
            'total_payable'
        ));
    }

    public function dashboard1(Request $request)
    {

        $accessToken = 'EAFdjyjFcJZAUBP9NIvna2pGX0Q7DH2huFpl9aLkeRyePWXfqvrfFWoU0McdKi3iojSYZArs40ZCvBShJsgyUn7SyZBZAZC0MLqCbJger1qWHbqoJ0XF5ymlKYDYdVsCHVMBs7OaLKGZAtcQhAiZCbgzmnUT3PESKZAMUXLrm3ZBQXI0t2QcGAR9rFNC095a3ZCf0ZCxQggZDZD'; // Replace with your access token
        $phoneNumberId = '661493770388470';
        $apiVersion = 'v22.0';
        $userPhone = '918839146038'; // e.g. 919876543210

        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";

        $data = [
            "messaging_product" => "whatsapp",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => "hello_world",
                "language" => [
                    "code" => "en_US"
                ]
            ]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        dd($response);
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            echo "HTTP Status Code: " . $httpcode . "\n";
            echo "Response: " . $response . "\n";
        }

        curl_close($ch);
        dd($response);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }

        curl_close($response);

    }

    public function downloadDatabase()
    {
        $fileName = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';

        $headers = [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->stream(function () {
            // Disable strict mode for export if necessary
            \DB::statement("SET SESSION SQL_MODE=''");
            
            $tables = \DB::select('SHOW TABLES');
            $tables = array_map('current', json_decode(json_encode($tables), true));

            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                echo "-- --------------------------------------------------------\n";
                echo "-- Table structure for `$table`\n";
                echo "-- --------------------------------------------------------\n";
                
                try {
                    $createTable = \DB::select("SHOW CREATE TABLE `$table`");
                    $createSql = json_decode(json_encode($createTable[0]), true);
                    $createSql = array_values($createSql)[1];
                    echo "DROP TABLE IF EXISTS `$table`;\n";
                    echo $createSql . ";\n\n";

                    // Fetch data using unbuffered query to save memory
                    $pdo = \DB::connection()->getPdo();
                    $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                    $stmt = $pdo->prepare("SELECT * FROM `$table`");
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $keys = array_keys($row);
                        $values = array_map(function ($value) {
                            if (is_null($value)) return 'NULL';
                            $value = str_replace(["\r", "\n"], ["\\r", "\\n"], addslashes($value));
                            return "'" . $value . "'";
                        }, array_values($row));
                        
                        echo "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $values) . ");\n";
                    }
                    $stmt->closeCursor();
                    $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                    echo "\n\n";
                } catch (\Exception $e) {
                    echo "-- Error exporting table $table: " . $e->getMessage() . "\n\n";
                }
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, 200, $headers);
    }

    public function getDashboardData(Request $request)
    {
        $fabricStock = $this->service->fabricStock($request)->original;
        $itemStock = $this->service->itemStock($request)->original;

        return response()->json([
            'itemStock' => [
                'labels' => $itemStock['labels'],   // Original SKUs for tooltip
                'data' => $itemStock['data']
            ],
            'fabricStock' => [
                'labels' => $fabricStock['labels'], // Original SKUs for tooltip
                'data' => $fabricStock['data']
            ]
        ]);
    }


}