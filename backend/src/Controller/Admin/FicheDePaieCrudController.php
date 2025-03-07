<?php

namespace App\Controller\Admin;

use App\Entity\Coach;
use App\Entity\FicheDePaie;
use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;

class FicheDePaieCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private CoachRepository $coachRepository;
    private SeanceRepository $seanceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CoachRepository $coachRepository,
        SeanceRepository $seanceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->coachRepository = $coachRepository;
        $this->seanceRepository = $seanceRepository;
    }

    public static function getEntityFqcn(): string
    {
        return FicheDePaie::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('coach'),
            DateField::new('periode')
                ->setLabel('Période (mois/année)')
                ->setFormat('MM/yyyy'),
            IntegerField::new('total_heures')
                ->setLabel('Total des heures'),
            MoneyField::new('montant_total')
                ->setLabel('Montant total')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Fiche de paie')
            ->setEntityLabelInPlural('Fiches de paie')
            ->setPageTitle('index', 'Liste des fiches de paie')
            ->setPageTitle('new', 'Créer une fiche de paie')
            ->setPageTitle('edit', 'Modifier une fiche de paie')
            ->setPageTitle('detail', 'Détails de la fiche de paie')
            ->showEntityActionsInlined()
            ->setDefaultSort(['periode' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/fiche_de_paie/index.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewPayslip = Action::new('viewPayslip', 'Voir')
            ->linkToRoute('admin_fiche_de_paie_view', function (FicheDePaie $ficheDePaie): array {
                return [
                    'id' => $ficheDePaie->getId(),
                ];
            })
            ->addCssClass('btn btn-info')
            ->setIcon('fa fa-eye');
            
        $editPayslip = Action::new('editPayslip', 'Modifier')
            ->linkToRoute('admin_fiche_de_paie_edit_form', function (FicheDePaie $ficheDePaie): array {
                return [
                    'id' => $ficheDePaie->getId(),
                ];
            })
            ->addCssClass('btn btn-warning')
            ->setIcon('fa fa-edit');
            
        $deletePayslip = Action::new('deletePayslip', 'Supprimer')
            ->linkToRoute('admin_fiche_de_paie_delete_confirm', function (FicheDePaie $ficheDePaie): array {
                return [
                    'id' => $ficheDePaie->getId(),
                ];
            })
            ->addCssClass('btn btn-danger')
            ->setIcon('fa fa-trash');
            
        // Désactiver l'action NEW standard pour tous les utilisateurs
        $actions = $actions
            ->disable(Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT);
            
        // Ajouter les actions dans l'ordre souhaité
        $actions->add(Crud::PAGE_INDEX, $deletePayslip);
        $actions->add(Crud::PAGE_INDEX, $editPayslip);
        $actions->add(Crud::PAGE_INDEX, $viewPayslip);
        
        // Ajouter les actions pour les autres pages
        $actions->add(Crud::PAGE_EDIT, $viewPayslip);
        $actions->add(Crud::PAGE_DETAIL, $viewPayslip);
            
        // Si l'utilisateur est un responsable, ajouter le bouton de génération de fiche de paie
        if ($this->isGranted('ROLE_RESPONSABLE')) {
            $generatePayslip = Action::new('generatePayslip', 'Générer une fiche de paie')
                ->linkToCrudAction('generatePayslipForm')
                ->createAsGlobalAction();
                
            $actions->add(Crud::PAGE_INDEX, $generatePayslip);
        } else {
            // Pour les coachs, désactiver également les actions d'édition et de suppression
            $actions->disable(Action::EDIT, Action::DELETE);
            $actions->remove(Crud::PAGE_INDEX, $editPayslip);
            $actions->remove(Crud::PAGE_INDEX, $deletePayslip);
        }
        
        return $actions;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        
        // Si l'utilisateur est un coach, filtrer pour n'afficher que ses fiches de paie
        if (!$this->isGranted('ROLE_RESPONSABLE') && $this->isGranted('ROLE_COACH')) {
            $user = $this->getUser();
            $coach = $this->entityManager->getRepository(Coach::class)->findOneBy(['email' => $user->getUserIdentifier()]);
            
            if ($coach) {
                $queryBuilder
                    ->andWhere('entity.coach = :coach')
                    ->setParameter('coach', $coach);
            }
        }
        
        return $queryBuilder;
    }

    /**
     * Affiche le formulaire de génération de fiche de paie
     */
    public function generatePayslipForm(): Response
    {
        $coaches = $this->coachRepository->findAll();
        
        return $this->render('admin/fiche_de_paie/generate_form.html.twig', [
            'coaches' => $coaches,
        ]);
    }

    /**
     * Génère une fiche de paie pour un coach et une période donnée
     */
    #[Route('/admin/fiche-de-paie/generate', name: 'admin_fiche_de_paie_generate', methods: ['POST'])]
    public function generatePayslip(Request $request): Response
    {
        $coachId = $request->request->get('coach');
        $month = $request->request->get('month');
        $year = $request->request->get('year');
        
        $coach = $this->coachRepository->find($coachId);
        
        if (!$coach) {
            $this->addFlash('danger', 'Coach non trouvé');
            return $this->redirectToRoute('admin');
        }
        
        // Créer la date pour la période (premier jour du mois)
        $periode = new \DateTime("$year-$month-01");
        
        // Vérifier si une fiche de paie existe déjà pour ce coach et cette période
        $existingPayslip = $this->entityManager->getRepository(FicheDePaie::class)->findOneBy([
            'coach' => $coach,
            'periode' => $periode,
        ]);
        
        if ($existingPayslip) {
            $this->addFlash('warning', 'Une fiche de paie existe déjà pour ce coach et cette période');
            return $this->redirectToRoute('admin_fiche_de_paie_view', ['id' => $existingPayslip->getId()]);
        }
        
        // Calculer le nombre d'heures à partir des séances validées
        $startDate = clone $periode;
        $endDate = clone $periode;
        $endDate->modify('last day of this month');
        
        $seances = $this->seanceRepository->findValidatedSessionsByCoachAndPeriod(
            $coach,
            $startDate,
            $endDate
        );
        
        $totalHeures = 0;
        foreach ($seances as $seance) {
            // Ajouter 1 heure par séance validée
            $totalHeures += 1;
        }
        
        // Calculer le montant total
        $montantTotal = $totalHeures * $coach->getTarifHoraire();
        
        // Créer la fiche de paie
        $ficheDePaie = new FicheDePaie();
        $ficheDePaie->setCoach($coach);
        $ficheDePaie->setPeriode($periode);
        $ficheDePaie->setTotalHeures($totalHeures);
        $ficheDePaie->setMontantTotal($montantTotal);
        
        $this->entityManager->persist($ficheDePaie);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Fiche de paie générée avec succès');
        
        return $this->redirectToRoute('admin_fiche_de_paie_view', ['id' => $ficheDePaie->getId()]);
    }
    
    /**
     * Affiche une fiche de paie
     */
    #[Route('/admin/fiche-de-paie/{id}', name: 'admin_fiche_de_paie_view', methods: ['GET'])]
    public function viewPayslip(FicheDePaie $ficheDePaie): Response
    {
        return $this->render('admin/fiche_de_paie/view.html.twig', [
            'ficheDePaie' => $ficheDePaie,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'une fiche de paie
     */
    #[Route('/admin/fiche-de-paie/{id}/edit', name: 'admin_fiche_de_paie_edit_form', methods: ['GET'])]
    public function editPayslipForm(FicheDePaie $ficheDePaie): Response
    {
        // Vérifier que l'utilisateur est un responsable
        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier une fiche de paie');
            return $this->redirectToRoute('admin');
        }
        
        $coaches = $this->coachRepository->findAll();
        
        return $this->render('admin/fiche_de_paie/edit_form.html.twig', [
            'ficheDePaie' => $ficheDePaie,
            'coaches' => $coaches,
        ]);
    }
    
    /**
     * Traite le formulaire d'édition d'une fiche de paie
     */
    #[Route('/admin/fiche-de-paie/{id}/update', name: 'admin_fiche_de_paie_update', methods: ['POST'])]
    public function updatePayslip(Request $request, FicheDePaie $ficheDePaie): Response
    {
        // Vérifier que l'utilisateur est un responsable
        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour modifier une fiche de paie');
            return $this->redirectToRoute('admin');
        }
        
        $coachId = $request->request->get('coach');
        $month = $request->request->get('month');
        $year = $request->request->get('year');
        
        $coach = $this->coachRepository->find($coachId);
        
        if (!$coach) {
            $this->addFlash('danger', 'Coach non trouvé');
            return $this->redirectToRoute('admin');
        }
        
        // Créer la date pour la période (premier jour du mois)
        $periode = new \DateTime("$year-$month-01");
        
        // Vérifier si une autre fiche de paie existe déjà pour ce coach et cette période
        $existingPayslip = $this->entityManager->getRepository(FicheDePaie::class)->findOneBy([
            'coach' => $coach,
            'periode' => $periode,
        ]);
        
        if ($existingPayslip && $existingPayslip->getId() !== $ficheDePaie->getId()) {
            $this->addFlash('warning', 'Une fiche de paie existe déjà pour ce coach et cette période');
            return $this->redirectToRoute('admin_fiche_de_paie_view', ['id' => $existingPayslip->getId()]);
        }
        
        // Calculer le nombre d'heures à partir des séances validées
        $startDate = clone $periode;
        $endDate = clone $periode;
        $endDate->modify('last day of this month');
        
        $seances = $this->seanceRepository->findValidatedSessionsByCoachAndPeriod(
            $coach,
            $startDate,
            $endDate
        );
        
        $totalHeures = 0;
        foreach ($seances as $seance) {
            // Ajouter 1 heure par séance validée
            $totalHeures += 1;
        }
        
        // Calculer le montant total
        $montantTotal = $totalHeures * $coach->getTarifHoraire();
        
        // Mettre à jour la fiche de paie
        $ficheDePaie->setCoach($coach);
        $ficheDePaie->setPeriode($periode);
        $ficheDePaie->setTotalHeures($totalHeures);
        $ficheDePaie->setMontantTotal($montantTotal);
        
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Fiche de paie mise à jour avec succès');
        
        return $this->redirectToRoute('admin_fiche_de_paie_view', ['id' => $ficheDePaie->getId()]);
    }

    /**
     * Redirection de l'action de détail par défaut vers notre template personnalisé
     */
    public function detailAction(): Response
    {
        $ficheDePaie = $this->getContext()->getEntity()->getInstance();
        return $this->redirectToRoute('admin_fiche_de_paie_view', [
            'id' => $ficheDePaie->getId(),
        ]);
    }

    /**
     * Calcule les données prévisionnelles d'une fiche de paie
     */
    #[Route('/admin/fiche-de-paie-preview', name: 'admin_fiche_de_paie_preview', methods: ['GET'])]
    public function previewPayslip(Request $request): JsonResponse
    {
        try {
            // Vérifier que l'utilisateur est un responsable
            if (!$this->isGranted('ROLE_RESPONSABLE')) {
                return new JsonResponse(['error' => 'Accès refusé'], 403);
            }
            
            $coachId = $request->query->get('coach');
            $month = $request->query->get('month');
            $year = $request->query->get('year');
            
            if (!$coachId || !$month || !$year) {
                return new JsonResponse(['error' => 'Paramètres manquants'], 400);
            }
            
            $coach = $this->coachRepository->find($coachId);
            
            if (!$coach) {
                return new JsonResponse(['error' => 'Coach non trouvé'], 404);
            }
            
            // Créer la date pour la période (premier jour du mois)
            $periode = new \DateTime("$year-$month-01");
            
            // Calculer le nombre d'heures à partir des séances validées
            $startDate = clone $periode;
            $endDate = clone $periode;
            $endDate->modify('last day of this month');
            
            $seances = $this->seanceRepository->findValidatedSessionsByCoachAndPeriod(
                $coach,
                $startDate,
                $endDate
            );
            
            $totalHeures = 0;
            foreach ($seances as $seance) {
                // Ajouter 1 heure par séance validée
                $totalHeures += 1;
            }
            
            // Calculer le montant total
            $montantTotal = $totalHeures * $coach->getTarifHoraire();
            
            // Formater les données pour éviter les problèmes de conversion
            return new JsonResponse([
                'coach' => [
                    'id' => $coach->getId(),
                    'nom' => $coach->getNom(),
                    'prenom' => $coach->getPrenom(),
                    'tarifHoraire' => (float)$coach->getTarifHoraire()
                ],
                'periode' => $periode->format('m/Y'),
                'totalHeures' => (int)$totalHeures,
                'montantTotal' => (float)$montantTotal
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche la page de confirmation de suppression d'une fiche de paie
     */
    #[Route('/admin/fiche-de-paie/{id}/delete', name: 'admin_fiche_de_paie_delete_confirm', methods: ['GET'])]
    public function deleteConfirm(FicheDePaie $ficheDePaie): Response
    {
        // Vérifier que l'utilisateur est un responsable
        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour supprimer une fiche de paie');
            return $this->redirectToRoute('admin');
        }
        
        return $this->render('admin/fiche_de_paie/delete_confirm.html.twig', [
            'ficheDePaie' => $ficheDePaie,
        ]);
    }
    
    /**
     * Supprime une fiche de paie
     */
    #[Route('/admin/fiche-de-paie/{id}/delete-confirm', name: 'admin_fiche_de_paie_delete', methods: ['POST'])]
    public function deletePayslip(Request $request, FicheDePaie $ficheDePaie): Response
    {
        // Vérifier que l'utilisateur est un responsable
        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour supprimer une fiche de paie');
            return $this->redirectToRoute('admin');
        }
        
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('delete-fiche-de-paie', $submittedToken)) {
            $this->addFlash('danger', 'Token CSRF invalide');
            return $this->redirectToRoute('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => 'App\\Controller\\Admin\\FicheDePaieCrudController'
            ]);
        }
        
        // Supprimer la fiche de paie
        $this->entityManager->remove($ficheDePaie);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Fiche de paie supprimée avec succès');
        
        return $this->redirectToRoute('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => 'App\\Controller\\Admin\\FicheDePaieCrudController'
        ]);
    }
} 