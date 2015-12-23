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
     * @RequestParam(name="names", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day.")
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(name="neighborhood", nullable=false, strict=true, description="neighborhood.")
     * @RequestParam(name="phone", nullable=false, strict=true, description="phone.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @RequestParam(array=true, name="workId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(array=true, name="workMainAddress", nullable=false, strict=true, description="main workplace Address.")
     * @RequestParam(array=true, name="workCity", nullable=false, strict=true, description="workplace city.")
     * @RequestParam(array=true, name="workDepartment", nullable=false, strict=true, description="workplace department.")
     * @return View
     */
    public function postEditPersonSubmitAction(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        /** @var Person $people */
        $people =$user->getPersonPerson();
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
            $employer->setEmployerType($paramFetcher->get('youAre'));
            $datetime = new DateTime();
            $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
            // TODO validate Date
            $people->setBirthDate($datetime);
            // TODO Check if null
            $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $workRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Workplace');
            $people->setCity($cityRepo->find($paramFetcher->get('city')));
            $people->setDepartment($depRepo->find($paramFetcher->get('department')));

            $em = $this->getDoctrine()->getManager();
            $actualWorkplacesId=$paramFetcher->get('workId');
            $actualWorkplacesAdd=$paramFetcher->get('workMainAddress');
            $actualWorkplacesCity=$paramFetcher->get('workCity');
            $actualWorkplacesDept=$paramFetcher->get('workDepartment');
            $actualWorkplaces= new ArrayCollection();
            for($i=0;$i<count($actualWorkplacesAdd);$i++){
                $tempWorkplace=null;
                if($actualWorkplacesId[$i]!=""){
                    /** @var Workplace $tempWorkplace */
                    $tempWorkplace=$workRepo->find($actualWorkplacesId[$i]);
                    if($tempWorkplace->getEmployerEmployer()->getIdEmployer()!=$employer->getIdEmployer()){
                        $view = View::create()->setStatusCode(400);
                        return $view;
                    }

                }else{
                    $tempWorkplace=new Workplace();
                }
                $tempWorkplace->setMainAddress($actualWorkplacesAdd[$i]);
                $tempWorkplace->setCity($cityRepo->find($actualWorkplacesCity[$i]));
                $tempWorkplace->setDepartment($depRepo->find($actualWorkplacesDept[$i]));
                $actualWorkplaces->add($tempWorkplace);
            }
            $workplaces = $employer->getWorkplaces();
            /** @var Workplace $work */
            foreach($workplaces as $work){
                /** @var Workplace $actWork */
                $flag=false;
                foreach($actualWorkplaces as $actWork){
                    if($work->getIdWorkplace()==$actWork->getIdWorkplace()){
                        $flag=true;
                        $work=$actWork;
                        $actualWorkplaces->removeElement($actWork);
                        continue;
                    }
                }
                if(!$flag){
                    $work->setEmployerEmployer(null);
                    $em->persist($work);
                    $em->remove($work);
                    $em->flush();
                    $workplaces->removeElement($work);
                }
            }
            foreach($actualWorkplaces as $work){
                $employer->addWorkplace($work);
            }


            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));

            if (count($errors) == 0) {
                $em->persist($user);
                $em->flush();
                $view->setData(array('url'=>$this->generateUrl('show_dashboard')) )->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }
    /**
     * Get the cities of a department.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     404 = "Returned when the department id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="department", nullable=false,  requirements="\d+", strict=true, description="the desired cities department.")
     * @return View
     */
    public function postCitiesAction(ParamFetcher $paramFetcher)
    {
        $idDepartment=$paramFetcher->get('department');
        $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
        $query = $cityRepo->createQueryBuilder('c')
            ->where('c.departmentDepartment = :department')
            ->setParameter('department', $idDepartment)
            ->orderBy('c.name', 'ASC')
            ->getQuery();


        $cities= $query->getResult();
        $view = View::create();

        if ( count($cities)!= 0) {
            $view->setData($cities)->setStatusCode(200);
            return $view;
        } else {
            $view->setStatusCode(404)->setHeader("error","Department does't exist");
            return $view;
        }
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