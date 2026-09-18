<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => true,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [

        'super-admin' => [

            'users' => 'c,r,u,d',
            'customers' => 'c,r,u,d',
            'shipments' => 'c,r,u,d',
            'quotations' => 'c,r,u,d',
            'documents' => 'c,r,u,d',
            'notifications' => 'c,r,u,d',
            'profile' => 'r,u',

        ],

        'admin' => [

            'users' => 'c,r,u,d',
            'customers' => 'c,r,u,d',
            'shipments' => 'c,r,u,d',
            'quotations' => 'c,r,u,d',
            'documents' => 'c,r,u,d',
            'notifications' => 'c,r,u,d',
            'profile' => 'r,u',

        ],

        'operations' => [

            'shipments' => 'c,r,u,d',
            'documents' => 'c,r,u,d',
            'notifications' => 'r,u',
            'profile' => 'r,u',

        ],

        'sales' => [

            'customers' => 'c,r,u,d',
            'quotations' => 'c,r,u,d',
            'notifications' => 'r,u',
            'profile' => 'r,u',

        ],

        'customer' => [

            'shipments' => 'r',
            'quotations' => 'c,r',
            'documents' => 'r',
            'notifications' => 'r,u',
            'profile' => 'r,u',

        ],

    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
