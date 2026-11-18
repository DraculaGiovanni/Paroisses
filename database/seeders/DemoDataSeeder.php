<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Génère des données réalistes pour démonstration:
     * - 3 paroisses
     * - 6-8 horaires par paroisse (~20 total)
     * - 15-20 annonces par paroisse (~50 total)
     * - 1 super admin, 1 admin + 2-3 secrétariats par paroisse
     */
    public function run(): void
    {
        $this->command->info('🌱 Génération des données de démonstration...');

        // 1. Super Admin (pas de paroisse)
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'super@paroisses.bj',
        ]);
        $this->command->info('✓ Super Admin créé');

        // 2. Créer 3 paroisses avec leurs données
        $paroisses = Paroisse::factory()->count(3)->create();
        $totalHoraires = 0;
        $totalAnnonces = 0;

        foreach ($paroisses as $index => $paroisse) {
            $this->command->info("📍 Paroisse: {$paroisse->nom}");

            // Admin de la paroisse
            User::factory()->admin()->create([
                'name' => "Admin {$paroisse->nom}",
                'email' => "admin{$index}@paroisses.bj",
                'paroisse_id' => $paroisse->id,
            ]);

            // 2-3 secrétariats
            User::factory()->count(rand(2, 3))->create([
                'paroisse_id' => $paroisse->id,
                'role' => 'secretariat',
            ]);

            // 6-8 horaires par paroisse
            $horaireCount = rand(6, 8);
            Horaire::factory()->count($horaireCount)->create([
                'paroisse_id' => $paroisse->id,
            ]);
            $totalHoraires += Horaire::forParoisse($paroisse->id)->count();
            $this->command->info("  ✓ {$horaireCount} horaires");

            // 15-20 annonces par paroisse
            $annonceCount = rand(15, 20);

            // Mix: quelques brouillons, quelques épinglées
            $publishedCount = (int) ($annonceCount * 0.8); // 80% publiées
            $pinnedCount = (int) ($publishedCount * 0.15); // 15% épinglées

            // Annonces publiées normales
            Annonce::factory()->count($publishedCount - $pinnedCount)->create([
                'paroisse_id' => $paroisse->id,
            ]);

            // Annonces épinglées
            Annonce::factory()->pinned()->count($pinnedCount)->create([
                'paroisse_id' => $paroisse->id,
            ]);

            // Brouillons
            Annonce::factory()->draft()->count($annonceCount - $publishedCount)->create([
                'paroisse_id' => $paroisse->id,
            ]);
            $totalAnnonces += Annonce::forParoisse($paroisse->id)->count();

            $this->command->info("  ✓ {$annonceCount} annonces ({$publishedCount} publiées, {$pinnedCount} épinglées)");
        }

        $totalUsers = User::count();

        $this->command->info('');
        $this->command->info('✅ Données de démonstration générées avec succès!');
        $this->command->info("   • {$paroisses->count()} paroisses");
        $this->command->info("   • {$totalUsers} utilisateurs");
        $this->command->info("   • {$totalHoraires} horaires");
        $this->command->info("   • {$totalAnnonces} annonces");
        $this->command->info('');
        $this->command->info('🔑 Identifiants de connexion:');
        $this->command->info('   Super Admin: super@paroisses.bj / password');
        $this->command->info('   Admin 1:     admin0@paroisses.bj / password');
        $this->command->info('   Admin 2:     admin1@paroisses.bj / password');
        $this->command->info('   Admin 3:     admin2@paroisses.bj / password');
    }
}
