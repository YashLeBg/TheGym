<?php

namespace App\Controller\Api;

use App\Entity\Coach;
use App\Repository\CoachRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_coach_')]
class CoachController extends AbstractController
{
    #[Route('/coachs', methods: ['GET'])]
    public function getCoachs(CoachRepository $repo): JsonResponse
    {
        $coachs = $repo->findAll();

        return $this->json($coachs, 200, [], ['groups' => 'coach:read']);
    }

    #[Route('/coachs/{id}', methods: ['GET'])]
    public function getCoach(Coach $coach): JsonResponse
    {
        return $this->json($coach, 200, [], ['groups' => 'coach:read']);
    }
}
