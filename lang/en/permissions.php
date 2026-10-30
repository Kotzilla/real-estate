<?php

return [
    'title' => 'Permissions',
    'intro' => 'What each role may do. Changes apply at once; admins always hold every permission.',
    'permission' => 'Permission',
    'admin_locked' => 'Admins always have every permission',
    'granted' => ':role can now: :permission',
    'revoked' => ':role can no longer: :permission',

    'modules' => [
        'settings' => ['label' => 'Settings', 'icon' => 'setting-2'],
        'properties' => ['label' => 'Properties', 'icon' => 'home-3'],
        'people' => ['label' => 'Contacts', 'icon' => 'people'],
        'contracts' => ['label' => 'Contracts', 'icon' => 'document'],
        'enquiries' => ['label' => 'Enquiries', 'icon' => 'calendar-tick'],
        'activity' => ['label' => 'Activity log', 'icon' => 'time'],
    ],

    'names' => [
        'settings' => [
            'manage' => ['label' => 'Manage users and permissions', 'hint' => 'Invite and archive staff, change roles, this page.'],
        ],
        'properties' => [
            'view' => ['label' => 'View properties', 'hint' => 'The listings with prices, owners and photos.'],
            'manage' => ['label' => 'Add and edit properties', 'hint' => 'Details, prices, status, facilities and photos.'],
        ],
        'people' => [
            'view' => ['label' => 'View contacts', 'hint' => 'Owners, customers and outside agents with their details.'],
            'manage' => ['label' => 'Add and edit contacts', 'hint' => 'Names, IDs, addresses and owners\' bank accounts.'],
        ],
        'contracts' => [
            'view' => ['label' => 'View contracts', 'hint' => 'Leases and sales, their commission, and printing them.'],
            'manage' => ['label' => 'Add and edit contracts', 'hint' => 'Prepare, sign off, complete or cancel leases and sales.'],
        ],
        'enquiries' => [
            'view' => ['label' => 'View enquiries', 'hint' => 'Viewing requests left on the public listing pages, with name and phone.'],
            'manage' => ['label' => 'Follow up enquiries', 'hint' => 'Change an enquiry\'s status and add follow-up notes.'],
        ],
        'activity' => [
            'view' => ['label' => 'View the activity log', 'hint' => 'Who signed in and what each user changed, with old and new values and IP.'],
        ],
    ],
];
