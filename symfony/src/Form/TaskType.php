<?php

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Security\Core\Security;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;

class TaskType extends AbstractType
{
    /** @var Security $security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('notebook', EntityType::class, [
                'label'         => false,
                'class'         => Notebook::class,
                'choice_label'  => 'name',
                'placeholder'   => false,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('notebook')
                        ->leftJoin('notebook.shareMaps', 'map', 'WITH', 'map.user = :userId')
                        ->where('notebook.user = :userId')
                        ->orWhere('map.user = :userId')
                        ->setParameter('userId', $user->getUserId())
                        ->distinct();
                },
            ])
            ->add('concept', TextType::class, [
                'label' => false,
                'attr'  => [
                    'class'         => 'input-big',
                    'placeholder'   => 'Write task concept..',
                ]
            ])
            ->add('openYearNumber', IntegerType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'YYYY',
                    'class' => 'input-small',
                    'min'   => '1000',
                    'max'   => '3000',
                    'step'  => '1'
                ]
            ])
            ->add('openWeekNumber', IntegerType::class, [
                'label' => false,
                'attr'  => [
                    'placeholder' => 'WW',
                    'class' => 'input-small',
                    'min'   => '0',
                    'max'   => '53',
                    'step'  => '1'
                ]
            ])
        ;
    }
}
