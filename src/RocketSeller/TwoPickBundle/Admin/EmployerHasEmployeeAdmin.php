<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmployerHasEmployeeAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idEmployerHasEmployee')
            ->add('employeeEmployee.personPerson.names', null, array('label' => 'Employee Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1',null, array('label' => 'Employee LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2',null, array('label' => 'Employee LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employeeEmployee.personPerson.documentType',null, array('label'=>'Employee Document Type')) 
            ->add('employeeEmployee.personPerson.document',null, array('label'=>'Employee Document'))
            ->add('employerEmployer.personPerson.names', null, array('label' => 'Employer Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employerEmployer.personPerson.lastName1',null, array('label' => 'Employer LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employerEmployer.personPerson.lastName2',null, array('label' => 'Employer LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employerEmployer.personPerson.documentType',null, array('label'=>'Employer Document Type')) 
            ->add('employerEmployer.personPerson.document',null, array('label'=>'Employer Document'))
        ;
    }
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idEmployerHasEmployee')
            ->add('employeeEmployee.personPerson',null,array('label' => 'Employee Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1','text', array('label' => 'Employee LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2','text', array('label' => 'Employee LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))                   
            ->add('employeeEmployee.personPerson.document','text', array('label'=>'Employee Document'))
            ->add('employerEmployer.personPerson',null, array('label' => 'Employer Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employerEmployer.personPerson.lastName1','text', array('label' => 'Employer LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employerEmployer.personPerson.lastName2','text', array('label' => 'Employer LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))                   
            ->add('employerEmployer.personPerson.document','text', array('label'=>'Employer Document'))

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
            ->add('employerEmployer', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No employee selected'
                ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEmployerHasEmployee')
        ;
    }
}
