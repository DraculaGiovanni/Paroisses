<?php

namespace Database\Factories;

use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Noms béninois courants
     */
    private static array $firstNames = [
        'Aimé', 'Prosper', 'Serge', 'Mathieu', 'Jean-Baptiste', 'François',
        'Marie', 'Thérèse', 'Angélique', 'Pauline', 'Rosine', 'Chantal',
    ];

    private static array $lastNames = [
        'Agbodjan', 'Koudjo', 'Hounnou', 'Assogba', 'Dossou', 'Kpadonou',
        'Amoussou', 'Gbenou', 'Sossou', 'Adjovi', 'Houngbédji', 'Zinsou',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->randomElement(self::$firstNames);
        $lastName = fake()->randomElement(self::$lastNames);
        $name = $firstName.' '.$lastName;

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'paroisse_id' => Paroisse::factory(),
            'role' => 'secretariat',
            'actif' => true,
            'must_change_password' => false,
            'permissions' => Permission::defaultForSecretariat(),
            'last_login_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'permissions' => Permission::values(),
        ]);
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'paroisse_id' => null,
        ]);
    }
}
