<?php

namespace Database\Seeders;

use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [

            // 🇦🇪 United Arab Emirates

            [
                'name' => 'Jebel Ali Port',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'port_code' => 'JEA',
                'unlocode' => 'AEJEA',
                'timezone' => 'Asia/Dubai',
                'latitude' => 25.0118,
                'longitude' => 55.0617,
                'is_gulf' => true,
                'is_uae' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Khalifa Port',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'port_code' => 'KHL',
                'unlocode' => 'AEKHL',
                'timezone' => 'Asia/Dubai',
                'latitude' => 24.8000,
                'longitude' => 54.6500,
                'is_gulf' => true,
                'is_uae' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Port Rashid',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'port_code' => 'RSH',
                'unlocode' => 'AERAS',
                'timezone' => 'Asia/Dubai',
                'latitude' => 25.2700,
                'longitude' => 55.2800,
                'is_gulf' => true,
                'is_uae' => true,
                'is_active' => true,
            ],

            // 🇸🇦 Saudi Arabia

            [
                'name' => 'Jeddah Islamic Port',
                'country' => 'Saudi Arabia',
                'country_code' => 'SA',
                'port_code' => 'JED',
                'unlocode' => 'SAJED',
                'timezone' => 'Asia/Riyadh',
                'latitude' => 21.4858,
                'longitude' => 39.1680,
                'is_gulf' => false,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'King Abdullah Port',
                'country' => 'Saudi Arabia',
                'country_code' => 'SA',
                'port_code' => 'KAP',
                'unlocode' => 'SAKAC',
                'timezone' => 'Asia/Riyadh',
                'latitude' => 22.4700,
                'longitude' => 39.1000,
                'is_gulf' => false,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'King Abdul Aziz Port',
                'country' => 'Saudi Arabia',
                'country_code' => 'SA',
                'port_code' => 'DMM',
                'unlocode' => 'SADMM',
                'timezone' => 'Asia/Riyadh',
                'latitude' => 26.4300,
                'longitude' => 50.1000,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            // 🇴🇲 Oman

            [
                'name' => 'Port of Sohar',
                'country' => 'Oman',
                'country_code' => 'OM',
                'port_code' => 'SOH',
                'unlocode' => 'OMSOH',
                'timezone' => 'Asia/Muscat',
                'latitude' => 24.5000,
                'longitude' => 56.6200,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Port of Salalah',
                'country' => 'Oman',
                'country_code' => 'OM',
                'port_code' => 'SLL',
                'unlocode' => 'OMSLL',
                'timezone' => 'Asia/Muscat',
                'latitude' => 16.9500,
                'longitude' => 54.0000,
                'is_gulf' => false,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Port of Duqm',
                'country' => 'Oman',
                'country_code' => 'OM',
                'port_code' => 'DQM',
                'unlocode' => 'OMDUQ',
                'timezone' => 'Asia/Muscat',
                'latitude' => 19.6700,
                'longitude' => 57.7000,
                'is_gulf' => false,
                'is_uae' => false,
                'is_active' => true,
            ],

            // 🇶🇦 Qatar

            [
                'name' => 'Hamad Port',
                'country' => 'Qatar',
                'country_code' => 'QA',
                'port_code' => 'HMD',
                'unlocode' => 'QAHMD',
                'timezone' => 'Asia/Qatar',
                'latitude' => 24.8500,
                'longitude' => 51.6000,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            // 🇧🇭 Bahrain

            [
                'name' => 'Khalifa Bin Salman Port',
                'country' => 'Bahrain',
                'country_code' => 'BH',
                'port_code' => 'KBS',
                'unlocode' => 'BHKBS',
                'timezone' => 'Asia/Bahrain',
                'latitude' => 26.2200,
                'longitude' => 50.6800,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            // 🇦🇪 Fujairah and Khorfakkan

            [
                'name' => 'Port of Fujairah',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'port_code' => 'FJR',
                'unlocode' => 'AEFJR',
                'timezone' => 'Asia/Dubai',
                'latitude' => 25.1700,
                'longitude' => 56.3600,
                'is_gulf' => false,
                'is_uae' => true,
                'is_active' => true,
            ],

            [
                'name' => 'Port of Khorfakkan',
                'country' => 'United Arab Emirates',
                'country_code' => 'AE',
                'port_code' => 'KLF',
                'unlocode' => 'AEKLF',
                'timezone' => 'Asia/Dubai',
                'latitude' => 25.35783,
                'longitude' => 56.36544,
                'is_gulf' => false,
                'is_uae' => true,
                'is_active' => true,
            ],

            // 🇮🇶 Iraq

            [
                'name' => 'Umm Qasr Port',
                'country' => 'Iraq',
                'country_code' => 'IQ',
                'port_code' => 'UQR',
                'unlocode' => 'IQUQR',
                'timezone' => 'Asia/Baghdad',
                'latitude' => 30.02737,
                'longitude' => 47.97300,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Khor Al Zubair Port',
                'country' => 'Iraq',
                'country_code' => 'IQ',
                'port_code' => 'KAZ',
                'unlocode' => 'IQKAZ',
                'timezone' => 'Asia/Baghdad',
                'latitude' => 30.19381,
                'longitude' => 47.87670,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Al Faw Grand Port',
                'country' => 'Iraq',
                'country_code' => 'IQ',
                'port_code' => null,
                'unlocode' => null,
                'timezone' => 'Asia/Baghdad',
                'latitude' => null,
                'longitude' => null,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => false,
            ],

            // 🇰🇼 Kuwait

            [
                'name' => 'Shuwaikh Port',
                'country' => 'Kuwait',
                'country_code' => 'KW',
                'port_code' => 'SWK',
                'unlocode' => 'KWSWK',
                'timezone' => 'Asia/Kuwait',
                'latitude' => 29.3500,
                'longitude' => 47.9200,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],

            [
                'name' => 'Shuaiba Port',
                'country' => 'Kuwait',
                'country_code' => 'KW',
                'port_code' => 'SAA',
                'unlocode' => 'KWSAA',
                'timezone' => 'Asia/Kuwait',
                'latitude' => 29.0500,
                'longitude' => 48.1500,
                'is_gulf' => true,
                'is_uae' => false,
                'is_active' => true,
            ],
        ];

        foreach ($ports as $port) {
            $identity = $port['unlocode']
                ? ['unlocode' => $port['unlocode']]
                : [
                    'country_code' => $port['country_code'],
                    'name' => $port['name'],
                ];

            Port::updateOrCreate($identity, $port);
        }
    }
}
