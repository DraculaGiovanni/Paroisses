<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateParoisseRequest;
use App\Models\Activite;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ParoisseSettingsController extends Controller
{
    public function edit(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($request->user()->paroisse_id, 403);

        $paroisse = $request->user()->paroisse;

        return Inertia::render('Admin/Paroisse/Edit', [
            'paroisse' => array_merge($paroisse->toArray(), [
                'logo_url' => $paroisse->logo_path ? Storage::url($paroisse->logo_path) : null,
                'banniere_url' => $paroisse->banniere_path ? Storage::url($paroisse->banniere_path) : null,
            ]),
        ]);
    }

    public function update(UpdateParoisseRequest $request)
    {
        $paroisse = $request->user()->paroisse;
        $data = $request->validated();

        foreach (['logo', 'banniere'] as $champ) {
            if (! $request->hasFile($champ)) {
                continue;
            }

            if ($paroisse->{$champ.'_path'}) {
                Storage::disk('public')->delete($paroisse->{$champ.'_path'});
            }

            $data[$champ.'_path'] = $request->file($champ)->store(
                'paroisses/'.$paroisse->id,
                'public',
            );
        }

        $paroisse->update($data);

        Activite::create([
            'paroisse_id' => $paroisse->id,
            'user_id' => $request->user()->id,
            'action' => 'paroisse.modifiee',
            'cible_type' => 'Paroisse',
            'cible_id' => $paroisse->id,
        ]);

        return back()->with('success', 'Paramètres enregistrés.');
    }

    /**
     * QR code du lien public de la paroisse, au format SVG.
     */
    public function qrCode(Request $request): Response
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($request->user()->paroisse_id, 403);

        $paroisse = $request->user()->paroisse;
        $url = route('paroisse.show', $paroisse);

        $renderer = new ImageRenderer(
            new RendererStyle(320, 2),
            new SvgImageBackEnd,
        );

        $svg = (new Writer($renderer))->writeString($url);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}
