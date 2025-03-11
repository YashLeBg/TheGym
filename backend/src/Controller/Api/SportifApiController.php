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

class SportifApiController extends AbstractController
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    #[Route('/api/sportifs', methods: ['POST'])]
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
}
