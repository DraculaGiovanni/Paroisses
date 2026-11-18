<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouleurExceptionnelle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LiturgieController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', CouleurExceptionnelle::class);

        $couleurs = $request->user()->isSuperAdmin()
            ? collect()
            : CouleurExceptionnelle::forParoisse($request->user()->paroisse_id)
                ->orderBy('date')
                ->get(['id', 'date', 'couleur', 'libelle'])
                ->map(fn (CouleurExceptionnelle $exception): array => [
                    'id' => $exception->id,
                    'date' => $exception->date->format('Y-m-d'),
                    'couleur' => $exception->couleur,
                    'libelle' => $exception->libelle,
                ]);

        return Inertia::render('Admin/Liturgie/Index', [
            'couleurs' => $couleurs,
            'couleursDisponibles' => [
                'violet' => 'Violet',
                'rose' => 'Rose',
                'blanc' => 'Blanc',
                'rouge' => 'Rouge',
                'vert' => 'Vert',
                'noir' => 'Noir',
            ],
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', CouleurExceptionnelle::class);
        abort_unless($request->user()->paroisse_id !== null, 403);

        $data = $request->validate([
            'date' => ['required', 'date', 'unique:couleurs_exceptionnelles,date,NULL,id,paroisse_id,'.$request->user()->paroisse_id],
            'couleur' => ['required', Rule::in(['violet', 'rose', 'blanc', 'rouge', 'vert', 'noir'])],
            'libelle' => ['nullable', 'string', 'max:150'],
        ]);

        CouleurExceptionnelle::create([
            ...$data,
            'paroisse_id' => $request->user()->paroisse_id,
        ]);

        return back()->with('success', 'Couleur exceptionnelle enregistrée.');
    }

    public function destroy(CouleurExceptionnelle $couleurExceptionnelle)
    {
        Gate::authorize('delete', $couleurExceptionnelle);
        $couleurExceptionnelle->delete();

        return back()->with('success', 'Couleur exceptionnelle supprimée.');
    }
}
