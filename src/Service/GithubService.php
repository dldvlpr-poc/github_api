<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubService
{
    private httpClientInterface $httpClient;

    public function __construct(httpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Méthode pour récupérer les informations GitHub de l'utilisateur

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchGitHubInformation(SessionInterface $session): array
    {
        // Récupère le token d'accès de l'utilisateur stocké dans la session
        $token = $session->get('user')['access_token'];

        // Définit les en-têtes de la requête
        $headers = [
            'Authorization' => 'Bearer ' . $token, // Utilise le token d'accès pour l'autorisation
            'Accept' => 'application/vnd.github.v3+json', // Spécifie le format de la réponse
            'X-GitHub-Api-Version' => '2022-11-28' // Spécifie la version de l'API GitHub à utiliser
        ];

        // URL de l'API GitHub pour récupérer les dépôts de l'utilisateur
        $url = 'https://api.github.com/user/repos';

        // Fait une requête GET à l'API GitHub
        $response = $this->httpClient->request('GET', $url, [
            'headers' => $headers
        ]);

        // Récupère le code de statut de la réponse
        $statusCode = $response->getStatusCode();

        // Si la requête a réussi
        if ($statusCode === 200) {
            // Renvoie les dépôts de l'utilisateur sous forme de tableau
            return $response->toArray();
        } else {
            // Si la requête a échoué, lance une exception
            throw new \Exception('Error: ' . $statusCode);
        }
    }
}
