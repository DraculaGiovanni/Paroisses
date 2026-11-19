<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreAvisRequest;
use App\Models\Avis;
use App\Models\Paroisse;

class AvisController extends Controller
{
    public function store(StoreAvisRequest $request, Paroisse $paroisse)
    {
        Avis::create([
            'paroisse_id' => $paroisse->id,
            'message' => $request->validated('message'),
            'statut' => 'en_attente',
        ]);

        return back()->with('success', 'Merci, votre avis a été transmis à la paroisse.');
    }
}
