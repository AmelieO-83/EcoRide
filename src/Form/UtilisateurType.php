<?php
// src/Form/UtilisateurType.php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    ChoiceType, EmailType, PasswordType, SubmitType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('roles', ChoiceType::class, [
                'label'    => 'Rôles',
                'choices'  => [
                    'Utilisateur' => 'ROLE_USER',
                    'Employé'     => 'ROLE_EMPLOYE',
                    'Admin'       => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            // mot de passe optionnel
            ->add('plainPassword', PasswordType::class, [
                'label'    => 'Nouveau mot de passe',
                'mapped'   => false,
                'required' => false,
                'attr'     => ['autocomplete' => 'new-password'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => ['class' => 'btn btn-success']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
