<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitiesPick extends AbstractType
{
    private $wealthEntities;
    private $pensionEntities;
    function __construct($wealthEntities,$pensionEntities){
        $this->wealthEntities=$wealthEntities;
        $this->pensionEntities=$pensionEntities;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wealth', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->wealthEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '

            ))
            ->add('pension', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->pensionEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>' '
            ))
            ->add('wealthAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomW')
            ))
            ->add('pensionAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomP')
            ))
            ->add('beneficiaries', 'choice', array(
                'choices' => array(
                    1 => 'Si',
                    -1 => 'No'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>' ',
                'required' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'pick_entities';
    }
}