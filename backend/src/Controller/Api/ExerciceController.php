<?php

namespace App\Controller\Api;

use App\Entity\Exercice;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ExerciceController extends AbstractController
{
    #[Route('/api/exercices', methods: ['GET'])]
    public function getExercices(ExerciceRepository $repo): JsonResponse
    {
        $exercices = $repo->findAll();

        return $this->json($exercices, 200, [], ['groups' => 'exercice:read']);
    }

    #[Route('/api/exercices/{id}', methods: ['GET'])]
    public function getExercice(Exercice $exercice): JsonResponse
    {
        return $this->json($exercice, 200, [], ['groups' => 'exercice:read']);
    }

    #[Route('/api/exercices', methods: ['POST'])]
    public function createExercice(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $exercice = new Exercice();
        $exercice->setNom($data['nom'])
            ->setDescription($data['description'])
            ->setDureeEstimee($data['duree_estimee'])
            ->setDifficulte($data['difficulte']);

        $manager->persist($exercice);
        $manager->flush();

        return $this->json($exercice, JsonResponse::HTTP_CREATED, [], ['groups' => 'exercice:read']);
    }

    #[Route('/api/exercices/{id}', methods: ['POST'])]
    public function updateExercice(Exercice $exercice, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $exercice->setNom($data['nom'])
            ->setDescription($data['description'])
            ->setDureeEstimee($data['duree_estimee'])
            ->setDifficulte($data['difficulte']);

        $manager->flush();

        return $this->json($exercice, JsonResponse::HTTP_OK, [], ['groups' => 'exercice:read']);
    }

    #[Route('/api/exercice/{id}', methods: ['DELETE'])]
    public function deleteExercice(Exercice $exercice, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($exercice);
        $manager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
