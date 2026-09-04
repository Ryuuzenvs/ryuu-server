<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Owner (Kamu)
        User::create([
            'name' => 'Ryuu (Owner)',
            'username' => 'owner',
            'email' => 'owner@ryuu.com',
            'password' => Hash::make('Dahna1357'), // Ganti password sesuai keinginanmu
            'role' => 'owner',
        ]);

        // Akun Guest (Portofolio / Publik)
        User::create([
            'name' => 'Guest User',
            'username' => 'guest',
            'email' => 'guest@ryuu.com',
            'password' => Hash::make('guest123'),
            'role' => 'guest',
        ]);
    }
}
