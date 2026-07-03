<?php

namespace Morfeditorial\TelegramBotBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class TelegramWebAppAuthenticator extends AbstractAuthenticator
{
    private string $botToken;

    public function __construct(string $botToken)
    {
        $this->botToken = $botToken;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-Telegram-Init-Data') || str_starts_with($request->headers->get('Authorization', ''), 'tma ');
    }

    public function authenticate(Request $request): Passport
    {
        $initData = $request->headers->get('X-Telegram-Init-Data');

        if (!$initData) {
            $authHeader = $request->headers->get('Authorization', '');
            if (str_starts_with($authHeader, 'tma ')) {
                $initData = substr($authHeader, 4);
            }
        }

        if (!$initData) {
            throw new CustomUserMessageAuthenticationException('No Telegram initData provided.');
        }

        $userData = $this->validateInitData($initData);

        if (!$userData || !isset($userData['id'])) {
            throw new CustomUserMessageAuthenticationException('Invalid Telegram initData signature.');
        }

        $telegramId = (string) $userData['id'];

        return new Passport(
            new UserBadge($telegramId, function ($identifier) {
                // Return a wrapper user object, or let the user provider handle it.
                // However, without a custom user provider, UserBadge will just call the default one with this identifier.
                // We will leave the closure empty so it uses the firewall's User Provider.
            }),
            new CustomCredentials(function () {
                return true; // The cryptography validation already proved their identity
            }, null)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Let the request continue to the controller
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(json_encode(['error' => $exception->getMessage()]), Response::HTTP_UNAUTHORIZED, [
            'Content-Type' => 'application/json',
        ]);
    }

    private function validateInitData(string $initData): ?array
    {
        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            return null;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);
        $dataCheckString = [];
        foreach ($data as $key => $value) {
            $dataCheckString[] = $key.'='.$value;
        }
        $dataCheckString = implode("\n", $dataCheckString);

        $secretKey = hash_hmac('sha256', $this->botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        if (hash_equals($calculatedHash, $hash)) {
            return json_decode($data['user'] ?? '{}', true);
        }

        return null;
    }
}
