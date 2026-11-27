<?php

namespace App\Services;

class TwoFactorService
{
    /**
     * Generate a new random base32 secret (RFC 6238 compatible).
     */
    public function generateSecret(): string
    {
        return \OTPHP\TOTP::create()->getSecret();
    }

    /**
     * Build an otpauth:// URI for the authenticator app.
     */
    public function generateUri(string $secret, string $label, string $issuer): string
    {
        $totp = \OTPHP\TOTP::create($secret);
        $totp->setLabel($label);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri();
    }

    /**
     * Verify a 6-digit TOTP code (accepts ±25s of clock drift).
     */
    public function verifyCode(string $secret, string $code): bool
    {
        $code = trim($code);

        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        try {
            return \OTPHP\TOTP::create($secret)->verify($code, null, 25);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
