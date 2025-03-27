<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Ensure you import the User model
use App\Models\Hazard; // Ensure you import the User model

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "id" => "1",
                "name" => "battery-charging-area",
                "name_sp" => "Área de Carga de Baterías",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713376205.png"
            ],
            [
                "id" => "2",
                "name" => "biological-hazard",
                "name_sp" => "Riesgo Biológico",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713376287.png"
            ],
            [
                "id" => "3",
                "name" => "cutting-hazard",
                "name_sp" => "Riesgo de Cortes",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457574.png"
            ],
            [
                "id" => "4",
                "name" => "danger-of-crushing",
                "name_sp" => "Peligro de Aplastamiento",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457607.png"
            ],
            [
                "id" => "5",
                "name" => "danger-of-death",
                "name_sp" => "Peligro de Muerte",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457641.png"
            ],
            [
                "id" => "6",
                "name" => "danger-of-suffocation-hazard",
                "name_sp" => "Peligro de Asfixia",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457675.png"
            ],
            [
                "id" => "7",
                "name" => "electrical-shock-hazard",
                "name_sp" => "Riesgo de Choque Eléctrico",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457707.png"
            ],
            [
                "id" => "8",
                "name" => "electricity-hazard",
                "name_sp" => "Riesgo Eléctrico",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457745.png"
            ],
            [
                "id" => "9",
                "name" => "entrapment-hazard",
                "name_sp" => "Riesgo de Atrapamiento",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457780.png"
            ],
            [
                "id" => "10",
                "name" => "environmental-hazard",
                "name_sp" => "Riesgo Ambiental",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457932.png"
            ],
            [
                "id" => "11",
                "name" => "explosive-materials",
                "name_sp" => "Materiales Explosivos",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457963.png"
            ],
            [
                "id" => "12",
                "name" => "falling-objects-hazard",
                "name_sp" => "Riesgo de Caída de Objetos",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713457990.png"
            ],
            [
                "id" => "13",
                "name" => "flammable-material",
                "name_sp" => "Material Inflamable",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458125.png"
            ],
            [
                "id" => "14",
                "name" => "floor-level-obstacle",
                "name_sp" => "Obstáculo a Nivel del Suelo",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458156.png"
            ],
            [
                "id" => "15",
                "name" => "forklift-trucks",
                "name_sp" => "Camiones de Montacargas",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458195.png"
            ],
            [
                "id" => "16",
                "name" => "gas-cylinders-hazard",
                "name_sp" => "Riesgo de Cilindros de Gas",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458223.png"
            ],
            [
                "id" => "17",
                "name" => "general-warning-sign",
                "name_sp" => "Advertencia General",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458252.png"
            ],
            [
                "id" => "18",
                "name" => "glass-hazard",
                "name_sp" => "Riesgo de Vidrio",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458282.png"
            ],
            [
                "id" => "19",
                "name" => "high-temperature-hazard",
                "name_sp" => "Riesgo de Alta Temperatura",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458316.png"
            ],
            [
                "id" => "20",
                "name" => "hot-surface",
                "name_sp" => "Superficie Caliente",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458349.png"
            ],
            [
                "id" => "21",
                "name" => "irritant",
                "name_sp" => "Irritante",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458377.png"
            ],
            [
                "id" => "22",
                "name" => "laser-beam-hazard",
                "name_sp" => "Riesgo de Haz Láser",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458412.png"
            ],
            [
                "id" => "23",
                "name" => "low-temperature-hazard",
                "name_sp" => "Riesgo de Baja Temperatura",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458439.png"
            ],
            [
                "id" => "24",
                "name" => "magnetic-fields",
                "name_sp" => "Campos Magnéticos",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458468.png"
            ],
            [
                "id" => "25",
                "name" => "non-ionizing-radiation",
                "name_sp" => "Radiación No Ionizante",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458493.png"
            ],
            [
                "id" => "26",
                "name" => "optical-radiation",
                "name_sp" => "Radiación Óptica",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458520.png"
            ],
            [
                "id" => "27",
                "name" => "overhead-load",
                "name_sp" => "Carga Sobrecabeza",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458576.png"
            ],
            [
                "id" => "28",
                "name" => "oxidizing-substance",
                "name_sp" => "Sustancia Oxidante",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458600.png"
            ],
            [
                "id" => "29",
                "name" => "radioactive-material",
                "name_sp" => "Material Radiactivo",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458629.png"
            ],
            [
                "id" => "30",
                "name" => "rotating-blade-hazard",
                "name_sp" => "Riesgo de Hoja Giratoria",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458673.png"
            ],
            [
                "id" => "31",
                "name" => "rotating-parts-hazard",
                "name_sp" => "Riesgo de Partes Giratorias",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458701.png"
            ],
            [
                "id" => "32",
                "name" => "slippery-floor-sign",
                "name_sp" => "Suelo Resbaladizo",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458780.png"
            ],
            [
                "id" => "33",
                "name" => "toxic-material",
                "name_sp" => "Material Tóxico",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458806.png"
            ],
            [
                "id" => "34",
                "name" => "warning-crushing-of-hands",
                "name_sp" => "Advertencia de Aplastamiento de Manos",
                "icon" => "https://stage541.yourdesigndemo.net/images/1713458835.png"
            ]
        ];
        foreach ($data as $item) {
            Hazard::create($item);
        }
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'member']);
        $viewerRole = Role::create(['name' => 'group_admin']);

        // Get all permissions
        $permissions = Permission::all();

        // Assign permissions to the roles
        $adminRole->givePermissionTo($permissions); // Admin gets all permissions
        $editorRole->givePermissionTo('group_management','leave_group', 'view_group_members', 'view_group'); // Editor gets limited permissions
        $viewerRole->givePermissionTo('group_management','leave_group','remove_member_from_group','make_group_member_admin','generate_group_invite_code','generate_group_invite_link','add_members_to_group','update_group_join_requests','view_group_members','reset_group_code','edit_group'); // Viewer gets only 'view_post'

    }
}
