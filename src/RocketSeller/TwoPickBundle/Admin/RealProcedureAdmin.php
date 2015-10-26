<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class RealProcedureAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idProcedure')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idProcedure')
            ->add('employerEmployer.personPerson.')
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
            ->add('userUser', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No person selected'
                ))
            ->add('procedureTypeProcedureType', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No person selected'
                ))
            ->add('employerEmployer', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No person selected'                    
                ))
            ->add('action', 'sonata_type_collection', array(
                    'by_reference' => false
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
            ->add('idProcedure')
        ;
    }
}
