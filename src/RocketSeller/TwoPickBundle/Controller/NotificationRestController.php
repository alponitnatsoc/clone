<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NotificationRestController extends FOSRestController
{

    /**
     * Add the novelty<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
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
     * @RequestParam(name="status", nullable=false,  requirements="-1|0|1", strict=true, description="the novelty type id.")
     * @return View
     */
    public function postChangeStatusAction(ParamFetcher $paramFetcher)
    {
        $user = $this->getUser();
        $view = View::create();
        if($user==null){
            $view->setStatusCode(401);
            return $view;
        }
        /** @var NotificationEmployer $notification */
        $notification = $this->getdoctrine()
            ->getRepository('RocketSellerTwoPickBundle:Notification')
            ->find($paramFetcher->get('notificationId'));
        if($notification==null){
            $view->setStatusCode(404);
            return $view;
        }
        $notification->setStatus($paramFetcher->get('status'));
        $em=$this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $view->setStatusCode(200);
        $response=array();
        $response["url"]="notifications/employer";
        $serializer = $this->get('jms_serializer');
        $serializer->serialize($response, "json");
        $view->setData($response);
        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }


}
?>