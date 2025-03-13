<?php

namespace App\DataFixtures;

use App\Entity\Coach;
use App\Entity\Exercice;
use App\Entity\FicheDePaie;
use App\Entity\Seance;
use App\Entity\Sportif;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TheGymExtendedFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $encoder;
    private array $themeSeances = ['bodybuilding', 'crossfit', 'powerlifting', 'streetlifting', 'yoga', 'cardio', 'calisthenics'];
    private array $niveaux = ['debutant', 'intermediaire', 'avance'];
    private array $typeSeances = ['collective', 'individuelle'];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->encoder = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Récupérer les données existantes
        $coachs = $manager->getRepository('App\Entity\Coach')->findAll();
        $sportifs = $manager->getRepository('App\Entity\Sportif')->findAll();
        $exercices = $manager->getRepository('App\Entity\Exercice')->findAll();

        // Générer 150 séances réparties sur les 3 prochains mois
        $seances = $this->generateSeances($coachs, $sportifs, $exercices);
        foreach ($seances as $seance) {
            $manager->persist($seance);
        }

        $manager->flush();
    }

    private function generateSeances(array $coachs, array $sportifs, array $exercices): array
    {
        $seances = [];

        // Uniquement pour mars et avril
        $startDate = new \DateTime('2025-03-01');
        $endDate = new \DateTime('2025-04-30');

        // Heures possibles (8h-12h et 14h-17h)
        $heuresPossibles = [8, 9, 10, 11, 14, 15, 16];

        // Parcourir chaque jour entre startDate et endDate
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            // Vérifier si c'est un dimanche (jour 0 de la semaine)
            if ($currentDate->format('w') !== '0') {
                // Pour chaque heure possible
                foreach ($heuresPossibles as $heure) {
                    // 2 chances sur 3 d'avoir un cours (66.67%)
                    if (mt_rand(1, 3) <= 2) {
                        // Créer une séance à cette heure
                        $seance = new Seance();

                        // Formater la date au format Y-m-d H:00:00
                        $formattedDate = $currentDate->format('Y-m-d') . ' ' . sprintf('%02d:00:00', $heure);

                        // Sélectionner un coach aléatoire
                        $coach = $coachs[array_rand($coachs)];

                        // Configurer la séance
                        $seance->setDateHeure(new \DateTime($formattedDate))
                            ->setTypeSeance($this->typeSeances[array_rand($this->typeSeances)])
                            ->setThemeSeance($this->themeSeances[array_rand($this->themeSeances)])
                            ->setNiveauSeance($this->niveaux[array_rand($this->niveaux)])
                            ->setStatut('terminee')
                            ->setCoach($coach);

                        // Ajouter 1 à 3 sportifs aléatoirement
                        $numSportifs = mt_rand(1, 3);
                        $shuffledSportifs = $sportifs;
                        shuffle($shuffledSportifs);
                        for ($j = 0; $j < $numSportifs && $j < count($shuffledSportifs); $j++) {
                            $seance->addSportif($shuffledSportifs[$j]);
                        }

                        // Ajouter 3 à 5 exercices aléatoirement
                        $numExercices = mt_rand(3, 5);
                        $shuffledExercices = $exercices;
                        shuffle($shuffledExercices);
                        for ($j = 0; $j < $numExercices && $j < count($shuffledExercices); $j++) {
                            $seance->addExercice($shuffledExercices[$j]);
                        }

                        $seances[] = $seance;

                        // 1 chance sur 3 d'avoir un deuxième cours avec un autre coach à la même heure
                        if (mt_rand(1, 3) === 1 && count($coachs) > 1) {
                            // Créer une deuxième séance à la même heure
                            $seance2 = new Seance();

                            // Sélectionner un coach différent
                            $autresCoachs = array_filter($coachs, function ($c) use ($coach) {
                                return $c !== $coach;
                            });

                            if (!empty($autresCoachs)) {
                                $coach2 = $autresCoachs[array_rand($autresCoachs)];

                                // Configurer la séance
                                $seance2->setDateHeure(new \DateTime($formattedDate))
                                    ->setTypeSeance($this->typeSeances[array_rand($this->typeSeances)])
                                    ->setThemeSeance($this->themeSeances[array_rand($this->themeSeances)])
                                    ->setNiveauSeance($this->niveaux[array_rand($this->niveaux)])
                                    ->setStatut('terminee')
                                    ->setCoach($coach2);

                                // Ajouter 1 à 3 sportifs aléatoirement
                                $numSportifs = mt_rand(1, 3);
                                $shuffledSportifs = $sportifs;
                                shuffle($shuffledSportifs);
                                for ($j = 0; $j < $numSportifs && $j < count($shuffledSportifs); $j++) {
                                    $seance2->addSportif($shuffledSportifs[$j]);
                                }

                                // Ajouter 3 à 5 exercices aléatoirement
                                $numExercices = mt_rand(3, 5);
                                $shuffledExercices = $exercices;
                                shuffle($shuffledExercices);
                                for ($j = 0; $j < $numExercices && $j < count($shuffledExercices); $j++) {
                                    $seance2->addExercice($shuffledExercices[$j]);
                                }

                                $seances[] = $seance2;
                            }
                        }
                    }
                }
            }

            // Passer au jour suivant
            $currentDate->modify('+1 day');
        }
        return $seances;
    }

    public function getDependencies(): array
    {
        return [
            TheGymFixtures::class,
        ];
    }
}
