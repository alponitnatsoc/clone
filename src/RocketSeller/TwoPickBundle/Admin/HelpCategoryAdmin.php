<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class HelpCategoryAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name',null,array('label' => 'Name' ))
            ->add('description',null,array('label' => 'Description' ))
            ->add('active',null,array('label' => 'Status' ))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name',null,array('label' => 'Name' ))
            ->add('description',null,array('label' => 'Description' ))
            ->add('helpArticles',null,array('label'=>'Articles'))
            ->add('active',null,array('label' => 'Status' ))
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
            ->add('name','text',array('label' => 'Name' ))            
            ->add('description','text',array('label' => 'Description' ))            
            ->add('icon',null,array('label'=>'icon'))
            ->add('active','text',array('label' => 'Status' ))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name',null,array('label' => 'Name' ))
            ->add('description',null,array('label' => 'Description' ))
            ->add('active',null,array('label' => 'Status' ))
        ;
    }
}
