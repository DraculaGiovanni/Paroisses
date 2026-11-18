<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateParoisseRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParoisseSettingsController extends Controller
{
    public function edit(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($request->user()->paroisse_id, 403);

        return Inertia::render('Admin/Paroisse/Edit', [
            'paroisse' => $request->user()->paroisse,
        ]);
    }

    public function update(UpdateParoisseRequest $request)
    {
        $paroisse = $request->user()->paroisse;

        $paroisse->update($request->validated());

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
