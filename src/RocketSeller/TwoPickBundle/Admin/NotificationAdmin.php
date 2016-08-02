<?php

namespace RocketSeller\TwoPickBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class NotificationAdmin extends Admin {

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('type')
                ->add('title')
                ->add('accion')
                ->add('status')
                ->add('sawDate')
                ->add('deadline')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                //->add('id')
                ->add('type')
                ->add('title')
                ->add('personPerson.document', null, array('label' => 'document'), null, null)
                ->add('personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('personPerson.lastName1', null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('personPerson.lastName2', null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                //->add('employerEmployer.personPerson.birthDate', null, array('label' => 'BirthDay', 'years' => range(1910, 2015), 'translation_domain' => 'RocketSellerTwoPickBundle'))
                //->add('employerEmployer.personPerson.mainAddress', null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('status')
                ->add('relatedLink')
                ->add('accion')
                ->add('description')
                ->add('sawDate')
                ->add('deadline')
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
    protected function configureFormFields(FormMapper $formMapper) {
        $subject = $this->getSubject();

        $formMapper
                //->with('form', array('class' => 'col-md-9'))
                ->add('title', 'text', array('required' => true))
                ->add('type', 'choice', array(
                    'choices' => array(
                        'alert' => 'Alert',
                        'notification' => 'Notification',
                        'mensaje' => 'Mensaje',
                        'informative' => 'Informative',
                        'reminder' => 'Reminder'
                    ),
                    'placeholder' => 'Seleccione...',
                    'required' => true,
                    //'label' => 'Estado',
                    'translation_domain' => 'RocketSellerTwoPickBundle'
                ))
                ->add('sawDate', 'datetime', array(
                    //'input' => 'datetime',
                    //'widget' => 'single_text',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'format' => 'yyyy-MM-dd H:i',
                    //'format' => 'Y-m-d H:i',
                    //'label' => 'Fecha de la notificacion',
                    'translation_domain' => 'RocketSellerTwoPickBundle',
                ))
                //->add('sawDate', null, array('format' => 'yyyy-MM-dd H:i:s'))
                ->add('relatedlink', 'text')
                ->add('accion', 'text')
                ->add('description', 'textarea')
                ->add('deadline', 'datetime', array(
                    //'input' => 'datetime',
                    //'widget' => 'single_text',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'format' => 'yyyy-MM-dd H:i',
                    //'format' => 'Y-m-d H:i',
                    //'label' => 'Fecha de la notificacion',
                    'translation_domain' => 'RocketSellerTwoPickBundle',
                ))
                ->add('personPerson', 'sonata_type_model_list', array(
                    'required' => false,
                    //'label' => 'Empleador',
                    'translation_domain' => 'RocketSellerTwoPickBundle'
                        ), array(
                    'placeholder' => 'No selected'
        ));

        if ($subject->getPersonPerson()) {
            //->add('status')
            //->add('status', 'choice', array('choices' => Comment::getStatusList()))
            //->add('status', 'checkbox', array('label' => 'Estado','label_render'=>true))
            $formMapper->add('status', 'choice', array(
                'choices' => array(
                    '1' => 'Activo',
                    '-1' => 'Inactivo',
                    '0' => 'Visto'
                ),
                //'placeholder' => 'Seleccione...',
                'required' => true,
                //'label' => 'Estado',
                'translation_domain' => 'RocketSellerTwoPickBundle'
            ));
        }
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper) {
        $showMapper
                //->add('id')
                ->add('title')
                ->add('type')
                //->add('sawDate', 'datetime', array('format' => 'yyyy-MM-dd H:i'))
                ->add('sawDate', 'datetime')
                ->add('deadline', 'datetime')
                ->add('personPerson.document', null, array('label' => 'document'), null, null)
                ->add('personPerson.names', null, array('label' => 'Names', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('personPerson.lastName1', null, array('label' => 'LastName1', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('personPerson.lastName2', null, array('label' => 'LastName2', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                //->add('employerEmployer.personPerson.birthDate', null, array('label' => 'BirthDay', 'years' => range(1910, 2015), 'translation_domain' => 'RocketSellerTwoPickBundle'))
                //->add('employerEmployer.personPerson.mainAddress', null, array('label' => 'Address', 'translation_domain' => 'RocketSellerTwoPickBundle'))
                ->add('relatedlink')
                ->add('accion')
                ->add('description')
                ->add('status')
        ;
    }

}
