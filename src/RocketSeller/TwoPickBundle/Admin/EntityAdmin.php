<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EntityAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper            
            ->add('name')
            ->add('entityTypeEntityType',null,array('label'=>'Entity type'))
            ->add('office')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idEntity')
            ->add('name')
            ->add('office',null,array('label'=>'Offices'))
            ->add('entityTypeEntityType',null,array('label'=>'Entity Type'))            
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
            ->add('entityTypeEntityType', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No entity selected'
                ))
            ->add('office',null, array(
                ), array(
                'edit' => 'inline',                
                'inline' => 'table',
                'sortable'  => 'position'
            ))
            ->add('action', null, array(                    
                ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'listOrder'
            ))
            ->add('entityFields', null, array(                    
                ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'listOrder'
            ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEntity')
            ->add('entityTypeEntityType',null,array('label'=>'Entity type'))
            ->add('action')
            ->add('entityFields')
            ->add('office',null,array('label'=>'Offices'))
        ;
    }
}
