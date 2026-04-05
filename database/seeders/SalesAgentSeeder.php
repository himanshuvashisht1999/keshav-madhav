<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SalesAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SalesAgent::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $agents = [
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sales@snapkid.com',
                'phone' => '9876543210',
                'password' => 'password', // setPasswordAttribute will hash this
                'address' => 'Andheri East, Mumbai, Maharashtra 400069',
                'status' => 1,
            ],
            [
                'name' => 'Anjali Gupta',
                'email' => 'anjali.sales@snapkid.com',
                'phone' => '9123456789',
                'password' => 'password',
                'address' => 'Salt Lake City, Kolkata, West Bengal 700091',
                'status' => 1,
            ],
            [
                'name' => 'Vikram Singh',
                'email' => 'vikram.sales@snapkid.com',
                'phone' => '9988776655',
                'password' => 'password',
                'address' => 'Gurugram Sector 14, Haryana 122001',
                'status' => 1,
            ],
        ];

        foreach ($agents as $agent) {
            SalesAgent::create($agent);
        }
    }
}
