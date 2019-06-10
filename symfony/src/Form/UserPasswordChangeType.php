<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserPasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'mapped'    => false,
                'label'     => false,
                'attr'      => [
                    'class'         => 'input-big',
                    'placeholder'   => 'old password',
                ]
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped'    => false,
                'label'     => false,
                'attr'      => [
                    'class'         => 'input-big',
                    'placeholder'   => 'new password',
                ]
            ])
            ->add('checkPassword', PasswordType::class, [
                'label'     => false,
                'mapped'    => false,
                'attr'      => [
                    'class'         => 'input-big',
                    'placeholder'   => 're-type password',
                ]
            ])
        ;
    }
}
