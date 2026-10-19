<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

/**
 * Location Seeder
 *
 * The 14 southern provinces and their districts, from database/data/south.json (findnest's location data). Safe to run
 * again: rows are matched on their government code.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package Database\Seeders
 */
class LocationSeeder extends Seeder
{
    /**
     * Seed the provinces and districts
     *
     * @return void
     */
    public function run(): void
    {
        $provinces = json_decode((string) file_get_contents(database_path('data/south.json')), true);

        foreach ($provinces as $row) {
            $province = Province::updateOrCreate(['code' => $row['code']], ['name' => $row['name'], 'name_th' => $row['name_th']]);

            foreach ($row['districts'] as [$code, $name, $nameTh]) {
                $province->districts()->updateOrCreate(['code' => $code], ['name' => $name, 'name_th' => $nameTh]);
            }
        }
    }
}
