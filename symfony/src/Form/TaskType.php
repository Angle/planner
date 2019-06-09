<?php

namespace App\Form;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\NotebookRepository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;



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
                'label'         => 'Notebook',
                'class'         => Notebook::class,
                'choice_label'  => 'name',
                'placeholder'   => false,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('n')
                        ->andWhere('n.user = :userId')
                        ->setParameter('userId', $user->getUserId());
                },
            ])
            ->add('concept', TextType::class, [
                'label' => 'Concept'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save'
            ])
        ;
    }
}
