<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AvisController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Avis::class);

        $statut = $request->string('statut')->toString();

        $query = Avis::query();
        if (array_key_exists($statut, Avis::STATUTS)) {
            $query->where('statut', $statut);
        }

        return Inertia::render('Admin/Avis/Index', [
            'avis' => $query->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Avis $avis): array => [
                    'id' => $avis->id,
                    'message' => $avis->message,
                    'statut' => $avis->statut,
                    'statut_label' => $avis->statutLabel(),
                    'created_at' => $avis->created_at?->format('d/m/Y H:i'),
                ]),
            'statuts' => collect(Avis::STATUTS)->map(fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ])->values()->all(),
            'filtre' => $statut,
        ]);
    }

    public function update(Request $request, Avis $avis)
    {
        Gate::authorize('update', $avis);

        $data = $request->validate([
            'statut' => ['required', 'in:en_attente,publie,rejete'],
        ], [
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut choisi n’est pas valide.',
        ]);

        $avis->update([
            'statut' => $data['statut'],
            'modere_le' => now(),
        ]);

        return back()->with('success', 'Avis mis à jour.');
    }

    public function destroy(Avis $avis)
    {
        Gate::authorize('delete', $avis);

        $avis->delete();

        return back()->with('success', 'Avis supprimé.');
    }
}
