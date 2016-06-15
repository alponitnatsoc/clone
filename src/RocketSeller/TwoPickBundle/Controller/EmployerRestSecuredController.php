<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;

class EmployerRestSecuredController extends FOSRestController
{
    /**
     * Edit a Beneficiary from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
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

        return $view->setStatusCode(200)->setData(array('data'=>$realUser->getPersonPerson()));
    }


}
