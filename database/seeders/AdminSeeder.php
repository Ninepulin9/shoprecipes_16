<?php

namespace Database\Seeders;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'name' => 'ผู้ดูแลระบบ',
                'UID' => Str::upper(Str::random(8)),
                'email_verified_at' => now(),
                'password' => Hash::make('123456789'),
                'remember_token' => null,
                'role' => 'admin',
                'point' => 0 
            ]
        );

        User::firstOrCreate(
            ['email' => 'users@example.com'], 
            [
                'name' => 'ลูกค้า',
                'UID' => Str::upper(Str::random(8)),
                'email_verified_at' => now(),
                'password' => Hash::make('123456789'),
                'remember_token' => null,
                'role' => 'user',
                'point' => 0 
            ]
        );
    }
}
