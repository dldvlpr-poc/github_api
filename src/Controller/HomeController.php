<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\GithubService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    /**
     * @throws IdentityProviderException
     */
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
    public function githubRepo(Request $request, GithubService $githubService, HttpClientInterface $httpClient): Response
    {
        $session = $request->getSession();
        $repos = $githubService->fetchGitHubInformation($session, $httpClient);


        //Retournez une réponse appropriée
        return $this->render('repo.html.twig', [
            'allRepo' => $repos,
        ]);
    }


    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $this->authService->logout($request->getSession());

        // Vous pouvez rediriger l'utilisateur vers la page d'accueil ou vers une autre page après la déconnexion
        return $this->redirectToRoute('app_home');
    }

}
