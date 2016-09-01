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
            ->add('wealthAC', 'text', array(
                'mapped' => false,
                'label'=>'Entidad Promotora de Salud (EPS)',
                'required' => false,
                'attr'=>array(
                    "class"=>'autocomW',
                    "placeholder" => "Escribe el nombre de la entidad"
                )
            ))
            ->add('wealthExists', 'choice', array(
                'choices' => array(
                    0 => 'Si',
                    1 => 'No, afiliar'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿El empleado está afiliado?',
                'required' => true
            ))

            ->add('pension', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->pensionEntities,
                'choice_label' =>'name',
                'placeholder'=>"Seleccione una opción",
                'mapped' => false,
                'label'=>'Pension'
            ))
            ->add('pensionExists', 'choice', array(
                'choices' => array(
                    0 => 'Si',
                    1 => 'No, afiliar'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿El empleado está afiliado?',
                'required' => true
            ))

            ->add('beneficiaries', 'choice', array(
                'choices' => array(
                    1 => 'Si',
                    -1 => 'No'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿Registrará beneficiarios?',
                'required' => true
            ))

            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
	              'placeholder'=>"Seleccione una opción",
                'mapped' => false,
                'label'=>'Cesantias'
            ))
            ->add('severancesExists', 'choice', array(
                'choices' => array(
                    0 => 'Si',
                    1 => 'No, afílieme'
                ),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'label'=>'¿Está afiliado?',
                'required' => true
            ))

            /*->add('ars', 'entity', array(
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
            ))
            ->add('arsExists', 'choice', array(
                'choices' => array(
                    0 => 'Ya tiene ARS',
                    1 => 'No, deseo que se realice la afiliación'
                ),
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,
                'label'=>' ',
                'required' => true
            ))*/;
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
