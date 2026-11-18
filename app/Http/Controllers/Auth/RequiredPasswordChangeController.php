<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateRequiredPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RequiredPasswordChangeController extends Controller
{
    public function edit(): Response
    {
        abort_unless(auth()->user()->must_change_password, 403);

        return Inertia::render('Auth/ChangePassword');
    }

    public function update(UpdateRequiredPasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'must_change_password' => false,
        ])->save();

        return redirect()->route('admin.dashboard')->with('success', 'Mot de passe modifié.');
    }
}
