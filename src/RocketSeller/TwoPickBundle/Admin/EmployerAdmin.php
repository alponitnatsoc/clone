<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmployerAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('personPerson', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No person selected'
                ))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idEmployer', null, array('label' => 'Employer id', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.idPerson', null, array('label' => 'Person id', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('personPerson.documentType',null, array('label'=>'Document Type','choices'  => array('cedula ciudadana' => 'Cedula ciudadana', 'cedula extregaria' => 'Cedula extrangeria' ,'paspote' => 'Pasaporte'))) 
            ->add('personPerson.document',null, array('label' => 'Document', 'translation_domain' => 'RocketSellerTwoPickBundle'))              
            ->add('personPerson.birthDate',null, array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.mainAddress',null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idEmployer', 'text', array('label' => 'Employer id', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson',null,array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))                        
            ->add('personPerson.lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('personPerson.lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('personPerson.documentType', 'choice', array('label'=>'Document Type','choices'  => array('cedula ciudadana' => 'Cedula ciudadana', 'cedula extregaria' => 'Cedula extrangeria' ,'paspote' => 'Pasaporte'))) 
            ->add('personPerson.document','text', array('label' => 'Document', 'translation_domain' => 'RocketSellerTwoPickBundle'))              
            ->add('personPerson.birthDate','date', array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.mainAddress','text', array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEmployer')
            ->add('personPerson.names')
            ->add('employerHasEmployees.employeeEmployee.personPerson.names', 'sonata_type_collection', array(
                    'by_reference' => false
                ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'listOrder'
            ))
        ;
    }
}