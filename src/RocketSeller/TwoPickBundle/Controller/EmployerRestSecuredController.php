<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Notification;
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
                                                        'referidosCode' => $realUser->getCode()));
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
        /** @var Notification $notif */
        foreach ( $notifications as $notif ) {
            if( $notif->getStatus() == 1 )
                $activeNotifications->add($notif);
        }
        return $view->setData(array('notifications'=>$activeNotifications))->setStatusCode(200);

    }


}
