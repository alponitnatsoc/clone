<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmployeeHasBeneficiaryAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idEmployeeHasBeneficiary')
            ->add('employeeEmployee.idEmployee')
            ->add('employeeEmployee.personPerson.idPerson', null, array('label' => 'Person id', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employeeEmployee.personPerson.documentType',null, array('label'=>'Document Type','choices'  => array('cedula ciudadana' => 'Cedula ciudadana', 'cedula extregaria' => 'Cedula extrangeria' ,'paspote' => 'Pasaporte'))) 
            ->add('employeeEmployee.personPerson.document')              
            ->add('employeeEmployee.personPerson.birthDate',null, array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.mainAddress',null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('employeeEmployee.personPerson',null, array('label' => 'Employee name', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       


            ->add('entityEntity.entityTypeEntityType.idEntityType', 'text', array('label' => 'Tipo entidad', 'translation_domain' => 'RocketSellerTwoPickBundle'))

            ->add('beneficiaryBeneficiary.personPerson',null, array('label' => 'Beneficiary name', 'translation_domain' => 'RocketSellerTwoPickBundle')) 
            ->add('beneficiaryBeneficiary.personPerson.lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('beneficiaryBeneficiary.personPerson.lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            
            

            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('employeeEmployee', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No employee selected'
                ))
            ->add('entityEntity', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No entity selected'
                ))
            ->add('beneficiaryBeneficiary', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No beneficiary selected'
                ))

        ;
    }


    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEmployeeHasBeneficiary')
        ;
    }
}
