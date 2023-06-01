<?php

namespace App\Service;

// Import des classes nécessaires pour la connexion OAuth avec GitHub et pour gérer les exceptions.
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthService
{
    private Github $github_provider;

    // Constructeur de la classe AuthService
    public function __construct()
    {
        // Initialise l'instance de la classe Github avec les informations de l'application GitHub
        $this->github_provider = new Github([
            'clientId' => $_ENV['GITHUB_ID'], // ID de l'application GitHub
            'clientSecret' => $_ENV['GITHUB_SECRET'], // Secret de l'application GitHub
            'redirectUri' => $_ENV['GITHUB_CALLBACK'], // URL de redirection après authentification GitHub
        ]);
    }

    // Méthode pour obtenir l'URL de connexion à GitHub
    public function getGithubLoginUrl(): string
    {
        // Les options pour la demande d'authentification
        $options = [
            'scope' => ['user', 'repo'], // Les permissions demandées à l'utilisateur lors de la connexion
        ];

        // Renvoie l'URL de connexion à GitHub
        return $this->github_provider->getAuthorizationUrl($options);
    }

    // Méthode pour gérer le retour de l'authentification GitHub
    public function handleGithubCallback(string $code, SessionInterface $session): array
    {
        try {
            // Obtient le token d'accès à partir du code d'authentification
            $token = $this->github_provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            // Obtient les informations de l'utilisateur GitHub avec le token d'accès
            $user = $this->github_provider->getResourceOwner($token);

            // Convertit les informations de l'utilisateur en tableau
            $userArray = $user->toArray();
            // Ajoute le token d'accès aux informations de l'utilisateur
            $userArray['access_token'] = $token->getToken();

            // Stocke les informations de l'utilisateur dans la session
            $session->set('user', $userArray);

            // Renvoie les informations de l'utilisateur
            return $userArray;
        } catch (IdentityProviderException $e) {
            // Gestion des exceptions en cas d'échec de l'authentification
            throw $e;
        }
    }

    // Méthode pour obtenir les informations de l'utilisateur de la session
    public function getUser(SessionInterface $session): ?array
    {
        return $session->get('user');
    }

    // Méthode pour vérifier si l'utilisateur est authentifié
    public function isAuthenticated(SessionInterface $session): bool
    {
        return $session->has('user');
    }

    // Méthode pour déconnecter l'utilisateur
    public function logout(SessionInterface $session): void
    {
        $session->remove('user');
    }
}
