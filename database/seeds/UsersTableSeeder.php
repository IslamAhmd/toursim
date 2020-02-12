<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', 'super_admin')->first();

        User::create([

        	'name' => 'Super Admin',
        	'role_id' => $role->id,
            'role_name' => $role->name,
        	'password' => bcrypt('super_admin')

        ]);
    }
}
