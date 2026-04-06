<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Municipality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departmentsWithMunicipalities = [
            'Boaco' => [
                'Boaco',
                'Camoapa',
                'San José de los Remates',
                'San Lorenzo',
                'Santa Lucía',
                'Teustepe'
            ],
            'Carazo' => [
                'Jinotepe',
                'Diriamba',
                'Dolores',
                'El Rosario',
                'La Conquista',
                'La Paz de Carazo',
                'San Marcos',
                'Santa Teresa'
            ],
            'Chinandega' => [
                'Chinandega',
                'Corinto',
                'El Realejo',
                'Chichigalpa',
                'Posoltega',
                'Quezalguaque',
                'El Viejo',
                'Somotillo',
                'Villanueva',
                'Puerto Morazán',
                'Cinco Pinos',
                'San Pedro del Norte',
                'Santo Tomás del Norte'
            ],
            'Chontales' => [
                'Juigalpa',
                'Acoyapa',
                'Comalapa',
                'Cuapa',
                'El Coral',
                'La Libertad',
                'San Francisco de Cuapa',
                'San Pedro de Lóvago',
                'Santo Domingo',
                'Villa Sandino'
            ],
            'Estelí' => [
                'Estelí',
                'Condega',
                'Pueblo Nuevo',
                'La Trinidad',
                'San Juan de Limay',
                'San Nicolás'
            ],
            'Granada' => [
                'Granada',
                'Diriá',
                'Diriomo',
                'Nandaime'
            ],
            'Jinotega' => [
                'Jinotega',
                'San Rafael del Norte',
                'San Sebastián de Yalí',
                'La Concordia',
                'Wiwilí de Jinotega',
                'El Cuá',
                'Santa María de Pantasma',
                'San José de Bocay'
            ],
            'León' => [
                'León',
                'Nagarote',
                'La Paz Centro',
                'Telica',
                'Quezalguaque',
                'El Jicaral',
                'El Sauce',
                'Achuapa',
                'Santa Rosa del Peñón',
                'Malpaisillo'
            ],
            'Madriz' => [
                'Somoto',
                'Totogalpa',
                'San Lucas',
                'Las Sabanas',
                'San José de Cusmapa',
                'Yalagüina',
                'Palacagüina',
                'Telpaneca',
                'San Juan de Río Coco'
            ],
            'Managua' => [
                'Managua',
                'Ciudad Sandino',
                'Tipitapa',
                'Ticuantepe',
                'El Crucero',
                'Mateare',
                'San Rafael del Sur',
                'Villa El Carmen',
                'San Francisco Libre'
            ],
            'Masaya' => [
                'Masaya',
                'Catarina',
                'Masatepe',
                'Nandasmo',
                'Nindirí',
                'Niquinohomo',
                'San Juan de Oriente',
                'Tisma',
                'La Concepción'
            ],
            'Matagalpa' => [
                'Matagalpa',
                'Sébaco',
                'San Isidro',
                'Terrabona',
                'Río Blanco',
                'Muy Muy',
                'Esquipulas',
                'San Dionisio',
                'Ciudad Darío',
                'San Ramón',
                'Matiguás',
                'Rancho Grande',
                'Tuma-La Dalia'
            ],
            'Nueva Segovia' => [
                'Ocotal',
                'Dipilto',
                'Jalapa',
                'Murra',
                'Mozonte',
                'Santa María',
                'Macuelizo',
                'Quilalí',
                'El Jícaro',
                'Wiwilí de Nueva Segovia',
                'San Fernando',
                'Ciudad Antigua'
            ],
            'Río San Juan' => [
                'San Carlos',
                'El Almendro',
                'San Miguelito',
                'Morrito',
                'El Castillo',
                'San Juan del Norte (Greytown)'
            ],
            'Rivas' => [
                'Rivas',
                'Altagracia',
                'San Jorge',
                'San Juan del Sur',
                'Cárdenas',
                'Belén',
                'Buenos Aires',
                'Potosí',
                'Tola',
                'Moyogalpa'
            ],
            'Región Autónoma de la Costa Caribe Norte (RACCN)' => [
                'Puerto Cabezas (Bilwi)',
                'Waspam',
                'Bonanza',
                'Rosita',
                'Siuna',
                'Prinzapolka',
                'Mulukukú',
                'Waslala'
            ],
            'Región Autónoma de la Costa Caribe Sur (RACCS)' => [
                'Bluefields',
                'Corn Island',
                'Laguna de Perlas',
                'Kukra Hill',
                'Desembocadura de Río Grande',
                'La Cruz de Río Grande',
                'Muelle de los Bueyes',
                'El Rama',
                'Nueva Guinea',
                'Paiwas',
                'Bocana de Paiwas',
                'El Ayote'
            ],
            'Internacional' => [
                'USA',
                'USA - Miami',
                'USA - Los Angeles',
                'USA - New York',
                'USA - Chicago',
                'USA - Houston',
                'USA - San Francisco',
                'USA - Seattle',
                'USA - Washington D.C.',
                'USA - Boston',
                'USA - Dallas',
                'Canada',
            ]
        ];

        foreach ($departmentsWithMunicipalities as $departmentName => $municipalities) {
            $department = Department::firstOrCreate(['name' => $departmentName]);
            foreach ($municipalities as $municipalityName) {
                Municipality::firstOrCreate([
                    'name' => $municipalityName,
                    'department_id' => $department->id
                ]);
            }
        }
    }
}
