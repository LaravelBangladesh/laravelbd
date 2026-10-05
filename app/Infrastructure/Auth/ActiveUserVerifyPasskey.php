<?php

namespace App\Infrastructure\Auth;

use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Passkey;
use Webauthn\PublicKeyCredential;

/**
 * A deactivated user's passkeys stay stored so a reactivation brings them
 * back, but they must not sign anyone in. The owner is soft deleted, so the
 * passkey's user relation comes back empty and the login stops here.
 */
class ActiveUserVerifyPasskey extends VerifyPasskey
{
    public function getPasskey(PublicKeyCredential $credential, bool $lock = false): Passkey
    {
        $passkey = parent::getPasskey($credential, $lock);

        if (! $passkey->user()->exists()) {
            throw InvalidPasskeyException::make(__('auth.account_deactivated'));
        }

        return $passkey;
    }
}
