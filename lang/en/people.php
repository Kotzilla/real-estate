<?php

return [
    'created' => 'Contact added.',
    'updated' => 'Contact saved.',

    'types' => ['owner' => 'Owner', 'customer' => 'Customer', 'agent' => 'Outside agent'],
    'lists' => ['all' => 'Contacts', 'owner' => 'Owners', 'customer' => 'Customers', 'agent' => 'Outside agents'],

    'table' => [
        'name' => 'Name',
        'type' => 'Type',
        'contact' => 'Phone / email',
        'nationality' => 'Nationality',
        'properties' => 'Properties',
        'contracts' => 'Contracts',
        'actions' => 'Actions',
        'view' => 'View',
        'edit' => 'Edit',
        'add' => 'Add contact',
        'empty' => 'No contacts match.',
        'search_placeholder' => 'Name, phone, email, ID or passport…',
    ],

    'create' => ['title' => 'Add contact'],

    'show' => [
        'properties' => 'Properties owned',
        'no_properties' => 'Owns no listings.',
        'new_property' => 'Add their property',
    ],

    'form' => [
        'details' => 'Personal details',
        'contact' => 'Contact and address',
        'bank' => 'Bank account',
        'type' => 'Type',
        'first_name' => 'First name (English)',
        'last_name' => 'Last name (English)',
        'first_name_th' => 'First name (Thai)',
        'last_name_th' => 'Last name (Thai)',
        'nickname' => 'Nickname',
        'nationality' => 'Nationality',
        'id_card_no' => 'Thai ID card no.',
        'passport_no' => 'Passport no.',
        'birth_on' => 'Date of birth',
        'birth_on_hint' => 'The contracts print the age.',
        'phone' => 'Phone',
        'email' => 'Email',
        'line_id' => 'LINE ID',
        'whatsapp' => 'WhatsApp',
        'address' => 'Address (English)',
        'address_th' => 'Address (Thai)',
        'bank_name' => 'Bank',
        'bank_account_name' => 'Account name',
        'bank_account_no' => 'Account no.',
        'remark' => 'Internal note',
        'remark_hint' => 'Never printed on a contract.',
    ],
];
