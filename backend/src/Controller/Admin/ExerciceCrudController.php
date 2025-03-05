<?php

namespace App\Controller\Admin;

use App\Entity\Exercice;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExerciceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Exercice::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom')
                ->setLabel('Nom')
                ->setRequired(true),
            TextField::new('description')
                ->setLabel('Description')
                ->setRequired(true),
            NumberField::new('duree_estimee')
                ->setLabel('Durée estimée (en minutes)')
                ->setRequired(true),
            ChoiceField::new('difficulte')
                ->setLabel('Difficulté')
                ->setChoices(['Facile' => 'facile', 'Moyen' => 'moyen', 'Difficile' => 'difficile'])
                ->setRequired(true)
        ];
    }
}
