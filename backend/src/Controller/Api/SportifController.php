<?php

namespace App\Controller\Api;

use App\Entity\Sportif;
use App\Repository\SportifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SportifController extends AbstractController
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    #[Route('/api/sportifs', methods: ['GET'])]
    public function getSportifs(SportifRepository $repo): JsonResponse
    {
        $sportifs = $repo->findAll();

        return $this->json($sportifs, 200, [], ['groups' => 'sportif:read']);
    }

    #[Route('/api/sportifs/{id}', methods: ['GET'])]
    public function getSportif(Sportif $sportif): JsonResponse
    {
        return $this->json($sportif, 200, [], ['groups' => 'sportif:read']);
    }

    #[Route('/api/sportifs', methods: ['POST'])]
    public function createSportif(Request $request, EntityManagerInterface $manager, SportifRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

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

    #[Route('/api/sportifs/{id}', methods: ['POST'])]
    public function updateSportif(Sportif $sportif, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $sportif->setNom($data['nom'])
            ->setPrenom($data['prenom'])
            ->setEmail($data['email'])
            ->setNiveauSportif($data['niveau_sportif']);

        $manager->flush();

        return $this->json($sportif, JsonResponse::HTTP_OK, [], ['groups' => 'sportif:read']);
    }

    #[Route('/api/sportifs/{id}', methods: ['DELETE'])]
    public function deleteSportif(Sportif $sportif, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($sportif);
        $manager->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
