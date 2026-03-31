<?php

namespace Diegokock\SdkSicoob\Helpers;

/**
 * Cache de token em memória por client_id.
 * 
 * Token do Sicoob dura 300s — renova com 30s de margem.
 */
class TokenCache
{
    private static array $cache = [];

    public static function get(string $clientId): ?object
    {
        if (!isset(self::$cache[$clientId])) {
            return null;
        }

        $entry = self::$cache[$clientId];

        if (time() >= ($entry->expires_at - 30)) {
            unset(self::$cache[$clientId]);
            return null;
        }

        return $entry->token;
    }

    public static function set(string $clientId, object $token): void
    {
        $entry             = new \stdClass();
        $entry->token      = $token;
        $entry->expires_at = time() + ($token->expires_in ?? 300);

        self::$cache[$clientId] = $entry;
    }

    public static function clear(string $clientId): void
    {
        unset(self::$cache[$clientId]);
    }
}