<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data (optional but recommended for a clean start as requested)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Vendor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $vendors = [
            [
                'sno' => 1,
                'sku' => 'VEND-ARV-001',
                'name' => 'Arvind Mills Ltd.',
                'phone' => '07922130805',
                'email' => 'contact@arvind.com',
                'description' => 'Leading producer of high-quality denim fabric and garments.',
                'address' => 'Naroda Road, Ahmedabad, Gujarat 380025, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 2,
                'sku' => 'VEND-VAR-002',
                'name' => 'Vardhman Textiles',
                'phone' => '01612228943',
                'email' => 'sales@vardhman.com',
                'description' => 'Specializes in high-quality yarns and processed fabrics.',
                'address' => 'Chandigarh Road, Ludhiana, Punjab 141010, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 3,
                'sku' => 'VEND-RAY-003',
                'name' => 'Raymond Luxury Cottons',
                'phone' => '02261527000',
                'email' => 'support@raymond.in',
                'description' => 'Premium cotton fabric manufacturer for high-end apparel.',
                'address' => 'New Hind House, Narottam Morarjee Marg, Ballard Estate, Mumbai, Maharashtra 400001, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 4,
                'sku' => 'VEND-SAN-004',
                'name' => 'Sangam India Ltd.',
                'phone' => '01482245400',
                'email' => 'info@sangamgroup.com',
                'description' => 'One of the largest producers of PV suiting and denim fabric.',
                'address' => 'Atun, Chittorgarh Road, Bhilwara, Rajasthan 311001, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 5,
                'sku' => 'VEND-SIY-005',
                'name' => 'Siyaram Silk Mills',
                'phone' => '02224934121',
                'email' => 'queries@siyaram.com',
                'description' => 'Renowned for branded fabrics and premium textiles.',
                'address' => 'H-3/2, MIDC, Tarapur, Boisar, Dist. Palghar, Maharashtra 401506, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 6,
                'sku' => 'VEND-NAN-006',
                'name' => 'Nandan Denim Ltd.',
                'phone' => '07926442661',
                'email' => 'nandan@chiripalgroup.com',
                'description' => 'Major denim manufacturer with a focus on trendy designs.',
                'address' => 'Chiripal House, Shivranjani Cross Roads, Satellite, Ahmedabad, Gujarat 380015, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 7,
                'sku' => 'VEND-SOM-007',
                'name' => 'Soma Textiles & Industries',
                'phone' => '07922113200',
                'email' => 'info@somatextiles.com',
                'description' => 'Trusted provider of high-grade indigo denim and shirting.',
                'address' => 'Rakhial Road, Ahmedabad, Gujarat 380023, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
            [
                'sno' => 8,
                'sku' => 'VEND-MAH-008',
                'name' => 'Mahasagar Textiles',
                'phone' => '02612345678',
                'email' => 'orders@mahasagar.com',
                'description' => 'Bulk distributor and trader of high-quality denim fabrics.',
                'address' => 'Ring Road, Surat, Gujarat 395002, India',
                'items' => serialize([0]),
                'status' => 1,
            ],
        ];


        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }
}
