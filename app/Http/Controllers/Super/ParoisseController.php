<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Http\Requests\Super\StoreParoisseRequest;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ParoisseController extends Controller
{
    public function index()
    {
        return Inertia::render('Super/Paroisses/Index', [
            'paroisses' => Paroisse::withCount('users')
                ->orderBy('nom')
                ->get(['id', 'nom', 'slug', 'diocese']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Super/Paroisses/Create');
    }

    public function store(StoreParoisseRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $paroisse = Paroisse::create([
                'nom' => $data['nom'],
                'slug' => Paroisse::uniqueSlug($data['nom']),
                'diocese' => $data['diocese'] ?? null,
                'couleur_principale' => $data['couleur_principale'] ?? null,
            ]);

            User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'paroisse_id' => $paroisse->id,
                'role' => 'admin',
            ]);
        });

        return redirect()->route('super.paroisses.index')
            ->with('success', 'Paroisse créée.');
    }
}
