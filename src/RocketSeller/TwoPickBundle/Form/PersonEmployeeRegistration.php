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
use RocketSeller\TwoPickBundle\Entity\Department;
use RocketSeller\TwoPickBundle\Form\BasicPersonRegistration;
use RocketSeller\TwoPickBundle\Form\PersonExtraData;
use RocketSeller\TwoPickBundle\Form\ContractRegistration;

class PersonEmployeeRegistration extends AbstractType
{
    private $timeCommitments;
    private $wealthEntities;
    private $pensionEntities;
    private $idEmployee;
    private $workplaces;
    private $user;
    private $arsEntities;
    private $severancesEntities;
    private $departments;
    private $cities;
    function __construct($idEmployee,$workplaces,$wealthEntities,$pensionEntities,$arsEntities,$severancesEntities,$timeCommitments,$user,$departments = array(), $cities = array()){
        $this->severancesEntities=$severancesEntities;
        $this->timeCommitments=$timeCommitments;
        $this->user=$user;
        $this->wealthEntities=$wealthEntities;
        $this->arsEntities=$arsEntities;
        $this->pensionEntities=$pensionEntities;
        $this->idEmployee=$idEmployee;
        $this->workplaces=$workplaces;
        $this->departments = $departments;
        $this->cities = $cities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($options['action'])
            ->setMethod($options['method'])
            ->add('idEmployee', 'hidden', array(
                'data' => $this->idEmployee,
                'mapped' => false
            ))
            ->add('idContract', 'hidden', array(
                'mapped' => false
            ))
            ->add('person', new BasicPersonRegistration($this->departments,$this->cities), array(
                'property_path' => 'personPerson'))
            ->add('personExtra', new PersonExtraData(), array(
                'property_path' => 'personPerson'))
            ->add('employeeHasEmployers', new ContractRegistration($this->workplaces,$this->timeCommitments,$this->user,$this->departments), array(
                'mapped' => false))
            ->add('entities',  new EntitiesPick($this->wealthEntities,$this->pensionEntities,$this->arsEntities,$this->severancesEntities), array(
                'mapped' => false))
            ->add('verificationCode', 'number', array(
                'label' => 'Codigo de verificación*',
                'required' => true,
                'mapped' => false,
            ))
            ->add('save', 'submit', array(
                'label' => 'Guardar'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => 'RocketSeller\TwoPickBundle\Entity\Employee'
        ));
    }

    public function getName()
    {
        return 'register_employee';
    }
}