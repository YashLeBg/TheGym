<?php

namespace App\DataFixtures;

use App\Entity\Coach;
use App\Entity\Exercice;
use App\Entity\FicheDePaie;
use App\Entity\Seance;
use App\Entity\Sportif;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TheGymFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $responsable = $this->getResponsable();
        $manager->persist($responsable);

        $coachs = $this->getCoachs();
        foreach ($coachs as $coach) {
            $manager->persist($coach);
        }

        $sportifs = $this->getSportifs();
        foreach ($sportifs as $sportif) {
            $manager->persist($sportif);
        }

        $exercices = $this->getExercices();
        foreach ($exercices as $exercice) {
            $manager->persist($exercice);
        }

        $seances = $this->getSeances();
        $seances[0]
            ->addExercice($exercices[4])
            ->addExercice($exercices[5])
            ->addExercice($exercices[6])
            ->setCoach($coachs[2])
            ->addSportif($sportifs[0]);
        $seances[1]
            ->addExercice($exercices[5])
            ->addExercice($exercices[6])
            ->addExercice($exercices[7])
            ->setCoach($coachs[1])
            ->addSportif($sportifs[1]);
        $seances[2]
            ->addExercice($exercices[0])
            ->addExercice($exercices[1])
            ->addExercice($exercices[2])
            ->setCoach($coachs[0])
            ->addSportif($sportifs[2]);
        foreach ($seances as $seance) {
            $manager->persist($seance);
        }

        $fiches = $this->getFichesDePaie($coachs);
        foreach ($fiches as $fiche) {
            $manager->persist($fiche);
        }

        $manager->flush();
    }

    private function getResponsable(): Utilisateur
    {
        $responsable = new Utilisateur();

        $responsable
            ->setNom("BUMSTEAD")
            ->setPrenom("Christopher")
            ->setEmail("christopher.bumstead@the-gym.fr")
            ->setPassword("password-responsable")
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
            ->setPassword("password-sportif")
            ->setRoles(["ROLE_SPORTIF"])
            ->setDateIncription(new \DateTime("2025-02-14"))
            ->setNiveauSportif("debutant");
        $sportifs[] = $sportif_1;

        $sportif_2 = new Sportif();
        $sportif_2
            ->setNom("MOHAMED")
            ->setPrenom("Anli-Yachourti")
            ->setEmail("anli-yachourti.mohamed@gmail.com")
            ->setPassword("password-sportif")
            ->setRoles(["ROLE_SPORTIF"])
            ->setDateIncription(new \DateTime("2025-02-14"))
            ->setNiveauSportif("intermediaire");
        $sportifs[] = $sportif_2;

        $sportif_3 = new Sportif();
        $sportif_3
            ->setNom("RECORD")
            ->setPrenom("Bastien")
            ->setEmail("bastien.record@gmail.com")
            ->setPassword("password-sportif")
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
            ->setPassword("password-coach")
            ->setRoles(["ROLE_COACH"])
            ->setSpecialites(["bodybuilding"])
            ->setTarifHoraire(50.00);
        $coachs[] = $coach_1;

        $coach_2 = new Coach();
        $coach_2
            ->setNom("NFC")
            ->setPrenom("Essan")
            ->setEmail("essan.nfc@the-gym.fr")
            ->setPassword("password-coach")
            ->setRoles(["ROLE_COACH"])
            ->setSpecialites(["bodybuilding", "crossfit"])
            ->setTarifHoraire(80.00);
        $coachs[] = $coach_2;

        $coach_3 = new Coach();
        $coach_3
            ->setNom("TARIDINIS")
            ->setPrenom("Panagiotis")
            ->setEmail("taridinis.panagiotis@the-gym.fr")
            ->setPassword("password-coach")
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

    private function getSeances(): array
    {
        $seances = [];

        $seance_1 = new Seance();
        $seance_1
            ->setDateHeure(new \DateTime("2025-03-10 10:00:00"))
            ->setTypeSeance("individuelle")
            ->setThemeSeance("powerlifting")
            ->setStatut("prevue")
            ->setNiveauSeance("debutant");
        $seances[] = $seance_1;

        $seance_2 = new Seance();
        $seance_2
            ->setDateHeure(new \DateTime("2025-03-10 14:25:00"))
            ->setTypeSeance("individuelle")
            ->setThemeSeance("crossfit")
            ->setStatut("prevue")
            ->setNiveauSeance("intermediaire");
        $seances[] = $seance_2;

        $seance_3 = new Seance();
        $seance_3
            ->setDateHeure(new \DateTime("2025-03-11 09:30:00"))
            ->setTypeSeance("individuelle")
            ->setThemeSeance("bodybuilding")
            ->setStatut("prevue")
            ->setNiveauSeance("avance");
        $seances[] = $seance_3;

        return $seances;
    }

    private function getFichesDePaie(array $coachs): array
    {
        $fiches = [];

        $fiche_1 = new FicheDePaie();
        $fiche_1
            ->setCoach($coachs[0])
            ->setPeriode(\DateTime::createFromFormat("Y-m", "2025-03"))
            ->setTotalHeures(20)
            ->setMontantTotal(20 * $coachs[0]->getTarifHoraire());
        $fiches[] = $fiche_1;

        $fiche_2 = new FicheDePaie();
        $fiche_2
            ->setCoach($coachs[1])
            ->setPeriode(\DateTime::createFromFormat("Y-m", "2025-03"))
            ->setTotalHeures(25)
            ->setMontantTotal(25 * $coachs[1]->getTarifHoraire());
        $fiches[] = $fiche_2;

        $fiche_3 = new FicheDePaie();
        $fiche_3
            ->setCoach($coachs[2])
            ->setPeriode(\DateTime::createFromFormat("Y-m", "2025-03"))
            ->setTotalHeures(30)
            ->setMontantTotal(30 * $coachs[2]->getTarifHoraire());
        $fiches[] = $fiche_3;

        return $fiches;
    }
}
