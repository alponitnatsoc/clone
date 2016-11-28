<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\User;
use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Tests\Request\ParamFetcherTest;

class UtilsRestController extends FOSRestController
{

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getUsersDotationAction()
    {
        //correo nombre telefono nombre de la empleada
        $contracts=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->findAll();
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $targetDate = new \DateTime("2016-08-21");
        $answer= array();
        $reals=new ArrayCollection();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            if($contract->getStartDate()<=$targetDate)
                $reals->add($contract);
        }
        $i=0;
        /** @var Contract $real */
        foreach ($reals as $real) {
            $answer[$i]=array();
            $employerP=$real->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
            /** @var User $user */
            $user=$userRepo->findOneBy(array('personPerson'=>$employerP->getIdPerson()));
            $answer[$i]['correo']=$user->getEmailCanonical();
            $answer[$i]['nombre']=$employerP->getFullName();
            $answer[$i]['telefono']=$employerP->getFullName();
            $answer[$i]['ENombre']=$real->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName();
            $answer[$i]['FechaI']=$real->getStartDate()->format("Y-m-d");
            $i++;
        }

        $view = View::create();
        $view->setData($answer)->setStatusCode(200);

        return $view;
    }


}
