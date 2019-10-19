<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Form\DataTransformer\EntityToValueTransformer;
use whatwedo\WorkflowBundle\Service\DoctrineHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransitionType extends AbstractType
{

    /** @var DoctrineHelper */
    private $doctirineHelper;

    /**
     * @param DoctrineHelper $doctirineHelper
     * @required
     */
    public function setDoctirineHelper(DoctrineHelper $doctirineHelper): void
    {
        $this->doctirineHelper = $doctirineHelper;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Transition $data */
        $data = $builder->getData();
        $builder
            ->add('name')
            ->add(
                'workflow',
                HiddenType::class
                )
            ->add(
                'froms',
                    EntityType::class,
                    [
                        'choices' => $data->getWorkflow()->getPlaces(),
//                        'choice_label' => function(Place $choice, $key, $value) {
//                            // adds a class like attending_yes, attending_no, etc
//                            return $choice->getName();
//                        },
//                        'choice_value' => function($choice) {
//                            // adds a class like attending_yes, attending_no, etc
//                            if ($choice instanceof Place) {
//                                return $choice ? $choice->getId() : '';
//                            }
//                            return '';
//                        },
                        'class' => Place::class,
                        'multiple' => true,

                    ]
                )
            ->add('tos'
                ,
                EntityType::class,
                [
                    'choices' => $data->getWorkflow()->getPlaces(),
//                    'choice_label' => function(Place $choice, $key, $value) {
//                        // adds a class like attending_yes, attending_no, etc
//                        return $choice->getName();
//                    },
//                    'choice_value' => function($choice) {
//                        // adds a class like attending_yes, attending_no, etc
//                        if ($choice instanceof Place) {
//                            return $choice ? $choice->getId() : '';
//                        }
//                        return '';
//                    },
                    'class' => Place::class,
                    'multiple' => true,

                ]
            )
        ;



        $builder->get('workflow')->addModelTransformer(new EntityToValueTransformer($this->doctirineHelper->getRepository(Workflow::class)));
//        $builder->get('froms')->addModelTransformer(new EntityToValueTransformer($this->doctirineHelper->getRepository(Place::class)));
//        $builder->get('tos')->addModelTransformer(new EntityToValueTransformer($this->doctirineHelper->getRepository(Place::class)));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transition::class,
        ]);
    }
}
