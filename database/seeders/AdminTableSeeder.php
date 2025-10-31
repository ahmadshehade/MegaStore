<?php

namespace Database\Seeders;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table("users")->delete();
         $user=User::create([
            'name'=>'admin',
            'email'=>'admin@admin.com',
            'password'=>Hash::make('P@ssw0rd'),
         ]);

         $user->assignRole(UserRoles::SuperAdmin->value);

    }
}
