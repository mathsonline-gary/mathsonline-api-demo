<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seed = [];

        // Australia
        foreach (range(0, 12) as $index) {
            $year = [
                'market_id' => 1,
                'name' => "Year $index",
                'order' => $index + 1,
            ];

            if ($index === 0) {
                $year['name'] = 'Kindergarten';
            }

            $seed[] = $year;
        }

        // UK
        foreach (range(0, 13) as $index) {
            $year = [
                'market_id' => 2,
                'name' => "Year $index",
                'order' => $index + 1,
            ];

            if ($index === 0) {
                $year['name'] = 'Reception';
            }

            $seed[] = $year;
        }

        // New Zealand
        foreach (range(1, 13) as $index) {
            $year = [
                'market_id' => 3,
                'name' => "Year $index",
                'order' => $index,
            ];

            $seed[] = $year;
        }

        // US
        foreach (range(0, 13) as $index) {
            $year = [
                'market_id' => 4,
                'name' => "{$index}th Grade",
                'order' => $index + 1,
            ];

            $year['name'] = match ($index) {
                0 => 'Kindergarten',
                1 => '1st Grade',
                2 => '2nd Grade',
                3 => '3rd Grade',
                9 => 'Algebra I',
                10 => 'Geometry',
                11 => 'Algebra II',
                12 => 'Pre-Calculus (Including Trigonometry)',
                13 => 'Calculus',
                default => $year['name'],
            };

            $seed[] = $year;
        }

        // Kenya
        foreach (range(1, 12) as $index) {
            $year = [
                'market_id' => 5,
                'name' => "Standard $index",
                'order' => $index,
            ];

            $year['name'] = match ($index) {
                9, 10, 11, 12 => "Form $index",
                default => $year['name'],
            };

            $seed[] = $year;
        }

        // Australia 2
        foreach (range(0, 12) as $index) {
            $year = [
                'market_id' => 6,
                'name' => "Year $index",
                'order' => $index + 1,
            ];

            if ($index === 0) {
                $year['name'] = 'Kindergarten';
            }

            $seed[] = $year;
        }

        // South Africa
        foreach (range(1, 13) as $index) {
            $year = [
                'market_id' => 7,
                'name' => "Standard $index",
                'order' => $index,
            ];

            $year['name'] = match ($index) {
                13 => "Advanced Programme Maths",
                default => $year['name'],
            };

            $seed[] = $year;
        }

        // US 2
        foreach (range(0, 13) as $index) {
            $year = [
                'market_id' => 8,
                'name' => "{$index}th Grade",
                'order' => $index + 1,
            ];

            $year['name'] = match ($index) {
                0 => 'Kindergarten',
                1 => '1st Grade',
                2 => '2nd Grade',
                3 => '3rd Grade',
                9 => 'Algebra I',
                10 => 'Geometry',
                11 => 'Algebra II',
                12 => 'Pre-Calculus (Including Trigonometry)',
                13 => 'Calculus',
                default => $year['name'],
            };

            $seed[] = $year;
        }

        // India
        foreach (range(1, 12) as $index) {
            $year = [
                'market_id' => 9,
                'name' => "Year $index",
                'order' => $index,
            ];

            $seed[] = $year;
        }

        DB::table('years')
            ->insert($seed);
    }
}
