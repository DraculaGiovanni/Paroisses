<?php

namespace App\Notifications;

/**
 * Classe abstraite pour toutes les notifications de la plateforme.
 *
 * Canaux supportés (gratuits uniquement):
 * - Page publique (affichage sur /p/{slug})
 * - WhatsApp (lien vers canal WhatsApp)
 * - Web Push (notifications navigateur via PWA)
 *
 * IMPORTANT: Pas de SMS (coûteux).
 */
abstract class BaseNotification
{
    /**
     * Canaux disponibles pour les notifications.
     */
    public const CHANNEL_PUBLIC = 'public';        // Affichage sur page publique

    public const CHANNEL_WHATSAPP = 'whatsapp';    // Lien WhatsApp

    public const CHANNEL_WEB_PUSH = 'web_push';    // PWA notifications

    /**
     * Retourne les canaux actifs pour cette notification.
     */
    abstract public function via(): array;

    /**
     * Données pour le canal 'public' (affichage sur page).
     */
    public function toPublic(): ?array
    {
        return null;
    }

    /**
     * Données pour le canal 'whatsapp' (génération de lien).
     */
    public function toWhatsApp(): ?array
    {
        return null;
    }

    /**
     * Données pour le canal 'web_push' (notification navigateur).
     */
    public function toWebPush(): ?array
    {
        return null;
    }
}
