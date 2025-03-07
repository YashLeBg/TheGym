<?php

namespace App\Controller\Admin;

use App\Entity\Coach;
use App\Entity\Exercice;
use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\ExerciceRepository;
use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class StatisticsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SeanceRepository $seanceRepository;
    private ExerciceRepository $exerciceRepository;
    private CoachRepository $coachRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SeanceRepository $seanceRepository,
        ExerciceRepository $exerciceRepository,
        CoachRepository $coachRepository
    ) {
        $this->entityManager = $entityManager;
        $this->seanceRepository = $seanceRepository;
        $this->exerciceRepository = $exerciceRepository;
        $this->coachRepository = $coachRepository;
    }

    #[Route('/admin/statistics', name: 'admin_statistics')]
    public function index(Request $request): Response
    {
        // Statistiques de fréquentation (nombre de séances par mois)
        $sessionsByMonth = $this->getSessionsByMonth();
        
        // Taux de remplissage des séances
        $sessionFillRates = $this->getSessionFillRates();
        
        // Popularité des thèmes d'entraînement
        $themePopularity = $this->getThemePopularity();
        
        // Exercices les plus pratiqués
        $topExercises = $this->getTopExercises();
        
        // Statistiques globales des coachs
        $coachSessionCountsByMonth = $this->getCoachSessionCountsByMonth();
        $coachPerformance = $this->getCoachPerformance();
        
        // Déterminer le coach le plus rentable
        $mostProfitableCoach = null;
        $highestProfitability = PHP_INT_MIN;
        
        foreach ($coachPerformance as $coach) {
            if ($coach['profitability'] > $highestProfitability) {
                $highestProfitability = $coach['profitability'];
                $mostProfitableCoach = $coach;
            }
        }

        return $this->render('admin/statistics.html.twig', [
            'sessionsByMonth' => json_encode($sessionsByMonth),
            'sessionFillRates' => json_encode($sessionFillRates),
            'themePopularity' => json_encode($themePopularity),
            'topExercises' => $topExercises,
            'coachSessionCountsByMonth' => json_encode($coachSessionCountsByMonth),
            'coachMonthlyPerformance' => $coachPerformance,
            'mostProfitableCoach' => $mostProfitableCoach
        ]);
    }

    private function getSessionsByMonth(): array
    {
        $sessions = $this->seanceRepository->findAll();
        $sessionsByMonth = [];
        
        foreach ($sessions as $session) {
            $month = $session->getDateHeure()->format('Y-m');
            if (!isset($sessionsByMonth[$month])) {
                $sessionsByMonth[$month] = 0;
            }
            $sessionsByMonth[$month]++;
        }
        
        // Format for Chart.js
        $labels = array_keys($sessionsByMonth);
        $data = array_values($sessionsByMonth);
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getSessionFillRates(): array
    {
        $sessions = $this->seanceRepository->findAll();
        $fillRates = [
            'labels' => ['0%', '1-33%', '34-66%', '67-99%', '100%'],
            'data' => [0, 0, 0, 0, 0]
        ];
        
        foreach ($sessions as $session) {
            $sportifCount = $session->getSportifs()->count();
            // Assuming max 3 sportifs per session as per the entity constraint
            $maxSportifs = 3;
            $fillRate = ($sportifCount / $maxSportifs) * 100;
            
            if ($fillRate == 0) {
                $fillRates['data'][0]++;
            } elseif ($fillRate <= 33) {
                $fillRates['data'][1]++;
            } elseif ($fillRate <= 66) {
                $fillRates['data'][2]++;
            } elseif ($fillRate < 100) {
                $fillRates['data'][3]++;
            } else {
                $fillRates['data'][4]++;
            }
        }
        
        return $fillRates;
    }

    private function getThemePopularity(): array
    {
        $sessions = $this->seanceRepository->findAll();
        $themes = [];
        
        foreach ($sessions as $session) {
            $theme = $session->getThemeSeance();
            if (!isset($themes[$theme])) {
                $themes[$theme] = 0;
            }
            $themes[$theme]++;
        }
        
        // Format for Chart.js
        return [
            'labels' => array_keys($themes),
            'data' => array_values($themes)
        ];
    }

    private function getTopExercises(int $limit = 10): array
    {
        $exercises = $this->exerciceRepository->findAll();
        $exerciseCounts = [];
        
        foreach ($exercises as $exercise) {
            $count = $exercise->getSeances()->count();
            $exerciseCounts[$exercise->getNom()] = $count;
        }
        
        arsort($exerciseCounts);
        
        return array_slice($exerciseCounts, 0, $limit, true);
    }
    
    private function getCoachSessionCountsByMonth(): array
    {
        $coaches = $this->coachRepository->findAll();
        $months = [];
        $datasets = [];
        
        // Récupérer tous les mois disponibles
        $sessions = $this->seanceRepository->findAll();
        foreach ($sessions as $session) {
            $month = $session->getDateHeure()->format('Y-m');
            if (!in_array($month, $months)) {
                $months[] = $month;
            }
        }
        
        // Trier les mois par ordre chronologique
        sort($months);
        
        // Pour chaque coach, compter les séances par mois
        foreach ($coaches as $index => $coach) {
            $data = array_fill(0, count($months), 0);
            
            foreach ($coach->getSeances() as $session) {
                $month = $session->getDateHeure()->format('Y-m');
                $monthIndex = array_search($month, $months);
                if ($monthIndex !== false) {
                    $data[$monthIndex]++;
                }
            }
            
            $datasets[] = [
                'label' => $coach->__toString(),
                'data' => $data,
                'backgroundColor' => $this->getColor($index, 0.7),
                'borderColor' => $this->getColor($index, 1),
                'borderWidth' => 1
            ];
        }
        
        // Format for Chart.js
        return [
            'labels' => $months,
            'datasets' => $datasets
        ];
    }
    
    private function getCoachPerformance(): array
    {
        $coaches = $this->coachRepository->findAll();
        $performance = [];
        
        foreach ($coaches as $coach) {
            $sessions = $coach->getSeances();
            $sessionCount = count($sessions);
            $totalSportifs = 0;
            $totalRevenue = 0;
            $hourlyRate = $coach->getTarifHoraire();
            $cost = $sessionCount * $hourlyRate;
            
            foreach ($sessions as $session) {
                $sportifCount = $session->getSportifs()->count();
                $totalSportifs += $sportifCount;
                $totalRevenue += $hourlyRate * $sportifCount;
            }
            
            if ($sessionCount > 0) {
                $performance[] = [
                    'coach' => $coach->__toString(),
                    'sessions' => $sessionCount,
                    'sportifs' => $totalSportifs,
                    'hourlyRate' => $hourlyRate,
                    'cost' => $cost,
                    'revenue' => $totalRevenue,
                    'profitability' => $totalRevenue - $cost,
                    'specialities' => implode(', ', $coach->getSpecialites())
                ];
            }
        }
        
        // Trier par rentabilité (meilleure rentabilité en premier)
        usort($performance, function($a, $b) {
            return $b['profitability'] <=> $a['profitability'];
        });
        
        return $performance;
    }
    
    private function getColor(int $index, float $alpha): string
    {
        $colors = [
            'rgba(54, 162, 235, %f)',
            'rgba(255, 99, 132, %f)',
            'rgba(75, 192, 192, %f)',
            'rgba(255, 206, 86, %f)',
            'rgba(153, 102, 255, %f)',
            'rgba(255, 159, 64, %f)',
            'rgba(199, 199, 199, %f)'
        ];
        
        $colorIndex = $index % count($colors);
        return sprintf($colors[$colorIndex], $alpha);
    }
} 