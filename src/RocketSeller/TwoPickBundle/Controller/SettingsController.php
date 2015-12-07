<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use RocketSeller\TwoPickBundle\Entity\Settings;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    
    public function manageSettingsAction(Request $request)
    {
    	$user=$this->getUser();

        $repository = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Settings');

        $defaultData = array(
            'notificaciones' => $repository->getSetting($user, 'notificaciones')->getSettingValue()
        );
        
    	$form = $this->createFormBuilder($defaultData)
            ->add('notificaciones', 'choice', array(
                'choices' => array(
                    'all'   => 'Todas las notificaciones',
                    'account' => 'Solo de mi cuenta',
                ),
                'multiple' => false,
                'expanded' => false)
            )
            ->add('credit_card', 'text')
            ->add('expiry_date', 'text')
            ->add('cvv', 'text')
            ->add('name_on_card', 'text')
            ->add('save', 'submit', array('label' => 'Submit'))
            ->getForm();

    	$form->handleRequest($request);
        if ($form->isValid()) {     
        	$data = $form->getData();

            if(isset($data["notificaciones"])){
                $setting = $repository->getSetting($user, 'notificaciones');
                $setting->setSettingValue($data["notificaciones"]);
                $em = $this->getDoctrine()->getManager();
                $em->persist($setting);
            }

            $em->flush();
	    } 

	    $redes = [];
    	$redes["facebook"] = $user->getFacebookId()!=""? "disabled": "";
    	$redes["google"] = $user->getGoogleId()!=""? "disabled": "";
    	$redes["linkedin"] = $user->getLinkedinId()!=""? "disabled": "";
    	
    	
    	return $this->render('RocketSellerTwoPickBundle:Settings:manageSettings.html.twig',
    		array(
    			'form' => $form->createView(),
    			'redes' => $redes
    		)
    	);
    }
}
