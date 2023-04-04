<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TutorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seed = [
            ['name' => 'primary parent'],
            ['name' => 'secondary parent'],
            ['name' => 'school parent'],
        ];

        DB::table('tutor_types')
            ->insert($seed);
    }
}
