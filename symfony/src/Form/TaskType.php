<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use App\Entity\Task;

class TaskType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('concept', TextType::class, [
                'label' => 'Concepto'
            ])
            ->add('details', TextType::class, [
                'label' => 'Detalle'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Guardar'
            ])
        ;
    }
}
