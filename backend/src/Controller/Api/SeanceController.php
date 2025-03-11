<?php

namespace App\Controller\Api;

use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\ExerciceRepository;
use App\Repository\SeanceRepository;
use App\Repository\SportifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SeanceController extends AbstractController
{
    #[Route('/api/seances', methods: ['GET'])]
    public function getSeances(SeanceRepository $repo): JsonResponse
    {
        $seances = $repo->findAll();

        return $this->json($seances, 200, [], ['groups' => 'seance:read']);
    }

    #[Route('/api/seances/{id}', methods: ['GET'])]
    public function getSeance(Seance $seance): JsonResponse
    {
        return $this->json($seance, 200, [], ['groups' => 'seance:read']);
    }

    #[Route('/api/seances', methods: ['POST'])]
    public function createSeance(Request $request, EntityManagerInterface $manager, CoachRepository $repoCoach, ExerciceRepository $repoExercice): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $seance = new Seance();
        $seance->setDateHeure($data['date_heure'])
            ->setTypeSeance($data['type_seance'])
            ->setThemeSeance($data['theme_seance'])
            ->setStatut($data['statut'])
            ->setNiveauSeance($data['niveau_seance']);

        $coach = $repoCoach->find($data['coach']);
        $seance->setCoach($coach);

        foreach ($data['exercices'] as $exerciceId) {
            $exercice = $repoExercice->find($exerciceId);
            $seance->addExercice($exercice);
        }

        $manager->persist($seance);
        $manager->flush();

        return $this->json($seance, JsonResponse::HTTP_CREATED, [], ['groups' => 'seance:read']);
    }

    #[Route('/api/seances/{id}', methods: ['POST'])]
    public function updateSeance(Seance $seance, Request $request, EntityManagerInterface $manager, CoachRepository $repoCoach, ExerciceRepository $repoExercice, SportifRepository $repoSportif): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $seance->setDateHeure($data['date_heure'])
            ->setTypeSeance($data['type_seance'])
            ->setThemeSeance($data['theme_seance'])
            ->setStatut($data['statut'])
            ->setNiveauSeance($data['niveau_seance']);

        $coach = $repoCoach->find($data['coach']);
        $seance->setCoach($coach);

        foreach ($seance->getExercices() as $exercice) {
            $seance->removeExercice($exercice);
        }
        foreach ($data['exercices'] as $exerciceId) {
            $exercice = $repoExercice->find($exerciceId);
            $seance->addExercice($exercice);
        }

        foreach ($seance->getSportifs() as $sportif) {
            $seance->removeSportif($sportif);
        }
        foreach ($data['sportifs'] as $sportifId) {
            $sportif = $repoSportif->find($sportifId);
            $seance->addSportif($sportif);
        }

        $manager->flush();

        return $this->json($seance, JsonResponse::HTTP_OK, [], ['groups' => 'seance:read']);
    }

    #[Route('/api/seances/{id}', methods: ['DELETE'])]
    public function deleteSeance(Seance $seance, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($seance);
        $manager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }    

}
