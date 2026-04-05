<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterCustomer;
use App\Models\SalesAgent;
use Illuminate\Support\Facades\DB;

class MasterCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterCustomer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $agentIds = SalesAgent::pluck('id')->toArray();

        $customers = [
            [
                'name' => 'Vardhaman Garments',
                'email' => 'contact@vardhamangarments.in',
                'phone' => '01615024101',
                'address' => 'GT Road, Ludhiana, Punjab 141001',
                'status' => 1,
            ],
            [
                'name' => 'Fashion Hub',
                'email' => 'fashionhub.mumbai@gmail.com',
                'phone' => '02226488776',
                'address' => 'Linking Road, Bandra West, Mumbai, Maharashtra 400050',
                'status' => 1,
            ],
            [
                'name' => 'Surana Textiles',
                'email' => 'orders@suranatextiles.com',
                'phone' => '01412356890',
                'address' => 'Johari Bazar, Jaipur, Rajasthan 302003',
                'status' => 1,
            ],
            [
                'name' => 'Mittal Cloth House',
                'email' => 'mittal.cloth@rediffmail.com',
                'phone' => '01123912345',
                'address' => 'Chandni Chowk, Delhi 110006',
                'status' => 1,
            ],
            [
                'name' => 'Rajeshwar Readymade Store',
                'email' => 'rajeshwar.ahmedabad@yahoo.com',
                'phone' => '07925354567',
                'address' => 'Ratanpole, Ahmedabad, Gujarat 380001',
                'status' => 1,
            ],
            [
                'name' => 'Krishna Fashion Mart',
                'email' => 'krishnafashion@gmail.com',
                'phone' => '02612334455',
                'address' => 'Ring Road Market, Surat, Gujarat 395002',
                'status' => 1,
            ],
            [
                'name' => 'Liberty Garments',
                'email' => 'care@libertygarments.com',
                'phone' => '08022233445',
                'address' => 'Commercial Street, Bangalore, Karnataka 560001',
                'status' => 1,
            ],
            [
                'name' => 'New Selection Store',
                'email' => 'selectionstore_kolkata@hotmail.com',
                'phone' => '03322445566',
                'address' => 'Burrabazar, Kolkata, West Bengal 700007',
                'status' => 1,
            ],
            [
                'name' => 'Standard Jeans Corner',
                'email' => 'standardjeans_pune@gmail.com',
                'phone' => '02024456789',
                'address' => 'Laxmi Road, Pune, Maharashtra 411002',
                'status' => 1,
            ],
            [
                'name' => 'A-One Traders',
                'email' => 'aonetraders.indore@gmail.com',
                'phone' => '07312456781',
                'address' => 'Cloth Market, Indore, Madhya Pradesh 452002',
                'status' => 1,
            ],
        ];

        foreach ($customers as $index => $customer) {
            $customer['sno'] = $index + 1;
            $customer['sku'] = 'CUST-' . strtoupper(substr($customer['name'], 0, 3)) . '-' . (100 + $index);
            // Randomly assign a sales agent if available
            if (!empty($agentIds)) {
                $customer['sales_agent_id'] = $agentIds[array_rand($agentIds)];
            }
            MasterCustomer::create($customer);
        }
    }
}
