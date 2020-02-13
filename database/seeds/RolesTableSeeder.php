<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [

        	'super_admin',
        	'admin',
        	'hr',
        	'user'

        ];

        foreach ($roles as $role) {
        	Role::create([

        		'name' => $role

        	]);
        }
    }
}
