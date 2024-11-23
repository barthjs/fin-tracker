<?php

return [

    'slug' => 'users',
    'navigation_label' => 'Users',
    'profile' => 'profile',
    'unverified_message' => 'Please change the password',

    'buttons' => [
        'create_button_label' => 'New user',
        'create_heading' => 'Create new user',
        'edit_heading' => 'Edit user',
        'delete_heading' => 'Delete user',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'password_confirmation_warning' => 'Passwords do not match.',
    ],

    'columns' => [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'full_name' => 'Name',
        'name' => 'Username',
        'name_unique_warning' => 'Username already taken',
        'email' => 'Email',
        'email_unique_warning' => 'Email already taken',
        'verified' => 'Verified',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'verified' => 'Verified',
        'unverified' => 'Unverified',
    ],

    'login' => [
        'user_or_email' => 'Username or email',
    ],

    'empty' => 'No users found'

];
