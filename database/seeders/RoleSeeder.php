<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'super_admin',
        ]);
        Role::create([
            'name' => 'school_fee_officer',
        ]);
        Role::create([
            'name' => 'igr_officer',
        ]);
        Role::create([
            'name' => 'student',
        ]);
    }
}
