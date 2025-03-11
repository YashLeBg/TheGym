<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Doctrine\ORM\EntityManagerInterface;

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
            DateTimeField::new('date_heure')
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
            AssociationField::new('exercices')
                ->setLabel('Exercices')
                ->setRequired(true),
            ChoiceField::new('statut')
                ->setLabel('Statut')
                ->setChoices([
                    'En attente' => 'prevue',
                    'Programmée' => 'programmee',
                    'Terminée' => 'terminee'
                ])
                ->setRequired(true)
                ->hideWhenCreating(),
            ChoiceField::new('niveau_seance')
                ->setLabel('Niveau de la séance')
                ->setChoices([
                    'Débutant' => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé' => 'avance'
                ])
                ->setRequired(true),
            AssociationField::new('coach')
                ->setLabel('Coach')
                ->setRequired(true),
            AssociationField::new('sportifs')
                ->setLabel('Sportifs')
                ->hideWhenCreating()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Action::INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, $this->isGranted('ROLE_COACH') ? 'ROLE_COACH' : 'ROLE_RESPONSABLE')
            ->setPermission(Action::EDIT, $this->isGranted('ROLE_COACH') ? 'ROLE_COACH' : 'ROLE_RESPONSABLE')
            ->setPermission(Action::DELETE, 'ROLE_RESPONSABLE');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Seance) {
            return;
        }

        if (count($entityInstance->getSportifs()) > 3) {
            throw new \RuntimeException("Vous ne pouvez pas ajouter plus de 3 sportifs.");
        }

        foreach ($entityInstance->getSportifs() as $sportif) {
            if ($sportif->getNiveauSportif() !== $entityInstance->getNiveauSeance()) {
                throw new \RuntimeException("Le niveau du sportif (" . $sportif->getNom() .  ") ne correspond pas au niveau de la séance.");
            }
        }

        if (!in_array($entityInstance->getThemeSeance(), $entityInstance->getCoach()->getSpecialites())) {
            throw new \RuntimeException("Le coach n'a pas la spécialité requise pour animer cette séance.");
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
