<?php

namespace RocketSeller\TwoPickBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\User;

class AddPayMethod extends AbstractType
{

    private $user, $bankEntities, $accountTypeEntities;
    function __construct($user, $bankEntities, $accountTypeEntities){
        $this->user=$user;
        $this->bankEntities=$bankEntities;
        $this->accountTypeEntities=$accountTypeEntities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $date = new \DateTime();
      $date->add(new \DateInterval('P1M'));
      $minYear = $date->format('Y');

      $person = $this->user->getPersonPerson();

      $builder
          ->setAction($options['action'])
          ->setMethod($options['method'])

          //Radio Button for choose pay method
          ->add('pay_method', 'choice', array(
              'choices' => array(
              'Tarjeta de Crédito' => 'Tarjeta de Crédito',
              'Cuenta Bancaria' => 'Cuenta Bancaria',
              ),
              'label' => 'Medio de pago',
              'multiple' => false,
              'expanded' => true,
              'required' => true,
              'data' => 'Tarjeta de Crédito'
          ))

          //Hidden User Id data
          ->add('userId', 'hidden', array(
              'data' => $this->user->getId()
          ))

          //Tarjeta de Crédito form fields
          ->add('name_on_card', 'text', array(
              'label' => 'Nombre en la tarjeta',
              'required' => false,
              'attr' => array('placeholder' => 'Nombre en la tarjeta')
          ))
          ->add('credit_card', 'integer', array(
              'label' => 'Número tarjeta de crédito',
              'required' => false,
              'attr' => array(
                  'placeholder' => '#### #### #### ####',
                  'min' => 1,
                  'step' => 1
              )
          ))
          ->add('expiry_date_month', 'integer', array(
              'label' => 'Fecha de vencimiento',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Mes',
                  'min' => 01,
                  'max' => 12,
                  'maxlength' => 2,
                  'minlength' => 2,
                  'step' => 1
              )
          ))
          ->add('expiry_date_year', 'integer', array(
              'label' => 'Fecha de vencimiento',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Año',
                  'min' => $minYear,
                  'max' => 9999,
                  'maxlength' => 4,
                  'minlength' => 4,
                  'step' => 1
              )
          ))
          ->add('cvv', 'integer', array(
              'label' => 'Código de seguridad:',
              'required' => false,
              'attr' => array(
                  'placeholder' => '###',
                  'min' => 1,
                  'max' => 9999,
                  'maxlength' => 4,
                  'minlength' => 3,
                  'step' => 1
              )
          ))

          //Cuenta Bancaria form fields
          ->add('titularName', 'text', array(
              'label' => 'Titular de la cuenta',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Nombre titular de la cuenta',
                  'readonly' => true,
                  'value' => $person->getNames() . ' ' . $person->getLastName1() . ' ' . $person->getLastName2()
              )
          ))
          ->add('documentType', 'text', array(
              'label' => 'Tipo de documento',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Tipo de documento',
                  'readonly' => true,
                  'value' => $person->getDocumentType()
              )
          ))
          ->add('documentNumber', 'text', array(
              'label' => 'Número de documento',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Número de documento',
                  'readonly' => true,
                  'value' => $person->getDocument()
              )
          ))
          ->add('bankId', 'entity', array(
              'class' => 'RocketSellerTwoPickBundle:Bank',
              'choice_label' =>'name',
              'mapped' => false,
              'required' => false,
              'label'=>'Banco'
          ))
          ->add('accountTypeId', 'entity', array(
              'class' => 'RocketSellerTwoPickBundle:AccountType',
              'choice_label' =>'name',
              'mapped' => false,
              'required' => false,
              'label'=>'Tipo de cuenta'
          ))
          ->add('accountNumber', 'text', array(
              'label' => 'Número de la cuenta',
              'required' => false,
              'attr' => array(
                  'placeholder' => 'Número de la cuenta',
                  'min' => 1,
                  'minlength' => 1,
                  'step' => 1
              )
          ))

          ->add('save', 'submit', array(
              'label' => 'Agregar medio de pago',
              'attr'   =>  array(
              'class'   => 'btn btn-orange')
          ));

    }

    public function getName()
    {
        return '';
    }
}
