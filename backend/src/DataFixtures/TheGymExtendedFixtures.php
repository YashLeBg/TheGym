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

    // Réutiliser les méthodes existantes de TheGymFixtures
    private function getResponsable(): Utilisateur
    {
        $responsable = new Utilisateur();

        $responsable
            ->setNom("BUMSTEAD")
            ->setPrenom("Christopher")
            ->setEmail("christopher.bumstead@the-gym.fr")
            ->setPassword($this->encoder->hashPassword($responsable, "password-responsable"))
            ->setRoles(["ROLE_RESPONSABLE"]);

        return $responsable;
    }

    private function getSportifs(): array
    {
        $sportifs = [];

        $sportif_1 = new Sportif();
        $sportif_1
            ->setNom("MAURIAC")
            ->setPrenom("Jimmy")
            ->setEmail("jimmy.mauriac@gmail.com")
            ->setPassword($this->encoder->hashPassword($sportif_1, "password-sportif"))
            ->setRoles(["ROLE_SPORTIF"])
            ->setDateIncription(new \DateTime("2025-02-14"))
            ->setNiveauSportif("debutant");
        $sportifs[] = $sportif_1;

        $sportif_2 = new Sportif();
        $sportif_2
            ->setNom("MOHAMED")
            ->setPrenom("Anli-Yachourti")
            ->setEmail("anli-yachourti.mohamed@gmail.com")
            ->setPassword($this->encoder->hashPassword($sportif_2, "password-sportif"))
            ->setRoles(["ROLE_SPORTIF"])
            ->setDateIncription(new \DateTime("2025-02-14"))
            ->setNiveauSportif("intermediaire");
        $sportifs[] = $sportif_2;

        $sportif_3 = new Sportif();
        $sportif_3
            ->setNom("RECORD")
            ->setPrenom("Bastien")
            ->setEmail("bastien.record@gmail.com")
            ->setPassword($this->encoder->hashPassword($sportif_3, "password-sportif"))
            ->setRoles(["ROLE_SPORTIF"])
            ->setDateIncription(new \DateTime("2025-02-14"))
            ->setNiveauSportif("avance");
        $sportifs[] = $sportif_3;

        return $sportifs;
    }

    private function getCoachs(): array
    {
        $coachs = [];

        $coach_1 = new Coach();
        $coach_1
            ->setNom("PLATZ")
            ->setPrenom("Tom")
            ->setEmail("tom.platz@the-gym.fr")
            ->setPassword($this->encoder->hashPassword($coach_1, "password-coach"))
            ->setRoles(["ROLE_COACH"])
            ->setSpecialites(["bodybuilding"])
            ->setTarifHoraire(50.00);
        $coachs[] = $coach_1;

        $coach_2 = new Coach();
        $coach_2
            ->setNom("NFC")
            ->setPrenom("Essan")
            ->setEmail("essan.nfc@the-gym.fr")
            ->setPassword($this->encoder->hashPassword($coach_2, "password-coach"))
            ->setRoles(["ROLE_COACH"])
            ->setSpecialites(["bodybuilding", "crossfit"])
            ->setTarifHoraire(80.00);
        $coachs[] = $coach_2;

        $coach_3 = new Coach();
        $coach_3
            ->setNom("TARIDINIS")
            ->setPrenom("Panagiotis")
            ->setEmail("taridinis.panagiotis@the-gym.fr")
            ->setPassword($this->encoder->hashPassword($coach_3, "password-coach"))
            ->setRoles(["ROLE_COACH"])
            ->setSpecialites(["powerlifting", "streetlifting"])
            ->setTarifHoraire(65.00);
        $coachs[] = $coach_3;

        return $coachs;
    }

    private function getExercices(): array
    {
        $exercices = [];

        $exercice_1 = new Exercice();
        $exercice_1
            ->setNom("Développé Couché (barre)")
            ->setDescription("Allongé sur un banc, les pieds au sol, les mains écartées à la largeur des épaules, descendez la barre jusqu'à la poitrine puis remontez.")
            ->setDureeEstimee(20)
            ->setDifficulte("moyen");
        $exercices[] = $exercice_1;

        $exercice_2 = new Exercice();
        $exercice_2
            ->setNom("Squat (barre)")
            ->setDescription("Debout, les pieds écartés à la largeur des épaules, descendez en fléchissant les genoux jusqu'à former un angle droit puis remontez.")
            ->setDureeEstimee(25)
            ->setDifficulte("moyen");
        $exercices[] = $exercice_2;

        $exercice_3 = new Exercice();
        $exercice_3
            ->setNom("Traction")
            ->setDescription("Suspendu à une barre, les mains écartées à la largeur des épaules, remontez le corps jusqu'à ce que le menton passe au-dessus de la barre.")
            ->setDureeEstimee(15)
            ->setDifficulte("difficile");
        $exercices[] = $exercice_3;

        $exercice_4 = new Exercice();
        $exercice_4
            ->setNom("Développé Militaire (barre)")
            ->setDescription("Debout, les pieds écartés à la largeur des épaules, les mains écartées à la largeur des épaules, poussez la barre au-dessus de la tête.")
            ->setDureeEstimee(15)
            ->setDifficulte("moyen");
        $exercices[] = $exercice_4;

        $exercice_5 = new Exercice();
        $exercice_5
            ->setNom("Soulevé de Terre (barre)")
            ->setDescription("Debout, les pieds écartés à la largeur des épaules, penchez-vous en avant pour saisir la barre, puis redressez-vous en soulevant la barre jusqu'à la hanche.")
            ->setDureeEstimee(30)
            ->setDifficulte("difficile");
        $exercices[] = $exercice_5;

        $exercice_6 = new Exercice();
        $exercice_6
            ->setNom("Rowing")
            ->setDescription("Debout, les pieds écartés à la largeur des épaules, penchez-vous en avant pour saisir la barre, puis redressez-vous en ramenant la barre vers le ventre.")
            ->setDureeEstimee(20)
            ->setDifficulte("moyen");
        $exercices[] = $exercice_6;

        $exercice_7 = new Exercice();
        $exercice_7
            ->setNom("Leg Curl")
            ->setDescription("Assis sur la machine, les jambes tendues, fléchissez les jambes pour ramener les talons vers les fesses.")
            ->setDureeEstimee(15)
            ->setDifficulte("facile");
        $exercices[] = $exercice_7;

        $exercice_8 = new Exercice();
        $exercice_8
            ->setNom("Leg Extension")
            ->setDescription("Assis sur la machine, les jambes fléchies, tendez les jambes pour lever les poids.")
            ->setDureeEstimee(15)
            ->setDifficulte("facile");
        $exercices[] = $exercice_8;

        $exercice_9 = new Exercice();
        $exercice_9
            ->setNom("Leg Press")
            ->setDescription("Assis sur la machine, les pieds écartés à la largeur des épaules, poussez les poids avec les jambes.")
            ->setDureeEstimee(20)
            ->setDifficulte("moyen");
        $exercices[] = $exercice_9;

        $exercice_10 = new Exercice();
        $exercice_10
            ->setNom("Crunch")
            ->setDescription("Allongé sur le dos, les jambes fléchies, relevez le buste pour rapprocher les coudes des genoux.")
            ->setDureeEstimee(15)
            ->setDifficulte("facile");
        $exercices[] = $exercice_10;

        return $exercices;
    }

    private function getFichesDePaie(array $coachs): array
    {
        $fiches = [];
        $startDate = new \DateTime('first day of next month');

        foreach ($coachs as $coach) {
            for ($i = 0; $i < 3; $i++) {
                $date = clone $startDate;
                $date->modify("+$i months");

                $fiche = new FicheDePaie();
                $fiche->setCoach($coach)
                    ->setPeriode($date)
                    ->setTotalHeures(mt_rand(20, 100))
                    ->setMontantTotal(mt_rand(1000, 5000));

                $fiches[] = $fiche;
            }
        }

        return $fiches;
    }
}
