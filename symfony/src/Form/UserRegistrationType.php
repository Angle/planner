<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use App\Entity\User;

class UserRegistrationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => false,
                'attr'  => [
                    'class'         => 'login-control',
                    'placeholder'   => 'first name',
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => false,
                'attr'  => [
                    'class'         => 'login-control',
                    'placeholder'   => 'last name',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr'  => [
                    'class'         => 'login-control',
                    'placeholder'   => 'email',
                ]
            ])
            ->add('password', PasswordType::class, [
                'label'     => false,
                'attr'      => [
                    'class'         => 'login-control',
                    'placeholder'   => 'password',
                ]
            ])
            ->add('checkPassword', PasswordType::class, [
                'label'     => false,
                'mapped'    => false,
                'attr'      => [
                    'class'         => 'login-control',
                    'placeholder'   => 're-type password',
                ]
            ])
        ;
    }
}
