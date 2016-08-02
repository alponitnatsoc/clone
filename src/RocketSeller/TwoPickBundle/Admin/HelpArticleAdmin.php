<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class HelpArticleAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('description')
            ->add('helpCategoryHelpCategory',null,array('label'=>'Category'))
            ->add('type' ,null,array('label'=>'Type'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('title')
            ->add('description')
            ->add('helpCategoryHelpCategory',null,array('label'=>'Category'))
            ->add('type')
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
            
            ->add('title')
            ->add('description')
            ->add('image',null,array('label'=>'Image'))
            ->add('helpCategoryHelpCategory', 'sonata_type_model_list', array(
                'label' => 'Category'
                ), array(
                    'placeholder' => 'No author selected'
                ))
            ->add('type' ,'choice',array(
                'label'=>'Type',
                'choices' => array(
                'art' => 'Article',
                'faq' => 'FAQ'                
            )))
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('idHelpArticle')
            ->add('title')
            ->add('description')
            ->add('type')
        ;
    }
}
