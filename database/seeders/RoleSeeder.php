<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Admin', 'Formulator', 'Teknisi', 'Manajer R&D', 'QA'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
}
