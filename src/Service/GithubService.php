<?php

namespace App\Service;

use Github\Client;

class GithubService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function authenticate(string $token)
    {
// Authenticate with an access token
        $this->client->authenticate($token, null, Client::AUTH_ACCESS_TOKEN);
    }

    public function getAllRepositories(): array
    {
// Get all repositories
        $repositories = $this->client->api('repo')->all();
        return $repositories;
    }

    public function getUserRepositories(string $username): array
    {
// Get repositories of a specific user
        $repositories = $this->client->api('user')->repositories($username);
        return $repositories;
    }
}
