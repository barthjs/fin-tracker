<?php

return [

    'url' => 'users',
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
        'name' => 'Username',
        'email' => 'Email',
        'verified' => 'Verified',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'all' => 'All',
        'verified' => 'Verified',
        'admins' => 'Administrators',
        'users' => 'Users',
    ],

    'login' => [
        'user_or_email' => 'Username or email',
    ],

    'empty' => 'No users found'

];
