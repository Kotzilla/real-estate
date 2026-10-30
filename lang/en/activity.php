<?php

return [
    'title' => 'Activity log',
    'search_placeholder' => 'Search user or IP',
    'all_actions' => 'All actions',
    'when' => 'When',
    'user' => 'User',
    'action' => 'Action',
    'record' => 'Record',
    'changes' => 'Changes',
    'from' => 'IP / device',
    'empty' => 'No activity yet.',

    // App\Traits\LogActivity codes; a code without a label shows as is
    'actions' => [
        'login' => 'Signed in',
        'logout' => 'Signed out',
        'login-failed' => 'Failed sign-in',
        'password-set' => 'Set password',
        'property-create' => 'Added property',
        'property-update' => 'Edited property',
        'person-create' => 'Added contact',
        'person-update' => 'Edited contact',
        'contract-create' => 'Added contract',
        'contract-update' => 'Edited contract',
        'contract-print' => 'Printed contract',
        'enquiry-update' => 'Changed enquiry status',
        'comment-create' => 'Added note',
        'user-invite' => 'Invited user',
        'user-update' => 'Edited user',
        'user-archive' => 'Archived user',
        'user-restore' => 'Restored user',
        'permission-grant' => 'Granted permission',
        'permission-revoke' => 'Revoked permission',
    ],
];
