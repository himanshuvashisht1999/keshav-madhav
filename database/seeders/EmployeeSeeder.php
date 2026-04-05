<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Employee::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $employees = [
            [
                'name' => 'Amit Verma',
                'phone' => '9812345678',
                'address' => 'Phase 1, Industrial Area, Ludhiana, Punjab 141003',
                'status' => 1,
            ],
            [
                'name' => 'Sita Devi',
                'phone' => '9823456789',
                'address' => 'Model Town, Ludhiana, Punjab 141002',
                'status' => 1,
            ],
            [
                'name' => 'Rajesh Kumar',
                'phone' => '9834567890',
                'address' => 'Shastri Nagar, Ludhiana, Punjab 141001',
                'status' => 1,
            ],
            [
                'name' => 'Priya Singh',
                'phone' => '9845678901',
                'address' => 'Civil Lines, Ludhiana, Punjab 141001',
                'status' => 1,
            ],
            [
                'name' => 'Mohit Malhotra',
                'phone' => '9856789012',
                'address' => 'Saraba Nagar, Ludhiana, Punjab 141001',
                'status' => 1,
            ],
            [
                'name' => 'Sunita Iyer',
                'phone' => '9867890123',
                'address' => 'Haibowal Kalan, Ludhiana, Punjab 141001',
                'status' => 1,
            ],
            [
                'name' => 'Deepak Chouhan',
                'phone' => '9878901234',
                'address' => 'Dugri, Ludhiana, Punjab 141013',
                'status' => 1,
            ],
            [
                'name' => 'Rina Parekh',
                'phone' => '9889012345',
                'address' => 'BRS Nagar, Ludhiana, Punjab 141012',
                'status' => 1,
            ],
            [
                'name' => 'Arjun Reddy',
                'phone' => '9890123456',
                'address' => 'Focal Point, Ludhiana, Punjab 141010',
                'status' => 1,
            ],
            [
                'name' => 'Kunal Gupta',
                'phone' => '9901234567',
                'address' => 'Miller Ganj, Ludhiana, Punjab 141003',
                'status' => 1,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
