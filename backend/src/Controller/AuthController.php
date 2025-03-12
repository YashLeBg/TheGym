<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AuthController extends AbstractController
{
    public function __construct(
        private Security $security
    ) {}

    #[Route('/api/user/me', name: 'api_user_me', methods: ['GET'])]
    public function me(UtilisateurRepository $repo)
    {
        $user = $this->security->getUser();
        $me = $repo->findOneBy(['email' => $user->getUserIdentifier()]);
        if (!empty($me) && !empty($user)) {
            return new JsonResponse([
                'id' => $me->getId(),
                'nom' => $me->getNom(),
                'prenom' => $me->getPrenom(),
                'email' => $me->getUserIdentifier(),
                'roles' => $me->getRoles()
            ]);
        } else {
            return new JsonResponse(null);
        }
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): Response
    {
        return new Response('LOGGED');
    }
}
