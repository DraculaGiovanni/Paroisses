<?php

namespace Tests;

use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsAdmin(Paroisse $paroisse): static
    {
        return $this->actingAs(User::factory()->admin()->create([
            'paroisse_id' => $paroisse->id,
        ]));
    }

    protected function actingAsSecretariat(Paroisse $paroisse): static
    {
        return $this->actingAs(User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'role' => 'secretariat',
        ]));
    }
}
