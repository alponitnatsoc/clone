<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Notification;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use DateTime;

class NotificationRestSecuredController extends FOSRestController
{

    /**
     * change notificatcion status <br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "change notificatcion status",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Returned when the notification id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="notificationId", nullable=false,  requirements="\d+", strict=true, description="the notification id.")
     * @RequestParam(name="status", nullable=false,  requirements="-1|0|1", strict=true, description="notification status")
     * @return View
     */
    public function postChangeNotificationStatusAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        $view = View::create();
        if($user==null){
            $view->setStatusCode(401);
            return $view;
        }
        /** @var Notification $notification */
        $notification = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Notification')
            ->find($paramFetcher->get('notificationId'));
        if($notification==null){
            $view->setStatusCode(404);
            return $view;
        }
        if($notification->getPersonPerson()->getIdPerson()!=$user->getPersonPerson()->getIdPerson()){
            $view->setStatusCode(404);
            return $view;
        }
        $notification->setStatus($paramFetcher->get('status'));
        $em=$this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $view->setStatusCode(200);
        $response=array();
        $view->setData($response);
        return $view;
    }
}