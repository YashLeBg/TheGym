<?php

namespace App\Controller\Api;

use App\Entity\Coach;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CoachController extends AbstractController
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    #[Route('/api/coachs', methods: ['GET'])]
    public function getCoachs(CoachRepository $repo): JsonResponse
    {
        $coachs = $repo->findAll();

        return $this->json($coachs, 200, [], ['groups' => 'coach:read']);
    }

    #[Route('/api/coachs/{id}', methods: ['GET'])]
    public function getCoach(Coach $coach): JsonResponse
    {
        return $this->json($coach, 200, [], ['groups' => 'coach:read']);
    }

    #[Route('/api/coachs', methods: ['POST'])]
    public function createCoach(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $coach = new Coach();
        $coach->setNom($data['nom'])
            ->setPrenom($data['prenom'])
            ->setEmail($data['email'])
            ->setPassword($this->encoder->hashPassword($coach, $data['password']))
            ->setRoles(['ROLE_COACH'])
            ->setSpecialites($data['specialites'])
            ->setTarifHoraire($data['tarif_horaire']);

        $manager->persist($coach);
        $manager->flush();

        return $this->json($coach, JsonResponse::HTTP_CREATED, [], ['groups' => 'coach:read']);
    }

    #[Route('/api/coachs/{id}', methods: ['POST'])]
    public function updateCoach(Coach $coach, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $coach->setNom($data['nom'])
            ->setPrenom($data['prenom'])
            ->setEmail($data['email'])
            ->setSpecialites($data['specialites'])
            ->setTarifHoraire($data['tarif_horaire']);

        $manager->flush();

        return $this->json($coach, JsonResponse::HTTP_OK, [], ['groups' => 'coach:read']);
    }

    #[Route('/api/coachs/{id}', methods: ['DELETE'])]
    public function deleteCoach(Coach $coach, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($coach);
        $manager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
