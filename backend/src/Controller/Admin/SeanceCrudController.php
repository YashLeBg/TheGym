<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class SeanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('date_heure')
                ->setLabel('Date et heure')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setRequired(true),
            ChoiceField::new('type_seance')
                ->setLabel('Type de séance')
                ->setChoices([
                    'Cours collectif' => 'collective',
                    'Cours individuel' => 'individuelle'
                ])
                ->setRequired(true),
            ChoiceField::new('theme_seance')
                ->setLabel('Thème de la séance')
                ->setChoices([
                    'Bodybuilding' => 'bodybuilding',
                    'Crossfit' => 'crossfit',
                    'Powerlifting' => 'powerlifting',
                    'Streetlifting' => 'streetlifting',
                    'Yoga' => 'yoga',
                    'Cardio' => 'cardio',
                    'Calisthenics' => 'calisthenics'
                ])
                ->setRequired(true),
            ChoiceField::new('statut')
                ->setLabel('Statut')
                ->setChoices([
                    'En attente' => 'prevue',
                    'Programmée' => 'programmee',
                    'Terminée' => 'terminee'
                ])
                ->setRequired(true),
            ChoiceField::new('niveau_seance')
                ->setLabel('Niveau de la séance')
                ->setChoices([
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance'
                ])
                ->setRequired(true)
        ];
    }
}
