<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | The Client ID can be found in the OAuth Credentials under Service Account
    |
    */
    'client_id' => 'something.apps.googleusercontent.com',

    /*
    |--------------------------------------------------------------------------
    | Service account name
    |--------------------------------------------------------------------------
    |
    | The Service account name is the Email Address that can be found in the
    | OAuth Credentials under Service Account
    |
    */
    'service_account_name' => 'something@developer.gserviceaccount.com',

    /*
    |--------------------------------------------------------------------------
    | Owner account name
    |--------------------------------------------------------------------------
    |
    | Owner account name is the Email Address of the real owner of the calendar.
    | The service account is a 'virtual' account, that owns the calendars.
    |
    */
        'owner_account_name' => 'something@gmail.com',


    /*
    |--------------------------------------------------------------------------
    | Key file location
    |--------------------------------------------------------------------------
    |
    | This is the location of the .p12 file from the Laravel root directory
    |
    */
    'key_file_location' => '/resources/assets/GoogleCert.p12',
];