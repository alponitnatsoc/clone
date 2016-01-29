<?php

namespace RocketSeller\TwoPickBundle\Form;

use RocketSeller\TwoPickBundle\Entity\PayMethodFields;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PayMethod extends AbstractType
{
    private $fields;
    function __construct($fields){
        $this->fields=$fields;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PayMethodFields $field */
        foreach ($this->fields as $field){
            // si es una letra en altas
            if($field->getDataType()[0]<="Z"){
                $builder
                    ->add($field->getDataType(), 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:'.$field->getDataType(),
                        'placeholder' => '',
                        'property' => 'name',
                        'multiple' => false,
                        'expanded' => false,
                        'placeholder' => 'Selecciona una opción'
                    ));
            }else{
                $builder
                    ->add($field->getColumnName(), $field->getDataType(), array(
                        'constraints' => array(
                            new NotBlank()
                        )
                    ));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'method_type_fields';
    }
}