<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demandes de messe
    |--------------------------------------------------------------------------
    |
    | Les demandes célébrées, refusées ou annulées depuis plus de ce nombre
    | de jours sont anonymisées par la commande intentions:purger.
    |
    */

    'intentions' => [
        'purge_apres_jours' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages privés
    |--------------------------------------------------------------------------
    |
    | Les messages traités depuis plus de ce nombre de mois sont archivés.
    |
    */

    'messages' => [
        'archiver_apres_mois' => 12,
    ],

];
