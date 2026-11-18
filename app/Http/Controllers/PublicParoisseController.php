<?php

namespace App\Http\Controllers;

use App\Http\Resources\HoraireResource;
use App\Http\Resources\PublicParoisseResource;
use App\Models\Annonce;
use App\Models\CouleurExceptionnelle;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Support\LiturgicalCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PublicParoisseController extends Controller
{
    private const PUBLIC_CACHE_KEY_VERSION = 'v3';

    private const PUBLIC_CACHE_TTL = 600;

    /**
     * Affiche la page publique d'une paroisse.
     *
     * Performance:
     * - 3 requêtes à froid (paroisse + horaires + annonces)
     * - 1 requête à chaud (liaison de la paroisse)
     * - TTL: 10 minutes
     * - Invalidation automatique via Observers
     */
    public function show(Paroisse $paroisse): Response
    {
        $cacheKey = "paroisse:{$paroisse->id}:public:".self::PUBLIC_CACHE_KEY_VERSION;
        $publicContent = Cache::get($cacheKey);

        if (! $this->isValidCachedPublicContent($publicContent)) {
            $publicContent = $this->loadPublicContent($paroisse);
            Cache::put($cacheKey, $publicContent, self::PUBLIC_CACHE_TTL);
        }

        $now = Carbon::now('Africa/Porto-Novo');
        $horaires = $this->filterValidSchedules($publicContent['horaires'], $now);
        $annonces = $this->filterVisibleAnnouncements($publicContent['annonces'], $now);
        $liturgical = $this->liturgicalForDate($paroisse, $now);

        return Inertia::render('Public/Paroisse', [
            'paroisse' => PublicParoisseResource::make($paroisse)->resolve(request()),
            'couleur_principale' => $paroisse->couleur_principale ?? '#1E40AF',
            'horaires' => $horaires,
            'annonces' => $annonces,
            'prochaine_messe' => $this->findNextMass($horaires, $now),
            'server_time' => $now->toIso8601String(),
            'liturgical' => $liturgical,
        ]);
    }

    public function annonces(Request $request, Paroisse $paroisse): Response
    {
        $now = Carbon::now('Africa/Porto-Novo');
        $cacheKey = "paroisse:{$paroisse->id}:public:".self::PUBLIC_CACHE_KEY_VERSION;
        $publicContent = Cache::get($cacheKey);

        if (! $this->isValidCachedPublicContent($publicContent)) {
            $publicContent = $this->loadPublicContent($paroisse);
            Cache::put($cacheKey, $publicContent, self::PUBLIC_CACHE_TTL);
        }

        $annonces = $this->filterVisibleAnnouncements($publicContent['annonces'], $now);
        $category = $request->string('categorie')->toString();
        if ($category !== '') {
            $annonces = array_values(array_filter(
                $annonces,
                static fn (array $annonce): bool => ($annonce['categorie'] ?? null) === $category
            ));
        }

        $perPage = 15;
        $page = max(1, $request->integer('page', 1));
        $slice = array_slice($annonces, ($page - 1) * $perPage, $perPage);
        $paginator = new Paginator(
            $slice,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Public/Annonces/Index', [
            'paroisse' => PublicParoisseResource::make($paroisse)->resolve(request()),
            'annonces' => $paginator,
            'categorie' => $category !== '' ? $category : null,
        ]);
    }

    public function annonce(Paroisse $paroisse, int $annonce): Response
    {
        $now = Carbon::now('Africa/Porto-Novo');
        $model = Annonce::forParoisse($paroisse->id)
            ->whereKey($annonce)
            ->actives()
            ->whereNotNull('publie_le')
            ->nonArchivees()
            ->firstOrFail();

        if (
            $model->publie_le->isAfter($now)
            || ($model->expire_le !== null && $model->expire_le->isPast())
        ) {
            abort(404);
        }

        return Inertia::render('Public/Annonces/Show', [
            'paroisse' => PublicParoisseResource::make($paroisse)->resolve(request()),
            'annonce' => [
                'id' => $model->id,
                'titre' => $model->titre,
                'contenu' => $model->contenu,
                'categorie' => $model->categorie,
                'categorie_label' => $model->category()->label(),
                'publie_le' => $model->publie_le->format('Y-m-d H:i'),
                'epinglee' => $model->categorie === 'urgence' || $model->epinglee,
            ],
        ]);
    }

    /**
     * @return array{horaires: array<int, array<string, mixed>>, annonces: array<int, array<string, mixed>>}
     */
    private function loadPublicContent(Paroisse $paroisse): array
    {
        $horaires = Horaire::forParoisse($paroisse->id)
            ->actifs()
            ->select(['id', 'type', 'jour_semaine', 'heure', 'heure_fin', 'valide_du', 'valide_au', 'lieu', 'note', 'actif'])
            ->orderBy('jour_semaine')
            ->orderBy('heure')
            ->get();

        $annonces = Annonce::forParoisse($paroisse->id)
            ->whereNotNull('publie_le')
            ->actives()
            ->nonArchivees()
            ->select(['id', 'titre', 'contenu', 'categorie', 'responsable', 'publie_le', 'expire_le', 'archivee_le', 'epinglee', 'date_debut', 'date_fin', 'actif'])
            ->orderByDesc('epinglee')
            ->orderByDesc('publie_le')
            ->get();

        return [
            'horaires' => array_values(HoraireResource::collection($horaires)->resolve(request())),
            'annonces' => array_values($annonces->map(fn (Annonce $annonce): array => [
                'id' => $annonce->id,
                'titre' => $annonce->titre,
                'contenu' => $annonce->contenu,
                'categorie' => $annonce->categorie,
                'categorie_label' => $annonce->category()->label(),
                'publie_le' => $annonce->publie_le?->format('Y-m-d H:i'),
                'expire_le' => $annonce->expire_le?->format('Y-m-d H:i'),
                'archivee_le' => $annonce->archivee_le?->format('Y-m-d H:i'),
                'epinglee' => $annonce->categorie === 'urgence' || $annonce->epinglee,
            ])->all()),
        ];
    }

    private function isValidCachedPublicContent(mixed $publicContent): bool
    {
        if (
            ! is_array($publicContent)
            || array_keys($publicContent) !== ['horaires', 'annonces']
            || ! array_is_list($publicContent['horaires'])
            || ! array_is_list($publicContent['annonces'])
        ) {
            return false;
        }

        return $this->containsOnlyArraysAndScalars($publicContent);
    }

    private function containsOnlyArraysAndScalars(mixed $value): bool
    {
        if (! is_array($value)) {
            return is_scalar($value) || $value === null;
        }

        foreach ($value as $item) {
            if (! $this->containsOnlyArraysAndScalars($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $horaires
     * @return array<int, array<string, mixed>>
     */
    private function filterValidSchedules(array $horaires, Carbon $today): array
    {
        $date = $today->toDateString();

        return array_values(array_filter($horaires, static function (array $horaire) use ($date): bool {
            $validFrom = $horaire['valide_du'] ?? null;
            $validUntil = $horaire['valide_au'] ?? null;

            return ($validFrom === null || $validFrom <= $date)
                && ($validUntil === null || $validUntil >= $date);
        }));
    }

    /**
     * @param  array<int, array<string, mixed>>  $annonces
     * @return array<int, array<string, mixed>>
     */
    private function filterVisibleAnnouncements(array $annonces, Carbon $now): array
    {
        $visible = array_values(array_filter($annonces, static function (array $annonce) use ($now): bool {
            if (($annonce['archivee_le'] ?? null) !== null) {
                return false;
            }

            $publishedAt = $annonce['publie_le'] ?? null;
            if ($publishedAt === null || Carbon::parse($publishedAt, 'Africa/Porto-Novo')->isAfter($now)) {
                return false;
            }

            $expiresAt = $annonce['expire_le'] ?? null;

            return $expiresAt === null || Carbon::parse($expiresAt, 'Africa/Porto-Novo')->isAfter($now);
        }));

        usort($visible, static function (array $left, array $right): int {
            $leftUrgent = ($left['categorie'] ?? null) === 'urgence';
            $rightUrgent = ($right['categorie'] ?? null) === 'urgence';
            if ($leftUrgent !== $rightUrgent) {
                return $leftUrgent ? -1 : 1;
            }

            $leftPinned = (bool) ($left['epinglee'] ?? false);
            $rightPinned = (bool) ($right['epinglee'] ?? false);
            if ($leftPinned !== $rightPinned) {
                return $leftPinned ? -1 : 1;
            }

            return strcmp((string) ($right['publie_le'] ?? ''), (string) ($left['publie_le'] ?? ''));
        });

        return array_slice($visible, 0, 30);
    }

    /**
     * @return array{period: array{key: string, label: string}, color: array{key: string, label: string, hex: string}}
     */
    private function liturgicalForDate(Paroisse $paroisse, Carbon $now): array
    {
        $exception = CouleurExceptionnelle::forParoisse($paroisse->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        $calculated = LiturgicalCalendar::forDate($now);
        if (! $exception) {
            return $calculated;
        }

        $labels = [
            'violet' => 'Violet',
            'rose' => 'Rose',
            'blanc' => 'Blanc',
            'rouge' => 'Rouge',
            'vert' => 'Vert',
            'noir' => 'Noir',
        ];
        $hex = [
            'violet' => '#7C3AED',
            'rose' => '#F472B6',
            'blanc' => '#FFFFFF',
            'rouge' => '#DC2626',
            'vert' => '#15803D',
            'noir' => '#111827',
        ];
        $calculated['color'] = [
            'key' => $exception->couleur,
            'label' => $labels[$exception->couleur] ?? $exception->couleur,
            'hex' => $hex[$exception->couleur] ?? '#111827',
        ];

        if ($exception->libelle !== null) {
            $calculated['period']['label'] = $exception->libelle;
        }

        return $calculated;
    }

    /**
     * @param  array<int, array<string, mixed>>  $horaires
     * @return array<string, mixed>|null
     */
    private function findNextMass(array $horaires, Carbon $now): ?array
    {
        $nextMass = null;
        $nextDayOffset = null;
        $currentDay = $now->dayOfWeekIso;
        $currentTime = $now->format('H:i');

        foreach ($horaires as $horaire) {
            if ($horaire['type'] !== 'messe') {
                continue;
            }

            $start = (string) $horaire['heure'];
            $end = $horaire['heure_fin'] ?? null;
            $dayOffset = (((int) $horaire['jour_semaine'] - $currentDay + 7) % 7);

            if ($dayOffset === 0 && $end !== null && $start <= $currentTime && $currentTime <= $end) {
                return [
                    ...$horaire,
                    'status' => 'en_cours',
                    'until' => $end,
                ];
            }

            if ($dayOffset === 0 && $start <= $currentTime) {
                $dayOffset = 7;
            }

            if (
                $nextMass === null
                || $dayOffset < $nextDayOffset
                || ($dayOffset === $nextDayOffset && $start < $nextMass['heure'])
            ) {
                $nextMass = $horaire;
                $nextDayOffset = $dayOffset;
            }
        }

        return $nextMass ? [
            ...$nextMass,
            'status' => 'a_venir',
        ] : null;
    }
}
