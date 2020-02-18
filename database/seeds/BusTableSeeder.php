<?php

use Illuminate\Database\Seeder;
use App\Bus;

class BusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $busArr = range(1, 49);

        foreach ($busArr as $bus) {
        	
        	Bus::create([


				'num' => $bus       		


        	]);

        }
    }
}
