<?php

namespace Database\Seeders;

use App\Models\CommercialProduct;
use App\Models\CommercialProductCategory;
use App\Models\CommercialProductType;
use Illuminate\Database\Seeder;

class CommercialProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            'Cableado',
            'Tubería',
            'Elevación',
            'Control de acceso',
            'Video vigilancia',
            'Audio y VOIP',
            'Energía',
            'Infraestructura',
        ])->mapWithKeys(fn (string $name): array => [
            $name => CommercialProductCategory::query()->firstOrCreate(['name' => $name]),
        ]);

        $types = collect([
            'Software',
            'Tarjeta',
            'Adaptador',
            'Accesorio',
            'Cámara',
            'Switch',
            'Cable',
            'Servicio',
        ])->mapWithKeys(fn (string $name): array => [
            $name => CommercialProductType::query()->firstOrCreate(['name' => $name]),
        ]);

        $products = [
            ['name' => 'Cable UTP Cat6 305 m azul', 'part_number' => 'CBL-CAT6-305', 'sat_code' => '43202212', 'category' => 'Cableado', 'type' => 'Cable', 'brand' => 'Panduit', 'model' => 'UTP28SP7BU'],
            ['name' => 'Bobina fibra óptica monomodo 12 hilos', 'part_number' => 'FO-SM-12', 'sat_code' => '85447001', 'category' => 'Cableado', 'type' => 'Cable', 'brand' => 'Corning', 'model' => '012T88-33131-29'],
            ['name' => 'Charola tipo escalerilla 4" x 2 m', 'part_number' => 'ESC-4-200', 'sat_code' => '73089099', 'category' => 'Tubería', 'type' => 'Accesorio', 'brand' => 'Indalum', 'model' => 'ES-102'],
            ['name' => 'Tubería conduit PVC 1"', 'part_number' => 'CND-PVC-1', 'sat_code' => '39172301', 'category' => 'Tubería', 'type' => 'Accesorio', 'brand' => 'IUSA', 'model' => 'PV-100'],
            ['name' => 'Elevador tipo tijera 12 m', 'part_number' => 'ELV-TIJ-12', 'sat_code' => '84279099', 'category' => 'Elevación', 'type' => 'Servicio', 'brand' => 'JLG', 'model' => '3246ES'],
            ['name' => 'Plataforma elevadora articulada', 'part_number' => 'ELV-ART-18', 'sat_code' => '84279099', 'category' => 'Elevación', 'type' => 'Servicio', 'brand' => 'Genie', 'model' => 'Z-45/25J'],
            ['name' => 'Lector tarjetas proximidad MIFARE', 'part_number' => 'ACC-LPR-01', 'sat_code' => '85235219', 'category' => 'Control de acceso', 'type' => 'Accesorio', 'brand' => 'HID', 'model' => 'RPK40'],
            ['name' => 'Controladora 4 puertas Ethernet', 'part_number' => 'ACC-CTL-4D', 'sat_code' => '84715099', 'category' => 'Control de acceso', 'type' => 'Tarjeta', 'brand' => 'Kantech', 'model' => 'KT-400'],
            ['name' => 'Licencia software gestión de acceso (50 usuarios)', 'part_number' => 'SW-ACC-50', 'sat_code' => '85235101', 'category' => 'Control de acceso', 'type' => 'Software', 'brand' => 'Kantech', 'model' => 'ENTRA-50'],
            ['name' => 'Cámara IP dome 4 MP WDR', 'part_number' => 'CAM-DM-4MP', 'sat_code' => '85258919', 'category' => 'Video vigilancia', 'type' => 'Cámara', 'brand' => 'Axis', 'model' => 'P3265-V'],
            ['name' => 'NVR 32 canales 4K', 'part_number' => 'NVR-32-4K', 'sat_code' => '84715099', 'category' => 'Video vigilancia', 'type' => 'Tarjeta', 'brand' => 'Hanwha', 'model' => 'XRN-3204'],
            ['name' => 'Adaptador PoE+ 30 W', 'part_number' => 'ADP-POE-30', 'sat_code' => '85044099', 'category' => 'Video vigilancia', 'type' => 'Adaptador', 'brand' => 'Ubiquiti', 'model' => 'POE-48-24W'],
            ['name' => 'Teléfono IP empresarial 12 líneas', 'part_number' => 'VOIP-T12', 'sat_code' => '85171899', 'category' => 'Audio y VOIP', 'type' => 'Accesorio', 'brand' => 'Yealink', 'model' => 'T46U'],
            ['name' => 'Gateway VoIP 8 FXS', 'part_number' => 'VOIP-GW-8', 'sat_code' => '85171899', 'category' => 'Audio y VOIP', 'type' => 'Tarjeta', 'brand' => 'Grandstream', 'model' => 'HT818'],
            ['name' => 'UPS en línea 3 kVA', 'part_number' => 'UPS-3K', 'sat_code' => '85044099', 'category' => 'Energía', 'type' => 'Accesorio', 'brand' => 'APC', 'model' => 'SMT3000RM2UC'],
            ['name' => 'PDU rack 19" 8 contactos', 'part_number' => 'PDU-R8', 'sat_code' => '85369099', 'category' => 'Energía', 'type' => 'Accesorio', 'brand' => 'APC', 'model' => 'AP7920B'],
            ['name' => 'Switch administrable 48 puertos PoE+', 'part_number' => 'SW-48-POE', 'sat_code' => '85176299', 'category' => 'Infraestructura', 'type' => 'Switch', 'brand' => 'Cisco', 'model' => 'C9200-48P'],
            ['name' => 'Rack abierto 42U 800 mm', 'part_number' => 'RCK-42U', 'sat_code' => '73089099', 'category' => 'Infraestructura', 'type' => 'Accesorio', 'brand' => 'Chatsworth', 'model' => 'CPI-11816-703'],
            ['name' => 'Organizador horizontal 2U', 'part_number' => 'ORG-H2U', 'sat_code' => '73089099', 'category' => 'Cableado', 'type' => 'Accesorio', 'brand' => 'Panduit', 'model' => 'WMPV2E'],
            ['name' => 'Parcheo UTP Cat6 1 m amarillo', 'part_number' => 'PTC-C6-1M', 'sat_code' => '85444220', 'category' => 'Cableado', 'type' => 'Cable', 'brand' => 'Panduit', 'model' => 'UTP6A1MY'],
            ['name' => 'Micrófono de techo array', 'part_number' => 'AUD-MIC-CLG', 'sat_code' => '85181099', 'category' => 'Audio y VOIP', 'type' => 'Accesorio', 'brand' => 'Shure', 'model' => 'MXA910'],
            ['name' => 'Kit herraje elevación canalización', 'part_number' => 'KIT-ELE-01', 'sat_code' => '73269099', 'category' => 'Elevación', 'type' => 'Accesorio', 'brand' => 'Milwaukee', 'model' => 'KIT-ELV-12'],
        ];

        foreach ($products as $product) {
            CommercialProduct::query()->updateOrCreate(
                ['part_number' => $product['part_number']],
                [
                    'name' => $product['name'],
                    'sat_code' => $product['sat_code'],
                    'commercial_product_category_id' => $categories[$product['category']]->getKey(),
                    'commercial_product_type_id' => $types[$product['type']]->getKey(),
                    'brand' => $product['brand'],
                    'model' => $product['model'],
                    'specifications' => '<p>Producto de demostración para pruebas del módulo Comercial.</p>',
                    'is_active' => true,
                ],
            );
        }
    }
}
