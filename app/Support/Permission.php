<?php

namespace App\Support;

enum Permission: string
{
    case AnnoncesGerer = 'annonces.gerer';
    case HorairesGerer = 'horaires.gerer';
    case IntentionsGerer = 'intentions.gerer';
    case QuetesGerer = 'quetes.gerer';
    case SacrementsGerer = 'sacrements.gerer';
    case LiturgieGerer = 'liturgie.gerer';
    case MessagesLire = 'messages.lire';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases()
        );
    }

    /**
     * @return list<string>
     */
    public static function defaultForSecretariat(): array
    {
        return [
            self::AnnoncesGerer->value,
            self::HorairesGerer->value,
            self::IntentionsGerer->value,
        ];
    }
}
