<?php

namespace App\Service;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthService
{
    private Github $github_provider;

    public function __construct()
    {
        $this->github_provider = new Github([
            'clientId' => $_ENV['GITHUB_ID'],
            'clientSecret' => $_ENV['GITHUB_SECRET'],
            'redirectUri' => $_ENV['GITHUB_CALLBACK'],
        ]);
    }

    public function getGithubLoginUrl(): string
    {
        $options = [
            'scope' => ['user', 'repo'],
        ];

        return $this->github_provider->getAuthorizationUrl($options);
    }

    /**
     * @throws IdentityProviderException
     */
    public function handleGithubCallback(string $code, SessionInterface $session): array
    {
        try {
            $token = $this->github_provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            $user = $this->github_provider->getResourceOwner($token);

            // Stocker l'utilisateur dans la session
            $session->set('user', $user->toArray());

            return $user->toArray();
        } catch (IdentityProviderException $e) {
            // Gérer les exceptions ici...
            throw $e;
        }
    }

    public function getUser(SessionInterface $session): ?array
    {
        return $session->get('user');
    }

    public function isAuthenticated(SessionInterface $session): bool
    {
        return $session->has('user');
    }

    public function logout(SessionInterface $session): void
    {
        $session->remove('user');
    }
}
