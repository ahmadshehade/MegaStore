<?php

namespace Database\Seeders;

use App\Enum\UserRoles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (UserRoles::cases() as $case) {
            Role::firstOrCreate([
                'name' => $case->value,
                'guard_name' => 'web'
            ]);
        }
    }
}
