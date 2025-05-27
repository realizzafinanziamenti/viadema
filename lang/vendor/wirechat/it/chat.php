<?php

return [

    /**-------------------------
     * Chat
     *------------------------*/
    'labels' => [
        'you_replied_to_yourself' => 'Hai risposto a te stesso',
        'participant_replied_to_you' => ':sender ha risposto a te',
        'participant_replied_to_themself' => ':sender ha risposto a se stesso',
        'participant_replied_other_participant' => ':sender ha risposto a :receiver',
        'you' => 'Tu',
        'user' => 'Utente',
        'replying_to' => 'Rispondendo a :participant',
        'replying_to_yourself' => 'Rispondendo a te stesso',
        'attachment' => 'Allegato',
    ],

    'inputs' => [
        'message' => [
            'label' => 'Messaggio',
            'placeholder' => 'Scrivi un messaggio',
        ],
        'media' => [
            'label' => 'Media',
            'placeholder' => 'Media',
        ],
        'files' => [
            'label' => 'File',
            'placeholder' => 'File',
        ],
    ],

    'message_groups' => [
        'today' => 'Oggi',
        'yesterday' => 'Ieri',
    ],

    'actions' => [
        'open_group_info' => [
            'label' => 'Info Gruppo',
        ],
        'open_chat_info' => [
            'label' => 'Info Chat',
        ],
        'close_chat' => [
            'label' => 'Chiudi Chat',
        ],
        'clear_chat' => [
            'label' => 'Cancella Cronologia Chat',
            'confirmation_message' => 'Sei sicuro di voler cancellare la cronologia della chat? Questo cancellerà solo la tua chat e non influenzerà gli altri partecipanti.',
        ],
        'delete_chat' => [
            'label' => 'Elimina Chat',
            'confirmation_message' => 'Sei sicuro di voler eliminare questa chat? Questo rimuoverà la chat solo dal tuo lato e non la eliminerà per gli altri partecipanti.',
        ],
        'delete_for_everyone' => [
            'label' => 'Elimina per tutti',
            'confirmation_message' => 'Sei sicuro?',
        ],
        'delete_for_me' => [
            'label' => 'Elimina per me',
            'confirmation_message' => 'Sei sicuro?',
        ],
        'reply' => [
            'label' => 'Rispondi',
        ],
        'exit_group' => [
            'label' => 'Esci dal gruppo',
            'confirmation_message' => 'Sei sicuro di voler uscire da questo gruppo?',
        ],
        'upload_file' => [
            'label' => 'File',
        ],
        'upload_media' => [
            'label' => 'Foto e Video',
        ],
    ],

    'messages' => [
        'cannot_exit_self_or_private_conversation' => 'Non puoi uscire da una conversazione privata o con te stesso',
        'owner_cannot_exit_conversation' => 'Il proprietario non può uscire dalla conversazione',
        'rate_limit' => 'Troppi tentativi! Rallenta, per favore',
        'conversation_not_found' => 'Conversazione non trovata.',
        'conversation_id_required' => 'È richiesto un ID conversazione',
        'invalid_conversation_input' => 'Input conversazione non valido.',
    ],

    /**-------------------------
     * Info Component
     *------------------------*/

    'info' => [
        'heading' => [
            'label' => 'Info Chat',
        ],
        'actions' => [
            'delete_chat' => [
                'label' => 'Elimina Chat',
                'confirmation_message' => 'Sei sicuro di voler eliminare questa chat? Questo rimuoverà la chat solo dal tuo lato e non la eliminerà per gli altri partecipanti.',
            ],
        ],
        'messages' => [
            'invalid_conversation_type_error' => 'Sono consentite solo conversazioni private o con se stessi',
        ],
    ],

    /**-------------------------
     * Group Folder
     *------------------------*/

    'group' => [

        // Group info component
        'info' => [
            'heading' => [
                'label' => 'Info Gruppo',
            ],
            'labels' => [
                'members' => 'Membri',
                'add_description' => 'Aggiungi una descrizione al gruppo',
            ],
            'inputs' => [
                'name' => [
                    'label' => 'Nome gruppo',
                    'placeholder' => 'Inserisci nome',
                ],
                'description' => [
                    'label' => 'Descrizione',
                    'placeholder' => 'Opzionale',
                ],
                'photo' => [
                    'label' => 'Foto',
                ],
            ],
            'actions' => [
                'delete_group' => [
                    'label' => 'Elimina Gruppo',
                    'confirmation_message' => 'Sei sicuro di voler eliminare questo gruppo?',
                    'helper_text' => 'Prima di poter eliminare il gruppo, devi rimuovere tutti i membri.',
                ],
                'add_members' => [
                    'label' => 'Aggiungi Membri',
                ],
                'group_permissions' => [
                    'label' => 'Permessi Gruppo',
                ],
                'exit_group' => [
                    'label' => 'Esci dal gruppo',
                    'confirmation_message' => 'Sei sicuro di voler uscire dal gruppo?',
                ],
            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Sono consentite solo conversazioni di gruppo',
            ],
        ],
        // Members component
        'members' => [
            'heading' => [
                'label' => 'Membri',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Cerca',
                    'placeholder' => 'Cerca membri',
                ],
            ],
            'labels' => [
                'members' => 'Membri',
                'owner' => 'Proprietario',
                'admin' => 'Amministratore',
                'no_members_found' => 'Nessun membro trovato',
            ],
            'actions' => [
                'send_message_to_yourself' => [
                    'label' => 'Messaggia te stesso',
                ],
                'send_message_to_member' => [
                    'label' => 'Messaggia :member',
                ],
                'dismiss_admin' => [
                    'label' => 'Rimuovi da amministratore',
                    'confirmation_message' => 'Sei sicuro di voler rimuovere :member come amministratore?',
                ],
                'make_admin' => [
                    'label' => 'Rendi amministratore',
                    'confirmation_message' => 'Sei sicuro di voler rendere :member amministratore?',
                ],
                'remove_from_group' => [
                    'label' => 'Rimuovi',
                    'confirmation_message' => 'Sei sicuro di voler rimuovere :member da questo gruppo?',
                ],
                'load_more' => [
                    'label' => 'Carica altri',
                ],
            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Sono consentite solo conversazioni di gruppo',
            ],
        ],
        // add-Members component
        'add_members' => [
            'heading' => [
                'label' => 'Aggiungi Membri',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Cerca',
                    'placeholder' => 'Cerca',
                ],
            ],
            'labels' => [],
            'actions' => [
                'save' => [
                    'label' => 'Salva',
                ],
            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Sono consentite solo conversazioni di gruppo',
                'members_limit_error' => 'I membri non possono superare :count',
                'member_already_exists' => 'Già aggiunto al gruppo',
            ],
        ],
        // permissions component
        'permisssions' => [
            'heading' => [
                'label' => 'Permessi',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Cerca',
                    'placeholder' => 'Cerca',
                ],
            ],
            'labels' => [
                'members_can' => 'I membri possono',
            ],
            'actions' => [
                'edit_group_information' => [
                    'label' => 'Modifica informazioni gruppo',
                    'helper_text' => 'Questo include nome, icona e descrizione',
                ],
                'send_messages' => [
                    'label' => 'Inviare messaggi',
                ],
                'add_other_members' => [
                    'label' => 'Aggiungere altri membri',
                ],
            ],
            'messages' => [],
        ],

    ],

];
