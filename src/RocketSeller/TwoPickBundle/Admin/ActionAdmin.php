<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ActionAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('idAction')    
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('idAction')            
            ->add('personPerson',null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))                       
            ->add('userUser.personPerson.names','text', array('label' => 'In charge', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('actionTypeActionType','text', array('label' => 'Id action type', 'translation_domain' => 'RocketSellerTwoPickBundle')) 
            ->add('entityEntity','text', array('label' => 'Id action type', 'translation_domain' => 'RocketSellerTwoPickBundle'))             
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
                    'placeholder' => 'No user selected'
                ))
            ->add('actionTypeActionType', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No procedure selected'
                ))
            ->add('personPerson', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No procedure selected'
                ))                  
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idAction')
        ;
    }
}
