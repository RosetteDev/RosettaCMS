<?php

namespace App\Services;

use InvalidArgumentException;

class IpMatchService
{
    /**
     * Check whether an IP address matches a CIDR range (or an exact IP).
     */
    public function matches(string $ip, string $cidr): bool
    {
        $ip = trim($ip);

        if ($ip === '') {
            return false;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address: ' . $ip);
        }

        $this->validate($cidr);

        return \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, trim($cidr));
    }

    /**
     * Validate a CIDR string (or exact IP), throwing on invalid input.
     */
    public function validate(string $cidr): string
    {
        $cidr = trim($cidr);

        if ($cidr === '') {
            throw new InvalidArgumentException('CIDR is required.');
        }

        $parts = explode('/', $cidr);
        $network = trim($parts[0]);

        if (!filter_var($network, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid network address: ' . $network);
        }

        if (isset($parts[1])) {
            $bits = trim($parts[1]);
            $max = filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 32 : 128;

            if (!ctype_digit($bits) || (int) $bits < 0 || (int) $bits > $max) {
                throw new InvalidArgumentException('Invalid CIDR mask: ' . $bits);
            }
        }

        return $cidr;
    }
}
