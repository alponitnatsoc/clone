<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class OfficeAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idOffice')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idOffice')
            ->add('name')
            ->add('address')
            ->add('countryCountry',null,array('label'=>'Country'))
            ->add('departmentDepartment',null,array('label'=>'Department'))
            ->add('cityCity',null,array('label'=>'City'))
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
            ->add('name',null,array('label'=>'Name'))
            ->add('address',null,array('label'=>'Address'))
            ->add('entityEntity', 'sonata_type_model_list', array(
                'label'=>'Entity'
                ), array(
                    'placeholder' => 'No entity selected',                                
                ))
            ->add('departmentDepartment', 'sonata_type_model_list', array(
                'label'=>'Department'
                ), array(
                    'placeholder' => 'No department selected',                                
                ))
            ->add('countryCountry', 'sonata_type_model_list', array(
                'label'=>'Country'
                ), array(
                    'placeholder' => 'No country selected',                                
                ))
            ->add('cityCity', 'sonata_type_model_list', array(
                'label'=>'City'
                ), array(
                    'placeholder' => 'No city selected',                                
                ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idOffice')
            ->add('name')
            ->add('address')
            ->add('countryCountry',null,array('label'=>'Country'))
            ->add('departmentDepartment',null,array('label'=>'Department'))
            ->add('cityCity',null,array('label'=>'City'))
        ;
    }
}
