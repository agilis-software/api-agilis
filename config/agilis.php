<?php

return [
    'users' => [
        'avatars' => [
            'folder' => 'users/avatars',
            'default' => 'users/avatars/default.png',
        ],
    ],
    'organizations' => [
        'avatars' => [
            'folder' => 'organizations/avatars',
            'default' => 'organizations/avatars/default.png',
        ],
    ],

    'chat' => [
        'secret' => env('CHAT_SERVER_SECRET', 'API_SECRET'),
    ],
];
