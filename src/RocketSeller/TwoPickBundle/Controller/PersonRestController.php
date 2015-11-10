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
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class PersonRestController extends FOSRestController
{	
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="youAre", nullable=false, strict=true, description="you Are.")
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false, strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false, strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false, strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day.")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="neighborhood", nullable=false, strict=true, description="neighborhood.")
     * @RequestParam(name="phone", nullable=false, strict=true, description="phone.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     *
     * @return View
     */
    public function postEditPersonSubmitAction(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        $people =$this->getPerson($user);
        $employer=$people->getEmployer();
        if ($employer==null) {
            $employer=new Employer();
            $people->setEmployer($employer);
        }

        //all the data is valid
        if (true) {
            $people->setNames($paramFetcher->get('names'));
            $people->setLastName1($paramFetcher->get('lastName1'));
            $people->setLastName2($paramFetcher->get('lastName2'));
            $people->setDocument($paramFetcher->get('document'));
            $people->setDocumentType($paramFetcher->get('documentType'));
            $people->setMainAddress($paramFetcher->get('mainAddress'));
            $people->setNeighborhood($paramFetcher->get('neighborhood'));
            $people->setPhone($paramFetcher->get('phone'));
            $datetime = new DateTime();
            $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
            // TODO validate Date
            $people->setBirthDate($datetime);
            // TODO Check if null
            $people->setCity($this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City')->find($paramFetcher->get('city')));
            $people->setDepartment($this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department')->find($paramFetcher->get('department')));

            $em = $this->getDoctrine()->getManager();


            /*$workplaces = new ArrayCollection();
            foreach ($employer->getWorkplaces() as $work) {
                $workplaces->add($work);
            }

            foreach ($workplaces as $work) {
                if (false === $employer->getWorkplaces()->contains($work)) {
                    $work->setEmployerEmployer(null);
                    $em->persist($work);
                    $em->remove($work);
                }
            }*/


            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));

            if (count($errors) == 0) {
                $em->persist($user);
                $em->flush();
                $view->setData($user)->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }
    /**
     * @return Person
     * @param $user
     */

    public function getPerson($user){
        return $user->getPersonPerson();
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