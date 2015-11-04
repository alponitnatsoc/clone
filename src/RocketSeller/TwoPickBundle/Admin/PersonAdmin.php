<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PersonAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('names', 'text', array('label' => 'Name', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('lastName1','text', array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))            
            ->add('lastName2','text', array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))       
            ->add('documentType', 'choice', array('label'=>'Document Type','choices'  => array('cedula ciudadana' => 'Cedula ciudadana', 'cedula extregaria' => 'Cedula extrangeria' ,'paspote' => 'Pasaporte'))) 
            ->add('document','text', array('label' => 'Document', 'translation_domain' => 'RocketSellerTwoPickBundle'))              
            ->add('birthDate','date', array('label'=>'BirthDay','years'=> range(1910,2015),'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('mainAddress','text', array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
            ->add('media', 'sonata_media_type', array(
                 'provider' => 'sonata.media.provider.file',
                 'context'  => 'person'
            ))            
            ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('names')
            ->add('lastName1')
            ->add('lastName2')
            ->add('document')
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('idPerson')
            ->add('names')
            ->add('lastName1')
            ->add('lastName2')
            ->add('mainAddress')
            ->add('media')
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
            ->add('names')
        ;
    }
}