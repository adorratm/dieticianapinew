<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 0; $i <= 10000; $i++) {
            $add = new \App\Model\Panel\Dieticians;

            $add->name = Str::random(10);
            $add->email = Str::random(10) . '@gmail.com';
            $add->password = Hash::make('password');
            $add->save();
        }
    }
}
