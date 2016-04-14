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
    private $arsEntities;
    function __construct($wealthEntities,$pensionEntities,$arsEntities){
        $this->wealthEntities=$wealthEntities;
        $this->arsEntities=$arsEntities;
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
                'label'=>'EPS*'

            ))
            ->add('pension', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->pensionEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>'Pension*'
            ))
            ->add('wealthAC', 'text', array(
                'mapped' => false,
                'label'=>'EPS*',
                'required' => false,
                'attr'=>array(
                    "class"=>'autocomW',
                    "placeholder" => "Seleccionar un opción"
                )
            ))
            ->add('pensionAC', 'text', array(
                'mapped' => false,
                'label'=>'Pension*',
                'required' => true,
                'attr'=>array(
                    "class"=>'autocomP',
                    "placeholder" => "Seleccionar un opción"
                )
            ))
            ->add('beneficiaries', 'choice', array(
                'choices' => array(
                    1 => 'Si',
                    -1 => 'No'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿Registrará beneficiarios?*',
                'required' => true
            ))

            ->add('ars', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->arsEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>'ARS*'

            ))

            ->add('arsAC', 'text', array(
                'mapped' => false,
                'label'=>'ARS*',
                'required' => false,
                'attr'=>array(
                    "class"=>'autocomArs',
                    "placeholder" => "Seleccionar un opción"
                )
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