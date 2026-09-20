<?php

return [
    'login' => 'resources/js/pages/auth/login.tsx',
    'register' => 'resources/js/pages/auth/login.tsx',
    'welcome' => 'resources/js/pages/welcome.tsx',
    'profile' => 'resources/js/pages/account/edit.tsx',
    'security' => 'resources/js/pages/account/edit.tsx',
    'verify_email' => 'resources/js/pages/auth/verify.tsx',
    'two_factor_challenge' => 'resources/js/pages/auth/verify.tsx',
    'confirm_password' => 'resources/js/pages/auth/verify.tsx',
    'auth_types' => 'resources/js/types/auth.ts',

    'two_factor_files' => [
        'resources/js/components/ui/input-otp.tsx',
    ],

    'two_factor_otp_package' => 'input-otp',

    'passkey_files' => [
        'resources/js/components/passkey-item.tsx',
        'resources/js/components/passkey-register.tsx',
        'resources/js/components/passkey-verify.tsx',
        'resources/js/components/manage-passkeys.tsx',
    ],
];
