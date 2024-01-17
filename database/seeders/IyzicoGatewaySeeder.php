<?php

namespace Database\Seeders;

use App\Models\Gateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IyzicoGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Gateway::create([
            'title' => 'iyzico',
            'slug' => 'iyzico',
            'image' => 'assets/images/gateway-icon/payiyzico.png',
            'footerimg' => 'assets/images/gateway-icon/logoband.png'
        ]);
    }
}
