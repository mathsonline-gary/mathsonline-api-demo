<?php

namespace Tests;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    /**
     * Run MarketSeeder before each test.
     *
     * @var string
     * @see MarketSeeder
     */
    protected string $seeder = MarketSeeder::class;
}
