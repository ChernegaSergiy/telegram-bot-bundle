<?php

declare(strict_types=1);

namespace Morfeditorial\TelegramBotBundle\InitData;

use Symfony\Component\HttpFoundation\Request;

/**
 * Single source of truth for reading and validating Telegram WebApp `initData`.
 *
 * Previously this logic (header/query extraction + HMAC signature check) was
 * copy-pasted independently in TelegramWebAppAuthenticator (security layer)
 * and TelegramPlatformAdapter (presentation layer), with the two copies
 * already out of sync — the adapter never validated the signature at all.
 * Both now delegate here.
 */
final class TelegramInitDataResolver
{
    public function __construct(
        private string $botToken,
    ) {
    }

    /**
     * Pulls the raw initData string off a request, checking (in order) the
     * `X-Telegram-Init-Data` header, an `Authorization: tma <data>` header,
     * the legacy `X-Init-Data` header (still sent by public/js/app.js for
     * background API polling), and an `initData` query parameter. Returns
     * null if none is present.
     */
    public function extractRaw(Request $request): ?string
    {
        $initData = $request->headers->get('X-Telegram-Init-Data');
        if ($initData) {
            return $initData;
        }

        $authHeader = $request->headers->get('Authorization', '');
        if (str_starts_with($authHeader, 'tma ')) {
            return substr($authHeader, 4);
        }

        $legacyHeader = $request->headers->get('X-Init-Data');
        if ($legacyHeader) {
            return $legacyHeader;
        }

        $fromQuery = $request->query->get('initData');

        return \is_string($fromQuery) && '' !== $fromQuery ? $fromQuery : null;
    }

    public function requestHasInitData(Request $request): bool
    {
        return null !== $this->extractRaw($request);
    }

    /**
     * Validates the HMAC signature of a raw initData string against the bot
     * token, per Telegram's WebApp validation scheme. Returns the decoded
     * `user` claim on success, or null if the signature is missing/invalid.
     *
     * @return array<string, mixed>|null
     */
    public function validate(string $initData): ?array
    {
        parse_str($initData, $data);

        if (!isset($data['hash']) || !\is_string($data['hash'])) {
            return null;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $dataCheckString = implode("\n", array_map(
            static fn (string $key, string $value): string => "{$key}={$value}",
            array_keys($data),
            $data,
        ));

        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        if (!hash_equals($calculatedHash, $hash)) {
            return null;
        }

        return json_decode($data['user'] ?? '{}', true) ?: null;
    }

    /**
     * Convenience: extract + validate in one call. Returns the decoded user
     * claim, or null if there was no initData or the signature was invalid.
     *
     * @return array<string, mixed>|null
     */
    public function resolveUser(Request $request): ?array
    {
        $raw = $this->extractRaw($request);

        return null !== $raw ? $this->validate($raw) : null;
    }
}
