<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\SellLog;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;

class EmployerRestSecuredController extends FOSRestController
{
    /**
     * Returns the information of an authenticated user.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the information of an authenticated user.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the requested Ids don't exist"
     *   }
     * )
     * @param $userName
     * @return View
     */
    public function getEmployerInformationAction($userName) {
        $view= View::create();
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $realUser */
        $realUser=$userRepo->findOneBy(array('usernameCanonical'=>$userName));
        if($realUser==null){
            return $view->setStatusCode(404);
        }

        return $view->setStatusCode(200)->setData(array('data'=>$realUser->getPersonPerson(),
                                                        'idUser'=>$realUser->getId(),
                                                        'dataCreditStatus' => $realUser->getDataCreditStatus(),
                                                        'referidosCode' => $realUser->getCode(),
                                                        'money' => $realUser->getMoney() ));
    }
    /**
     * Obtener las notificaciones activas del usuario
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Enviar email requerido",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when no user found"
     *   }
     * )
     *
     * @param $userId
     * @return View

     */
    public function getNotificationByUserAction($userId)
    {

        $view=View::create();
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user=$userRepo->find($userId);
        if($user==null){
            return $view->setStatusCode(404);
        }
        $notifications=$user->getPersonPerson()->getNotifications();
        $activeNotifications = new ArrayCollection();
	    $loPasaronDataCreditoArray = new ArrayCollection();
        /** @var Notification $notification */
        foreach ( $notifications as $notification ) {
            if( $notification->getStatus() == 1 ) {
            	if($notification->getDocumentTypeDocumentType()->getDocCode() == 'MAND') {
		            $sellLogRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:SellLog");
		            
		            $sellLogs = $sellLogRepo->findBy(array("targetUser" => $user));
		            $loPasaronDataCredito = false;
		            /** @var SellLog $sellLog */
		            foreach($sellLogs as $sellLog) {
		            	if($sellLog->getActionType() == 'DC') {
				            $loPasaronDataCredito = true;
			            }
		            }
		            $activeNotifications->add($notification);
		            $loPasaronDataCreditoArray->add($loPasaronDataCredito);
	            } else {
		            $activeNotifications->add($notification);
		            $loPasaronDataCreditoArray->add(false);
	            }
            }
        }
        return $view->setData(array('notifications'=>$activeNotifications,
                                    'loPasaronDataCreditoArr' => $loPasaronDataCreditoArray))
                    ->setStatusCode(200);

    }


}
