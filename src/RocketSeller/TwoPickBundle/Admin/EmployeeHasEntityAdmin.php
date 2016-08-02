<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmployeeHasEntityAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idEmployeeHasEntity')
            ->add('employeeEmployee.idEmployee')
            ->add('employeeEmployee.personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employeeEmployee.personPerson.document',null,array('label'=>'Document'))              
           ->add('entityEntity',null, array('label' => 'Entity', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('entityEntity.entityTypeEntityType',null, array('label' => 'entity Type', 'translation_domain' => 'RocketSellerTwoPickBundle'))
        ;    

    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idEmployeeHasEntity')
            ->add('employeeEmployee.personPerson.names', 'text', array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employeeEmployee.personPerson.documentType','text', array('label'=>'Document Type')) 
            ->add('employeeEmployee.personPerson.document','text', array('label'=>'Document'))

            ->add('entityEntity',null, array('label' => 'Entity', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('entityEntity.entityTypeEntityType','text', array('label' => 'entity Type', 'translation_domain' => 'RocketSellerTwoPickBundle'))


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
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEmployeeHasEntity')
        ;
    }
}
