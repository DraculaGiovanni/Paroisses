<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Annonce\StoreAnnonceRequest;
use App\Http\Requests\Admin\Annonce\UpdateAnnonceRequest;
use App\Models\Annonce;
use App\Support\AnnouncementCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AnnonceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Annonce::class);

        $query = Annonce::query();
        $status = $request->string('statut')->toString();
        $category = $request->string('categorie')->toString();

        if ($category !== '') {
            $query->where('categorie', $category);
        }

        if ($status === 'brouillon') {
            $query->whereNull('publie_le');
        } elseif ($status === 'programmee') {
            $query->whereNotNull('publie_le')->where('publie_le', '>', now());
        } elseif ($status === 'archivee') {
            $query->whereNotNull('archivee_le');
        } elseif ($status === 'expiree') {
            $query->whereNotNull('expire_le')->where('expire_le', '<=', now());
        } elseif ($status === 'publiee') {
            $query->whereNotNull('publie_le')->where('publie_le', '<=', now())
                ->where(function ($query): void {
                    $query->whereNull('expire_le')->orWhere('expire_le', '>', now());
                })
                ->whereNull('archivee_le');
        }

        return Inertia::render('Admin/Annonces/Index', [
            'annonces' => $query->orderByDesc('epinglee')
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString(),
            'categories' => collect(AnnouncementCategory::cases())->map(fn (AnnouncementCategory $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
                'color' => $category->color(),
            ])->values(),
            'filters' => ['statut' => $status, 'categorie' => $category],
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Annonce::class);

        return Inertia::render('Admin/Annonces/Form', [
            'annonce' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreAnnonceRequest $request)
    {
        Gate::authorize('create', Annonce::class);

        $data = $request->validated();
        $publishedAt = ($data['publier'] ?? false)
            ? ($data['publie_le'] ?? now())
            : null;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store(
                'annonces/'.$request->user()->paroisse_id,
                'public',
            );
        }

        Annonce::create([
            'image_path' => $imagePath,
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'categorie' => $data['categorie'] ?? 'administration',
            'epinglee' => (($data['categorie'] ?? 'administration') === 'urgence') || ($data['epinglee'] ?? false),
            'notifier' => $data['notifier'] ?? true,
            'publie_le' => $publishedAt,
            'expire_le' => $data['expire_le'] ?? null,
            'responsable' => $data['responsable'] ?? null,
            'actif' => $data['actif'] ?? true,
        ]);

        return redirect()->route('admin.annonces.index')->with('success', 'Annonce enregistrée.');
    }

    public function edit(Annonce $annonce)
    {
        Gate::authorize('update', $annonce);

        return Inertia::render('Admin/Annonces/Form', [
            'annonce' => $annonce,
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateAnnonceRequest $request, Annonce $annonce)
    {
        Gate::authorize('update', $annonce);

        $data = $request->validated();
        $publishedAt = ($data['publier'] ?? false)
            ? ($data['publie_le'] ?? ($annonce->publie_le ?? now()))
            : null;

        $imagePath = $annonce->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store(
                'annonces/'.$request->user()->paroisse_id,
                'public',
            );
        } elseif (($data['supprimer_image'] ?? false) && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        $annonce->update([
            'image_path' => $imagePath,
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'categorie' => $data['categorie'] ?? $annonce->categorie,
            'epinglee' => (($data['categorie'] ?? $annonce->categorie) === 'urgence') || ($data['epinglee'] ?? false),
            'notifier' => $data['notifier'] ?? $annonce->notifier,
            'publie_le' => $publishedAt,
            'expire_le' => $data['expire_le'] ?? null,
            'archivee_le' => $data['archivee_le'] ?? null,
            'responsable' => $data['responsable'] ?? null,
            'actif' => $data['actif'] ?? $annonce->actif,
        ]);

        return redirect()->route('admin.annonces.index')->with('success', 'Annonce modifiée.');
    }

    public function destroy(Annonce $annonce)
    {
        Gate::authorize('delete', $annonce);

        if ($annonce->image_path) {
            Storage::disk('public')->delete($annonce->image_path);
        }

        $annonce->delete();

        return back()->with('success', 'Annonce supprimée.');
    }

    public function toggleArchive(Annonce $annonce)
    {
        Gate::authorize('update', $annonce);
        $annonce->update([
            'archivee_le' => $annonce->archivee_le === null ? now() : null,
        ]);

        return back()->with('success', $annonce->archivee_le === null
            ? 'Annonce désarchivée.'
            : 'Annonce archivée.');
    }

    /**
     * @return array<int, array{value: string, label: string, color: string}>
     */
    private function categories(): array
    {
        return collect(AnnouncementCategory::cases())->map(fn (AnnouncementCategory $category): array => [
            'value' => $category->value,
            'label' => $category->label(),
            'color' => $category->color(),
        ])->values()->all();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'contenu' => ['required', 'string', 'max:5000'],
            'epinglee' => ['boolean'],
            'publier' => ['boolean'],
        ]);
    }
}
