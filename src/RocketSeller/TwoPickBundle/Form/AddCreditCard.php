<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class AddCreditCard extends AbstractType
{

    /**
     * La funcion que agrega los campos extra al formulario del registro que se renderiza completo
     * @param el formulario y las opciones que recibe la funcion para crear el formulario
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('credit_card', 'integer', array(
                    "attr" => array(
                        'placeholder' => 'Tu tarjeta de crédito',
                        'title' => 'Tu tarjeta de crédito'
                    ),
                    'required' => true
                ))
                ->add('expiry_date_year', 'integer', array(
                    "attr" => array(
                        'placeholder' => 'Año de vencimiento',
                        'title' => 'Año de vencimiento'
                    ),
                    'required' => true
                ))
                /* ->add('expiry_date', 'date', array(
                  'placeholder' => array(
                  'year' => 'Año', 'month' => 'Mes', 'day' => 'Día'
                  ),
                  'format' => 'yyyy-MM-dd',
                  'years' => range(2016, 2099),
                  'months' => range(1, 12),
                  'label' => 'Fecha vencimiento',
                  'required' => true
                  )) */
                ->add('expiry_date_month', 'choice', array(
                    'choices' => array(
                        '' => 'Mes',
                        '01' => '1',
                        '02' => '2',
                        '03' => '3',
                        '04' => '4',
                        '05' => '5',
                        '06' => '6',
                        '07' => '7',
                        '08' => '8',
                        '09' => '9',
                        '10' => '10',
                        '11' => '11',
                        '12' => '12',
                    ),
                    "attr" => array(
                        'placeholder' => 'Mes de vencimiento',
                        'title' => 'Mes de vencimiento'
                    ),
                    'required' => true
                ))
                ->add('cvv', 'integer', array(
                    "attr" => array(
                        'placeholder' => 'CVV',
                        'title' => 'CVV'
                    ),
                    'required' => true
                ))
                ->add('name_on_card', 'text', array(
                    "attr" => array(
                        'placeholder' => 'Tu nombre en la tarjeta de crédito',
                        'title' => 'Tu nombre en la tarjeta de crédito'
                    ),
                    'required' => true
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => 'RocketSeller\TwoPickBundle\Entity\Person',
        ));
    }

    public function getName()
    {
        return 'app_user_add_credit_card';
    }

}
