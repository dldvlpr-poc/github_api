<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $user = $this->authService->getUser($request->getSession());

        $loginUrl = $this->authService->getGithubLoginUrl();

        return $this->render('home/index.html.twig', [
            'login' => $user ? $user['login'] : null,
            'picture' => $user ? $user['avatar_url'] : null,
            'loginUrl' => $loginUrl
        ]);
    }

    #[Route('/github-login', name: 'github_login')]
    public function githubLogin(): Response
    {
        return $this->redirect($this->authService->getGithubLoginUrl());
    }

    #[Route('/github-callback', name: 'github_callback')]
    public function githubCallback(Request $request): Response
    {
        $code = $request->query->get('code');

        if (!$code) {
// Gérer l'absence de code ici...
            throw new \Exception('No code provided');
        }

        $user = $this->authService->handleGithubCallback($code, $request->getSession());

        return $this->render('show.html.twig', [
            'login' => $user['login'],
            'picture' => $user['avatar_url'],
        ]);
    }
}
