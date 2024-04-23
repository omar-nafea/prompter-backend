<?php

declare(strict_types=1);

namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\app\Models\User;

class AuthDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(10)->create();
    }
}
