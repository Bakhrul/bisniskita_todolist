<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
           DB::table('m_users')->insert([
          'us_id'=> 1,
          'us_name' => 'Administrator',
          'email' => 'bakhrulrpl@gmail.com',
          'password' => bcrypt('123456'),
        ]); 
    }
}
