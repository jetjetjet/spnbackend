<?php

use Illuminate\Database\Seeder;
use App\User;

class userseed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        User::create([
            'full_name' => 'Afdal Zikri',
            'username' => 'Afdal',
            'email' => 'afd.zik@gmail.com',
            'password' => bcrypt('afd'),
            'active' => '1',
            'created_at' => now()->toDateTimeString(),
            'created_by' => '1'
        ]);

        factory(User::class, 40)->create();
    }
}
