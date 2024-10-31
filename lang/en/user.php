<?php

return [

    'slug' => 'users',
    'navigation_label' => 'Users',

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
        'email' => 'Email',
        'verified' => 'Verified',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        'admins' => 'Administrators',
        'users' => 'Users',
    ],

    'login' => [
        'user_or_email' => 'Username or email',
    ],

    'empty' => 'No users found'

];
