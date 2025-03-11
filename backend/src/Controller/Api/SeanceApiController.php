<?php

namespace App\Controller\Api;

use App\Repository\SeanceRepository;
use App\Repository\SportifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class SeanceApiController extends AbstractController
{
    public function __construct(
        private Security $security
    ) {}

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

    #[Route('/seances/{id}/register', methods: ['GET'])]
    #[IsGranted('ROLE_SPORTIF')]
    public function registerUserToSeance(Request $request, EntityManagerInterface $manager, SportifRepository $repoSportif, SeanceRepository $repoSeance, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $seance = $repoSeance->find($id);

        if (!$seance) {
            return $this->json(['error' => 'Seance not found'], 404);
        }

        if (empty($data['id'])) {
            return $this->json(['error' => 'Missing required data'], 400);
        }

        $sportif = $repoSportif->find($data['id']);
        if (!$sportif) {
            return $this->json(['error' => 'Sportif not found'], 404);
        }

        $user = $this->security->getUser()->getUserIdentifier();
        if ($user !== $sportif->getEmail()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($seance->getSportifs()->contains($sportif)) {
            return $this->json(['error' => 'User already registered to seance'], 400);
        }

        if ($seance->getSportifs()->count() > 3) {
            return $this->json(['error' => 'Seance is full'], 400);
        }

        if ($sportif->getNiveauSportif() !== $seance->getNiveauSeance()) {
            return $this->json(['error' => 'Sportif niveau does not match seance niveau'], 400);
        }

        if ($sportif->getSeances()->count() > 3) {
            return $this->json(['error' => 'Sportif already registered to 3 seances'], 400);
        }

        $seance->addSportif($sportif);
        $manager->flush();

        return $this->json($seance, JsonResponse::HTTP_OK, [], ['groups' => 'seance:read']);
    }
}
