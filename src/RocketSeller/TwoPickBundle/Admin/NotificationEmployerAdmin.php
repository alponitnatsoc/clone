<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class NotificationEmployerAdmin extends Admin
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
                ->add('employerEmployer.personPerson.document', null, array('label' => 'document'), null, null)
                ->add('employerEmployer.personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.lastName1', null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.lastName2', null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.birthDate', null, array('label' => 'BirthDay', 'years' => range(1910, 2015), 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.mainAddress', null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('status')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
        ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                //->add('sawDate')
                ->add('sawDate', 'datetime', array(
                    //'input' => 'datetime',
                    //'widget' => 'single_text',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'format' => 'yyyy-MM-dd H:i',
                    //'format' => 'Y-m-d H:i',
                    'label' => 'Fecha de la notificacion',
                    'translation_domain' => 'RocketSellerTwoPickBundle',
                ))
                //->add('sawDate', null, array('format' => 'yyyy-MM-dd H:i:s'))
                ->add('employerEmployer', 'sonata_type_model_list', array(
                    'required' => false,
                    'label' => 'Empleador',
                    'translation_domain' => 'RocketSellerTwoPickBundle'
                        ), array(
                    'placeholder' => 'No selected'
                ))
                //->add('status')
                //->add('status', 'choice', array('choices' => Comment::getStatusList()))
                //->add('status', 'checkbox', array('label' => 'Estado','label_render'=>true))
                ->add('status', 'choice', array(
                    'choices' => array(
                        '0' => 'Activo',
                        '-1' => 'Inactivo',
                        '1' => 'Visto'
                    ),
                    'required' => false,
                    'label' => 'Estado',
                    'translation_domain' => 'RocketSellerTwoPickBundle'
                ))
        ;

//        $subject = $this->getSubject();
//        var_dump($subject);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
                ->add('id')
                ->add('sawDate')
                ->add('employerEmployer.personPerson.document', null, array('label' => 'document'), null, null)
                ->add('employerEmployer.personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.lastName1', null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.lastName2', null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.birthDate', null, array('label' => 'BirthDay', 'years' => range(1910, 2015), 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('employerEmployer.personPerson.mainAddress', null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('status')
        ;
    }

}
