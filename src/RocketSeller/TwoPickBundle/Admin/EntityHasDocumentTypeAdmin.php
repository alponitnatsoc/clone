<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EntityHasDocumentTypeAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('entityEntity',null,array('lable'=>'Entity'))
            ->add('documentTypeDocumentType',null,array('label'=>'Documnet Type'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('entityEntity',null,array('lable'=>'Entity'))
            ->add('documentTypeDocumentType',null,array('label'=>'Documnet Type'))
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
            ->add('entityEntity', 'sonata_type_model_list', array(
                ), array(
                    'placeholder' => 'No entity selected'
                ))
            ->add('documentTypeDocumentType', 'sonata_type_model_list', array(
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
            ->add('entityEntity',null,array('lable'=>'Entity'))
            ->add('documentTypeDocumentType',null,array('label'=>'Documnet Type'))
        ;
    }
}
