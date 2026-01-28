<?php

declare(strict_types=1);

return [

    'change_password_message' => 'Please change your password to continue.',

    'set_password_to_convert' => 'Set a password to enable login via username and password.',

    'oidc' => [
        'heading' => 'Connected Accounts',
        'no_providers_connected' => 'No external providers linked',
        'connected' => 'Connected',
        'not_connected' => 'Not connected',
        'connect' => 'Connect account',
        'remove' => 'Disconnect account',
        'removed' => 'The provider has been successfully disconnected.',
        'cannot_remove_last_method' => 'You cannot remove your last remaining login method.',
        'auth_failed_title' => 'Authentication via :provider failed.',
    ],

    'api_tokens' => [
        'heading' => 'API Tokens',
        'delete' => 'Delete',
        'deleted' => 'Token revoked',
        'empty' => 'You haven\'t created any API tokens yet.',
        'expired' => 'Expired',
        'create' => 'Create API Token',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
        'name' => 'Token name',
        'abilities' => 'Abilities',
        'read' => 'Read',
        'write' => 'Read & Write',
        'expires_at' => 'Expiration date',
        'min_abilities' => 'Please select at least one ability.',
        'token_value' => 'Your API Token',
        'token_warning' => 'Copy this token now. It will never be shown again.',
        'close' => 'Close',
    ],

    'sessions' => [
        'heading' => 'Devices & Sessions',
        'delete' => 'Log Out Other Browser Sessions',
        'unknown_platform' => 'Unknown platform',
        'unknown_browser' => 'Unknown browser',
        'this_device' => 'This device',
        'last_active' => 'Last active',
        'logout_success' => 'All other browser sessions have been logged out successfully.',
    ],

    'delete_account' => 'Delete Account',
    'delete_account_section_description' => 'Permanently delete your account.',
    'delete_account_modal_description' => 'Once your account is deleted, all of your data will be permanently erased. Before deleting your account, please download any data or information you wish to keep.',

];
