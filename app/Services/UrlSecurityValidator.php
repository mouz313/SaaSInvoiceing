<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class UrlSecurityValidator
{
    /**
     * Blocked hostnames known for SSRF or cloud metadata access.
     *
     * @var list<string>
     */
    protected static array $blockedHosts = [
        'localhost',
        '127.0.0.1',
        '0.0.0.0',
        '::1',
        '169.254.169.254',
        'metadata.google.internal',
        'instance-data',
        'metadata.packet.net',
    ];

    /**
     * Check if a webhook URL is safe from SSRF attacks.
     */
    public static function isSafeWebhookUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $allowedSchemes = app()->isLocal() ? ['http', 'https'] : ['https'];

        if (! in_array($scheme, $allowedSchemes, true)) {
            return false;
        }

        $host = strtolower($parts['host']);

        // Check blocked hostnames
        if (in_array($host, static::$blockedHosts, true)) {
            return false;
        }

        // Prohibit numeric/hex/octal encoded loopback IP tricks
        if (is_numeric($host) || str_ends_with($host, '.localhost')) {
            return false;
        }

        // If host is an IP literal
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return static::isPublicIp($host);
        }

        // Resolve DNS to IPv4 addresses
        $ipv4s = @gethostbynamel($host);
        if ($ipv4s === false || empty($ipv4s)) {
            // In testing / local environments, if DNS does not resolve, check scheme and host sanity
            return false;
        }

        foreach ($ipv4s as $ip) {
            if (! static::isPublicIp($ip)) {
                return false;
            }
        }

        // Resolve DNS to IPv6 addresses if available
        $ipv6Records = @dns_get_record($host, DNS_AAAA);
        if (is_array($ipv6Records)) {
            foreach ($ipv6Records as $record) {
                if (! empty($record['ipv6']) && ! static::isPublicIp($record['ipv6'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Assert that a webhook URL is safe, throwing ValidationException if unsafe.
     *
     * @throws ValidationException
     */
    public static function assertSafeWebhookUrl(string $url): void
    {
        if (! static::isSafeWebhookUrl($url)) {
            throw ValidationException::withMessages([
                'url' => ['The webhook URL must be a publicly accessible HTTPS URL and cannot point to internal, reserved, or private network addresses.'],
            ]);
        }
    }

    /**
     * Verify if an IP address is a valid public Internet address.
     */
    public static function isPublicIp(string $ip): bool
    {
        // Must be a valid IP and NOT in private or reserved ranges
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        // Additional link-local and loopback checks
        if (str_starts_with($ip, '127.') || str_starts_with($ip, '169.254.') || str_starts_with($ip, '0.')) {
            return false;
        }

        if ($ip === '::1' || str_starts_with(strtolower($ip), 'fe80:') || str_starts_with(strtolower($ip), 'fc00:')) {
            return false;
        }

        return true;
    }
}
