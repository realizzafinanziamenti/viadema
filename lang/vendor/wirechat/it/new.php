<?php

return [

    // componente nuova chat
    'chat' => [
        'labels' => [
            'heading' => 'Nuova Chat',
            'you' => 'Tu',

        ],

        'inputs' => [
            'search' => [
                'label' => 'Cerca conversazioni',
                'placeholder' => 'Cerca',
            ],
        ],

        'actions' => [
            'new_group' => [
                'label' => 'Nuovo gruppo',
            ],

        ],

        'messages' => [

            'empty_search_result' => 'Nessun utente trovato con la tua ricerca.',
        ],
    ],

    // componente nuovo gruppo
    'group' => [
        'labels' => [
            'heading' => 'Nuova Chat',
            'add_members' => 'Aggiungi Membri',

        ],

        'inputs' => [
            'name' => [
                'label' => 'Nome Gruppo',
                'placeholder' => 'Inserisci nome',
            ],
            'description' => [
                'label' => 'Descrizione',
                'placeholder' => 'Opzionale',
            ],
            'search' => [
                'label' => 'Cerca',
                'placeholder' => 'Cerca',
            ],
            'photo' => [
                'label' => 'Foto',
            ],
        ],

        'actions' => [
            'cancel' => [
                'label' => 'Annulla',
            ],
            'next' => [
                'label' => 'Avanti',
            ],
            'create' => [
                'label' => 'Crea',
            ],

        ],

        'messages' => [
            'members_limit_error' => 'I membri non possono superare :count',
            'empty_search_result' => 'Nessun utente trovato con la tua ricerca.',
        ],
    ],

];
