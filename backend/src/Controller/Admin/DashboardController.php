<?php

namespace App\Controller\Admin;

use App\Entity\Coach;
use App\Entity\Exercice;
use App\Entity\FicheDePaie;
use App\Entity\Seance;
use App\Entity\Sportif;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TheGym Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Gestion de la salle');
        yield MenuItem::linkToCrud('Exercices', 'fas fa-dumbbell', Exercice::class);
        yield MenuItem::linkToCrud('Séances', 'fas fa-calendar-alt', Seance::class);
        if ($this->isGranted('ROLE_RESPONSABLE')) {
            yield MenuItem::linkToRoute('Statistiques', 'fa fa-chart-bar', 'admin_statistics');
        }

        if ($this->isGranted('ROLE_COACH')) {
            yield MenuItem::section('Mes finances');
            yield MenuItem::linkToCrud('Mes fiches de paie', 'fa fa-money-bill', FicheDePaie::class);
        }

        if ($this->isGranted('ROLE_RESPONSABLE')) {
            yield MenuItem::section('Gestion des utilisateurs');
            yield MenuItem::linkToCrud('Coachs', 'fa fa-user', Coach::class);
            yield MenuItem::linkToCrud('Sportifs', 'fa fa-user', Sportif::class);
            
            yield MenuItem::section('Gestion financière');
            yield MenuItem::linkToCrud('Fiches de paie', 'fa fa-money-bill', FicheDePaie::class);
        }
    }
}
