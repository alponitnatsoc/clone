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
    private $severancesEntities;
    function __construct($wealthEntities,$pensionEntities,$arsEntities,$severancesEntities ){
        $this->severancesEntities=$severancesEntities;
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
                'label'=>'Entidad Promotora de Salud (EPS) *',
                'required' => false,
                'attr'=>array(
                    "class"=>'autocomW',
                    "placeholder" => "Escribe el nombre de la entidad"
                )
            ))
            ->add('pensionAC', 'text', array(
                'mapped' => false,
                'label'=>'Administradora de Fondo de Pensiones (AFP) *',
                'required' => true,
                'attr'=>array(
                    "class"=>'autocomP',
                    "placeholder" => "Escribe el nombre de la entidad"
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

            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
                'mapped' => false,
                'label'=>'Cesantias*'

            ))

            ->add('severancesAC', 'text', array(
                'mapped' => false,
                'label'=>'Cesantias*',
                'required' => false,
                'attr'=>array(
                    "class"=>'autocomCes',
                    "placeholder" => "Escribe el nombre de la entidad"
                )
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
                    "placeholder" => "Escribe el nombre de la entidad"
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
