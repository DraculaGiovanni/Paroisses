<?php

namespace App\Support;

enum AnnouncementCategory: string
{
    case Liturgie = 'liturgie';
    case Sacrements = 'sacrements';
    case Catechese = 'catechese';
    case Evenements = 'evenements';
    case Solidarite = 'solidarite';
    case Administration = 'administration';
    case Urgence = 'urgence';

    public function label(): string
    {
        return match ($this) {
            self::Liturgie => 'Liturgie',
            self::Sacrements => 'Sacrements',
            self::Catechese => 'Catéchèse',
            self::Evenements => 'Événements',
            self::Solidarite => 'Solidarité',
            self::Administration => 'Administration',
            self::Urgence => 'Urgence',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Liturgie => '#7C3AED',
            self::Sacrements => '#2563EB',
            self::Catechese => '#0F766E',
            self::Evenements => '#C2410C',
            self::Solidarite => '#15803D',
            self::Administration => '#475569',
            self::Urgence => '#DC2626',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $category): string => $category->value,
            self::cases()
        );
    }
}
