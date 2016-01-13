<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmployerHasEntityAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('employerEmployer.personPerson',null,array('label'=>'Employer'))
            ->add('employerEmployer.personPerson.lastName1',null,array('label'=>'Last name'))
            ->add('employerEmployer.personPerson.lastName2',null,array('label'=>'Second last name'))
            ->add('employerEmployer.personPerson.document',null,array('label'=>'Document'))
            ->add('entityEntity',null,array('label'=>'Entity'))
            ->add('entityEntity.entityTypeEntityType',null,array('label'=>'Entity type'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->add('employerEmployer.personPerson',null,array('label'=>'Employer'))
            ->add('employerEmployer.personPerson.lastName1',null,array('label'=>'Last name'))
            ->add('employerEmployer.personPerson.lastName2',null,array('label'=>'Second last name'))
            ->add('employerEmployer.personPerson.document',null,array('label'=>'Document'))
            ->add('entityEntity',null,array('label'=>'Entity'))
            ->add('entityEntity.entityTypeEntityType',null,array('label'=>'Entity type'))
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
            ->add('employerEmployer', 'sonata_type_model_list', array(
                'label'=>'Employer'
                ), array(
                    'placeholder' => 'No employer selected',                                
                ))
            ->add('entityEntity', 'sonata_type_model_list', array(
                'label'=>'Entity'
                ), array(
                    'placeholder' => 'No entity selected',                                
                ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idEmployerHasEntity')
        ;
    }
}
