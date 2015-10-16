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
            ->add('employeeEmployee.personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('employeeEmployee.personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('employeeEmployee.personPerson.documentType',null, array('label'=>'Document Type','choices'  => array('cedula ciudadana' => 'Cedula ciudadana', 'cedula extregaria' => 'Cedula extrangeria' ,'paspote' => 'Pasaporte'))) 
            ->add('employeeEmployee.personPerson.document')              
            ->add('employeeEmployee.personPerson.birthDate',null, array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('employeeEmployee.personPerson.address',null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idEmployeeHasBeneficiary')
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
            ->add('idEmployeeHasBeneficiary')
            ->add('employeeEmployee', 'sonata_type_model_autocomplete', array(
                    'property' => 'personPerson.document',
                    'label' => 'id Employee',
                    'class' => 'Employee',
                    'placeholder'=> 'Search by Employee document',
                    'to_string_callback' => function($entity, $property) {
                        return $entity->getid_employee();
                    },
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
