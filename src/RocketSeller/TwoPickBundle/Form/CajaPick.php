<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CajaPick extends AbstractType
{
    private $severancesEntities;
    function __construct($severancesEntities){
        $this->severancesEntities=$severancesEntities;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('idEmployerHasEntity', 'hidden')
            ->add('severances', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:Entity',
                'choices' => $this->severancesEntities,
                'choice_label' =>'name',
                'placeholder'=>"",
                'property_path' => 'entityEntity',
                'label'=>'Caja de Compensación Familiar*',
                'required' => true
            ))
            ->add('severancesAC', 'text', array(
                'mapped' => false,
                'label'=>' ',
                'required' => true,
                'attr'=>array("class"=>'autocomS',"placeholder" => "Escribe el nombre de tu entidad")
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RocketSeller\TwoPickBundle\Entity\EmployerHasEntity'

        ));
    }

    public function getName()
    {
        return 'pick_entities_severances';
    }
}
