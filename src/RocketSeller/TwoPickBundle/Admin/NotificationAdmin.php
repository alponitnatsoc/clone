<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class NotificationAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('sawDate')
            ->add('status')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('sawDate')
            ->add('personPerson.document', null, array('label'=>'document'), null, null)
            ->add('personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))                  
            ->add('personPerson.birthDate',null, array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.mainAddress',null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('status')
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
            ->add('sawDate')
            ->add('personPerson', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No author selected'
                ))
            ->add('status')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('sawDate')
            ->add('personPerson.document', null, array('label'=>'document'), null, null)
            ->add('personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.lastName1',null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('personPerson.lastName2',null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))                  
            ->add('personPerson.birthDate',null, array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('personPerson.mainAddress',null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('status')
        ;
    }
}
