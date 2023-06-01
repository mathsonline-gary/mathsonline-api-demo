<?php

namespace Tests;

use Database\Seeders\MarketSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Run market seeder before each test.
     *
     * @var string
     */
    protected string $seeder = MarketSeeder::class;
}
