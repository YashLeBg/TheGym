<?php

namespace App\Controller\Api;

use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class SeanceApiController extends AbstractController
{
    #[Route('/seances', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function getSeances(SeanceRepository $repo): JsonResponse
    {
        $seances = $repo->findAll();

        return $this->json($seances, 200, [], ['groups' => 'seance:read']);
    }

    #[Route('/seances/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_SPORTIF')]
    public function getSeance(SeanceRepository $repo, int $id): JsonResponse
    {
        $seance = $repo->find($id);

        if (!$seance) {
            return $this->json(['error' => 'Seance not found'], 404);
        }

        return $this->json($seance, 200, [], ['groups' => 'seance:read']);
    }
}
