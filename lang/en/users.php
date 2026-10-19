<?php

return [
    'title' => 'Users',
    'subtitle' => 'Everyone who can sign in to the back office, and the accounts of people who have left.',

    'tabs' => [
        'active' => 'Active',
        'archived' => 'Archive',
    ],

    'roles' => [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'agent' => 'Agent',
    ],

    'form' => [
        'name_label' => 'Name',
        'name_placeholder' => 'As on contracts, e.g. Somchai Jaidee',
        'username_label' => 'Username',
        'username_hint' => 'What they type to sign in: letters, numbers, - and _ only.',
        'email_label' => 'Email',
        'email_hint' => 'Where the set-password link goes.',
        'role_label' => 'Role',
        'phone_label' => 'Phone',
        'cancel' => 'Cancel',
    ],

    'table' => [
        'user' => 'User',
        'role' => 'Role',
        'phone' => 'Phone',
        'last_seen' => 'Last seen',
        'last_login' => 'Last sign-in',
        'online' => 'Online',
        'joined' => 'Joined',
        'archived_at' => 'Archived on',
        'actions' => 'Actions',
        'search_placeholder' => 'Search by name, email or username…',
        'filter_role' => 'Filter by role',
        'all_roles' => 'All roles',
        'invite_user' => 'Invite user',
        'edit_user' => 'Edit',
        'archive_user' => 'Archive',
        'restore_user' => 'Restore',
        'no_active_users' => 'No active accounts.',
        'no_archived_users' => 'No archived accounts.',
        'cannot_archive_self' => 'You cannot archive your own account.',
        'cannot_archive_last_admin' => 'This is the last administrator; archiving it would lock everyone out.',
        'archived_success' => 'Account archived.',
        'restored_success' => 'Account restored.',
        'updated_success' => 'Account updated.',
        'invited_success' => 'Account created and the sign-in link sent.',
        'invite_email_failed' => 'Account created, but the email did not go out (:reason).',
    ],

    'edit' => [
        'title' => 'Edit user',
        'save' => 'Save changes',
        'cannot_demote_last_admin' => 'This is the last administrator; its role cannot be changed.',
    ],

    'invite' => [
        'title' => 'Invite user',
        'intro' => 'They get an email with a link to set their own password.',
        'send' => 'Send invitation',
    ],
];
