<?php

namespace Morfeditorial\TelegramBotBundle\Security;

use Morfeditorial\TelegramBotBundle\Event\TelegramUserAuthenticatedEvent;
use Morfeditorial\TelegramBotBundle\InitData\TelegramInitDataResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TelegramWebAppAuthenticator extends AbstractAuthenticator
{
    private TelegramInitDataResolver $resolver;
    private EventDispatcherInterface $eventDispatcher;
    private string $loginUrl;

    public function __construct(string $botToken, EventDispatcherInterface $eventDispatcher, string $loginUrl = '/login')
    {
        $this->resolver = new TelegramInitDataResolver($botToken);
        $this->eventDispatcher = $eventDispatcher;
        $this->loginUrl = $loginUrl;
    }

    public function supports(Request $request): ?bool
    {
        return $this->resolver->requestHasInitData($request);
    }

    public function authenticate(Request $request): Passport
    {
        $initData = $this->resolver->extractRaw($request);

        if (!$initData) {
            throw new CustomUserMessageAuthenticationException('No Telegram initData provided.');
        }

        $userData = $this->resolver->validate($initData);

        if (!$userData || !isset($userData['id'])) {
            throw new CustomUserMessageAuthenticationException('Invalid Telegram initData signature.');
        }

        $event = new TelegramUserAuthenticatedEvent($userData);
        $this->eventDispatcher->dispatch($event);

        $user = $event->getUser();

        if (!$user) {
            // Provide a generic fallback or throw an error if the host app didn't set a user
            throw new CustomUserMessageAuthenticationException('Telegram user authenticated, but no User entity was provided by the host application.');
        }

        return new Passport(
            new UserBadge($user->getUserIdentifier(), function () use ($user) {
                return $user;
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
        if ($request->isXmlHttpRequest() || str_starts_with($request->getPathInfo(), '/api/')) {
            return new Response(json_encode(['error' => $exception->getMessage()]), Response::HTTP_UNAUTHORIZED, [
                'Content-Type' => 'application/json',
            ]);
        }

        return new RedirectResponse($this->loginUrl);
    }
}
