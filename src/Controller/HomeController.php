<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\GithubService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private AuthService $authService;
    private GithubService $githubService;

    public function __construct(AuthService $authService, GithubService $githubService)
    {
        $this->authService = $authService;
        $this->githubService = $githubService;
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        if ($this->authService->isAuthenticated($request->getSession())) {
            $user = $this->authService->getUser($request->getSession());
        } else {
            $user = null;
        }

        $loginUrl = $this->authService->getGithubLoginUrl();

        return $this->render('home/index.html.twig', [
            'user' => $user,
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
            throw new \Exception('No code provided');
        }

        $user = $this->authService->handleGithubCallback($code, $request->getSession());

        return $this->render('show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/github-repos', name: 'github_repos')]
    public function githubRepo(Request $request): Response
    {
        $user = $this->authService->getUser($request->getSession());
        $this->githubService->authenticate($user['access_token']);

        $repositories = $this->githubService->getAllRepositories();

        return $this->render('repo.html.twig', [
            'allRepo' => $repositories,
        ]);
    }


    #[Route('/github-user-repos/{username}', name: 'github_user_repos')]
    public function userRepositories($username): Response
    {
        $this->githubService->authenticate('your-access-token');

        $repositories = $this->githubService->getUserRepositories($username);

        return $this->render('github/user_repositories.html.twig', ['repositories' => $repositories]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $this->authService->logout($request->getSession());

        // Vous pouvez rediriger l'utilisateur vers la page d'accueil ou vers une autre page après la déconnexion
        return $this->redirectToRoute('app_home');
    }

}
