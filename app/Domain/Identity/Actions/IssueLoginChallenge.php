<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Invalidates any outstanding challenge for the address and stores a fresh one.
 *
 * @return array{code: string, token: string}
 */
final class IssueLoginChallenge
{
    /**
     * @return array{code: string, token: string}
     */
    public function __invoke(string $email, ?string $name): array
    {
        LoginChallenge::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        LoginChallenge::query()->create([
            'email' => $email,
            'name' => $name,
            'code_hash' => Hash::make($code),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
            'ip_address' => request()->ip(),
        ]);

        return ['code' => $code, 'token' => $token];
    }
}
