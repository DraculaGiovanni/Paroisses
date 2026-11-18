<?php

namespace App\Console\Commands;

use App\Models\IntentionMesse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgerIntentions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intentions:purger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymise les demandes de messe clôturées depuis plus de 90 jours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jours = max(1, (int) config('paroisses.intentions.purge_apres_jours', 90));
        $limite = now()->subDays($jours);
        $anonymisees = 0;

        IntentionMesse::query()
            ->whereIn('statut', ['celebree', 'refusee', 'annulee'])
            ->where('updated_at', '<=', $limite)
            ->where(function ($query): void {
                $query->where('nom_demandeur', '!=', IntentionMesse::NOM_ANONYME)
                    ->orWhereNotNull('telephone');
            })
            ->chunkById(100, function ($intentions) use (&$anonymisees): void {
                foreach ($intentions as $intention) {
                    $intention->updateQuietly([
                        'nom_demandeur' => IntentionMesse::NOM_ANONYME,
                        'telephone' => null,
                    ]);

                    $anonymisees++;
                }
            });

        Log::info('Purge des intentions terminée.', ['anonymisees' => $anonymisees]);

        $this->info($anonymisees.' demande(s) anonymisée(s).');

        return self::SUCCESS;
    }
}
