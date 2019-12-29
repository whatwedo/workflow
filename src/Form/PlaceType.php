<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Workflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PlaceType extends AbstractType
{
    /** @var \Doctrine\Persistence\ManagerRegistry */
    private $doctirine;

    /**
     * @param \Doctrine\Persistence\ManagerRegistry $doctirine
     * @required
     */
    public function setDoctirine(\Doctrine\Persistence\ManagerRegistry $doctirine): void
    {
        $this->doctirine = $doctirine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('workflow',
                HiddenType::class,
                [
                    'property_path' => 'id'
                ]
            )
            ->add('name')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
