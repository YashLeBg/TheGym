<?php

namespace App\Controller\Admin;

use App\Entity\Coach;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CoachCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $encoder
    ) {}

    public function configureActions(Actions $actions): Actions
    {
        if (!$this->isGranted('ROLE_RESPONSABLE')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour accéder à cette page');
        }

        return $actions;
    }

    public static function getEntityFqcn(): string
    {
        return Coach::class;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['hashPassword'],
        ];
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ChoiceField::new('roles')
                ->setChoices(['Coach' => 'ROLE_COACH'])
                ->setRequired(true)
                ->hideWhenCreating()
                ->hideOnIndex()
                ->hideOnForm()
                ->setFormTypeOption('data', 'ROLE_COACH'),
            TextField::new('nom')
                ->setLabel('Nom')
                ->setRequired(true),
            TextField::new('prenom')
                ->setLabel('Prénom')
                ->setRequired(true),
            EmailField::new('email')
                ->setLabel('Email')
                ->setRequired(true),
            TextField::new('password')
                ->setLabel('Mot de passe')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Répéter le mot de passe'],
                ])
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->onlyOnForms(),
            ChoiceField::new('specialites')
                ->setLabel('Spécialités')
                ->setChoices([
                    'Bodybuilding' => 'bodybuilding',
                    'Crossfit' => 'crossfit',
                    'Powerlifting' => 'powerlifting',
                    'Streetlifting' => 'streetlifting',
                    'Yoga' => 'yoga',
                    'Cardio' => 'cardio',
                    'Calisthenics' => 'calisthenics'
                ])
                ->allowMultipleChoices()
                ->setRequired(true),
            NumberField::new('tarif_horaire')
                ->setLabel('Tarif horaire (€/h)')
                ->setRequired(true)
        ];
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword()
    {
        return function ($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null) {
                return;
            }

            $hash = $this->encoder->hashPassword($event->getData(), $password);
            $form->getData()->setPassword($hash);
        };
    }
}
