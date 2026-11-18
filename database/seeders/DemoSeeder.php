<?php

namespace Database\Seeders;

use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DemoSeeder
        $paroisse = Paroisse::create([
            'nom' => 'Paroisse Saint-Jean (démo)',
            'slug' => 'saint-jean-demo',
        ]);

        User::create([
            'name' => 'Admin Démo',
            'email' => 'admin@example.com',
            'password' => Hash::make('change-moi'),
            'paroisse_id' => $paroisse->id,
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'toi@example.com',
            'password' => Hash::make('change-moi'),
            'role' => 'super_admin',
        ]);
    }
}
