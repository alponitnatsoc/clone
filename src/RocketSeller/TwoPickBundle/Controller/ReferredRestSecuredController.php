<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Campaign;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class ReferredRestSecuredController extends FOSRestController
{
	
	/**
	 * get referred info (code, money, campaignStock) <br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "get download document",
	 *   statusCodes = {
	 *     200 = "OK",
	 *     400 = "Bad Request",
	 *     401 = "Unauthorized",
	 *     404 = "Not Found"
	 *   }
	 * )
	 *
	 *
	 * @return View
	 */
	public function getReferredPromoInfoAction() {
		/** @var User $user */
		$user = $this->getUser();
		
		/** @var Campaign $campaignRef */
		$campaignRef = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Campaign')
			->findOneBy(array('description' => 'RefCamp'));
			
		$view = View::create();
		$view->setStatusCode(200);
		$view->setData(array(
			'campaignStock' => $campaignRef->getStock(),
			'money' => $user->getMoney(),
			'code' => $user->getCode(),
			'textCampaign' => 'En Enero gana $' . number_format($campaignRef->getStock()) . ' por cada referido que complete su registro',
			'numberMonths' => '2'
		));
		
		return $view;
	}
	
}