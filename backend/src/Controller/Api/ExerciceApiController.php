<?php

namespace App\Controller\Api;

use App\Entity\Exercice;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ExerciceApiController extends AbstractController
{
    #[Route('/exercices', methods: ['GET'])]
    #[IsGranted('ROLE_SPORTIF')]
    public function getExercices(ExerciceRepository $repo): JsonResponse
    {
        $exercices = $repo->findAll();

        return $this->json($exercices, 200, [], ['groups' => 'exercice:read']);
    }

    #[Route('/exercices/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_SPORTIF')]
    public function getExercice(ExerciceRepository $repo, int $id): JsonResponse
    {
        $exercice = $repo->find($id);

        if (!$exercice) {
            return $this->json(['error' => 'Exercice not found'], 404);
        }

        return $this->json($exercice, 200, [], ['groups' => 'exercice:read']);
    }
}
