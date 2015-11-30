<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ProductAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper            
            ->add('name',null,array('label'=>'Product name'))
            ->add('price',null,array('label'=>'Price'))
            ->add('description',null,array('label'=>'Description'))
            ->add('validity',null,array('label'=>'Validity'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->add('name',null,array('label'=>'Product name'))
            ->add('price',null,array('label'=>'Price'))
            ->add('description',null,array('label'=>'Description'))
            ->add('validity',null,array('label'=>'Validity'))            
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
            ->add('name',null,array('label'=>'Product name'))
            ->add('price',null,array('label'=>'Price'))
            ->add('description',null,array('label'=>'Description'))
            ->add('validity',null,array('label'=>'Validity'))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idProduct')
            ->add('name',null,array('label'=>'Product name'))
            ->add('price',null,array('label'=>'Price'))
            ->add('description',null,array('label'=>'Description'))
            ->add('validity',null,array('label'=>'Validity'))
        ;
    }
}
