<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Horaire\StoreHoraireRequest;
use App\Http\Requests\Admin\Horaire\UpdateHoraireRequest;
use App\Models\Horaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HoraireController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Horaire::class);

        return Inertia::render('Admin/Horaires/Index', [
            'horaires' => Horaire::orderBy('jour_semaine')->orderBy('heure')->get(),
            'types' => Horaire::TYPES,
            'jours' => Horaire::JOURS,
        ]);
    }

    public function store(StoreHoraireRequest $request)
    {
        Gate::authorize('create', Horaire::class);

        Horaire::create($request->validated());

        return back()->with('success', 'Horaire ajouté.');
    }

    public function update(UpdateHoraireRequest $request, Horaire $horaire)
    {
        Gate::authorize('update', $horaire);

        $horaire->update($request->validated());

        return back()->with('success', 'Horaire modifié.');
    }

    public function destroy(Horaire $horaire)
    {
        Gate::authorize('delete', $horaire);

        $horaire->delete();

        return back()->with('success', 'Horaire supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(array_keys(Horaire::TYPES))],
            'jour_semaine' => ['required', 'integer', 'between:1,7'],
            'heure' => ['required', 'date_format:H:i'],
            'lieu' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:255'],
            'actif' => ['boolean'],
        ]);
    }
}
