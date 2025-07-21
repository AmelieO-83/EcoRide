<?php
// src/Form/VoitureType.php
namespace App\Form;

use App\Entity\Marque;
use App\Entity\Voiture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoitureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', EntityType::class, [
                'class' => Marque::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez une marque',
                'label' => 'Marque',
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
            ])
            ->add('couleur', TextType::class, [
                'label' => 'Couleur',
            ])
            ->add('energie', ChoiceType::class, [
                'choices' => [
                    'Hybride' => 'hybride',
                    'Électrique' => 'electrique',
                ],
                'placeholder' => 'Type d’énergie',
                'label' => 'Énergie',
            ])
            ->add('fumeur', CheckboxType::class, [
                'required' => false,
                'label' => 'Fumeur autorisé',
            ])
            ->add('animaux', CheckboxType::class, [
                'required' => false,
                'label' => 'Animaux autorisés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voiture::class,
        ]);
    }
}
