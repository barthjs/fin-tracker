<?php

declare(strict_types=1);

return [

    'label' => 'User',
    'plural_label' => 'Users',
    'slug' => 'users',
    'profile-slug' => 'profile',

    'fields' => [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'full_name' => 'Name',
        'username' => 'Username',
        'username_or_email' => 'Username or email',
        'avatar' => 'Avatar',
        'is_verified' => 'Verified',
        'is_admin' => 'Administrator',
    ],

    'change_password_message' => 'Please change your password to continue.',

    'sessions' => [
        'heading' => 'Devices & Sessions',
        'delete' => 'Log Out Other Browser Sessions',
        'unknown_platform' => 'Unknown platform',
        'unknown_browser' => 'Unknown browser',
        'this_device' => 'This device',
        'last_active' => 'Last active',
        'logout_success' => 'All other browser sessions have been logged out successfully.',
    ],

];
