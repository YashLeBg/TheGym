<?php

namespace App\Controller\Api;

use App\Entity\Sportif;
use App\Repository\SportifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class SportifApiController extends AbstractController
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $passwordHasher, private Security $security)
    {
        $this->encoder = $passwordHasher;
    }

    #[Route('/sportifs', methods: ['POST'])]
    public function createSportif(Request $request, EntityManagerInterface $manager, SportifRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['password']) || empty($data['niveau_sportif'])) {
            return $this->json(['error' => 'Missing required data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (in_array($data['niveau_sportif'], ['debutant', 'intermediaire', 'avance']) === false) {
            return $this->json(['error' => 'Invalid niveau_sportif'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($repo->findOneBy(['email' => $data['email']])) {
            return $this->json(['error' => 'Email already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $sportif = new Sportif();
        $sportif->setNom($data['nom'])
            ->setPrenom($data['prenom'])
            ->setEmail($data['email'])
            ->setPassword($this->encoder->hashPassword($sportif, $data['password']))
            ->setRoles(['ROLE_SPORTIF'])
            ->setDateIncription(new \DateTime())
            ->setNiveauSportif($data['niveau_sportif']);

        $manager->persist($sportif);
        $manager->flush();

        return $this->json($sportif, JsonResponse::HTTP_CREATED, [], ['groups' => 'sportif:read']);
    }

    #[Route('/sportifs/{id}/bilan', methods: ['GET'])]
    #[IsGranted('ROLE_SPORTIF')]
    public function getBilan(SportifRepository $repo, int $id): JsonResponse
    {
        $sportif = $repo->find($id);

        if (!$sportif) {
            return $this->json(['error' => 'Sportif not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser()->getUserIdentifier();
        if ($user !== $sportif->getEmail()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $seances = $sportif->getSeances();

        $top_3_exercices = [];
        foreach ($seances as $seance) {
            foreach ($seance->getExercices() as $exercice) {
                if (array_key_exists($exercice->getId(), $top_3_exercices)) {
                    $top_3_exercices[$exercice->getId()] += 1;
                } else {
                    $top_3_exercices[$exercice->getId()] = 1;
                }
            }
        }
        arsort($top_3_exercices);

        $duree_totale = 0;
        foreach ($seances as $seance) {
            foreach ($seance->getExercices() as $exercice) {
                $duree_totale += $exercice->getDureeEstimee();
            }
        }

        $bilan = [
            'seances' => $seances,
            'total_seances_mois' => count(array_filter($seances->toArray(), fn($seance) => $seance->getDateHeure()->format('m') === (new \DateTime())->format('m'))),
            'total_seances' => count($seances),
            'theme_seances' => [
                'bodybuilding' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'bodybuilding')),
                'crossfit' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'crossfit')),
                'powerlifting' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'powerlifting')),
                'streetlifting' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'streetlifting')),
                'yoga' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'yoga')),
                'cardio' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'cardio')),
                'calisthenics' => count(array_filter($seances->toArray(), fn($seance) => $seance->getThemeSeance() === 'calisthenics')),
            ],
            'top_3_exercices' => array_slice($top_3_exercices, 0, 3, true),
            'duree_totale' => $duree_totale
        ];

        return $this->json($bilan, JsonResponse::HTTP_OK, [], ['groups' => 'seance:read']);
    }
}
