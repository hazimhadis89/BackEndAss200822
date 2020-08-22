<?php

use App\Model\User;
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
        factory(User::class)->create([
            'name' => 'superadmin',
            'email' => 'hazim.hadis@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt(env('DEFAULT_PASSWORD')),
            'remember_token' => Str::random(10),
        ]);

        factory(User::class, 99)->create();
    }
}
