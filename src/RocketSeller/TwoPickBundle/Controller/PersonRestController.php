<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
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
     * @RequestParam(name="youAre", nullable=true, strict=true, description="you Are.")
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @RequestParam(name="names", nullable=false,  strict=true, description="names.")
     * @RequestParam(name="lastName1", nullable=false,  strict=true, description="last Name 1.")
     * @RequestParam(name="lastName2", nullable=false,  strict=true, description="last Name 2.")
     * @RequestParam(name="year", nullable=false, strict=true, description="year.")
     * @RequestParam(name="month", nullable=false, strict=true, description="month.")
     * @RequestParam(name="day", nullable=false, strict=true, description="day.")
     * @return View
     */
    public function postEditPersonSubmitStep1Action(ParamFetcher $paramFetcher)
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
            if($paramFetcher->get('youAre')!=null){
                $employer->setEmployerType($paramFetcher->get('youAre'));
            }
            $datetime = new DateTime();
            $datetime->setDate($paramFetcher->get('year'), $paramFetcher->get('month'), $paramFetcher->get('day'));
            // TODO validate Date
            $people->setBirthDate($datetime);
            $em = $this->getDoctrine()->getManager();

            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));

            if (count($errors) == 0) {
                if($employer->getRegisterState()<33)
                    $employer->setRegisterState(33);
                $em->persist($employer);
                $em->persist($people);
                $em->flush();
                $view->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }
    /**
     * Create a Person from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new person from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned when the requested Ids don't exist"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="mainAddress", nullable=false, strict=true, description="mainAddress.")
     * @RequestParam(array=true, name="phonesIds", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(array=true, name="phones", nullable=false, strict=true, description="main workplace Address.")
     * @RequestParam(name="department", nullable=false, strict=true, description="department.")
     * @RequestParam(name="city", nullable=false, strict=true, description="city.")
     * @return View
     */
    public function postEditPersonSubmitStep2Action(ParamFetcher $paramFetcher)
    {
        $user=$this->getUser();
        /** @var Person $people */
        $people =$user->getPersonPerson();
        $employer=$people->getEmployer();

        //all the data is valid
        if (true) {
            if($employer->getRegisterState()<33){
                $view = View::create()->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'1')),
                    'error'=>array('form'=>'please fill all the fields')))->setStatusCode(403);
                return $view;
            }
            $people->setMainAddress($paramFetcher->get('mainAddress'));
            $phoneRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Phone');
            $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $actualPhonesId=$paramFetcher->get('phonesIds');
            $actualPhonesAdd=$paramFetcher->get('phones');
            $people->setCity($cityRepo->find($paramFetcher->get('city')));

            $people->setDepartment($depRepo->find($paramFetcher->get('department')));
            $em = $this->getDoctrine()->getManager();

            $actualPhones= new ArrayCollection();
            for($i=0;$i<count($actualPhonesAdd);$i++){
                $tempPhone=null;
                if($actualPhonesId[$i]!=""){
                    /** @var Phone $tempPhone */
                    $tempPhone=$phoneRepo->find($actualPhonesId[$i]);
                    if($tempPhone->getPersonPerson()->getEmployer()->getIdEmployer()!=$employer->getIdEmployer()){
                        $view = View::create()->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'1')),
                            'error'=>array('wokplaces'=>'you dont have those phones')))->setStatusCode(400);
                        return $view;
                    }

                }else{
                    $tempPhone=new Phone();
                }
                $tempPhone->setPhoneNumber($actualPhonesAdd[$i]);
                $actualPhones->add($tempPhone);
            }
            $phones = $people->getPhones();
            /** @var Phone $phone */
            foreach($phones as $phone){
                /** @var Phone $actPhone */
                $flag=false;
                foreach($actualPhones as $actPhone){
                    if($phone->getIdPhone()==$actPhone->getIdPhone()){
                        $flag=true;
                        $phone=$actPhone;
                        $actualPhones->removeElement($actPhone);
                        continue;
                    }
                }
                if(!$flag){
                    $phone->setPersonPerson(null);
                    $em->persist($phone);
                    $em->remove($phone);
                    $em->flush();
                    $phones->removeElement($phone);
                }
            }
            foreach($actualPhones as $phone){
                $people->addPhone($phone);
            }

            $view = View::create();
            $errors = $this->get('validator')->validate($user, array('Update'));
            if($people->getCity()==null){
                $view->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'2')),
                    'error'=>array('department'=>'not valid city')))->setStatusCode(404);
            }
            if($people->getDepartment()==null){
                $view->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'2')),
                    'error'=>array('department'=>'not valid department')) )->setStatusCode(404);
            }
            if (count($errors) == 0) {
                if($employer->getRegisterState()==33)
                    $employer->setRegisterState(66);
                $em->persist($employer);
                $em->persist($people);
                $em->flush();
                $view->setStatusCode(200);
                return $view;
            } else {
                $view = $this->getErrorsView($errors);
                return $view;
            }
        }
    }
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
     * @RequestParam(name="sameWorkHouse", nullable=true, strict=true, description="work is the same as the file.")
     * @RequestParam(array=true, name="workId", nullable=false, strict=true, description="id if exist else -1.")
     * @RequestParam(array=true, name="workName", nullable=false, strict=true, description="workplace name.")
     * @RequestParam(array=true, name="workMainAddress", nullable=false, strict=true, description="main workplace Address.")
     * @RequestParam(array=true, name="workCity", nullable=false, strict=true, description="workplace city.")
     * @RequestParam(array=true, name="workDepartment", nullable=false, strict=true, description="workplace department.")
     * @return View
     */
    public function postEditPersonSubmitStep3Action(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getUser();
        /** @var Person $people */
        $people =$user->getPersonPerson();
        $employer=$people->getEmployer();

        //all the data is valid
        if (true) {
            if($employer->getRegisterState()<66){
                $view = View::create()->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'1')),
                    'error'=>array('form'=>'please fill all the fields')))->setStatusCode(403);
                return $view;
            }
            $cityRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:City');
            $depRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Department');
            $workRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Workplace');

            $em = $this->getDoctrine()->getManager();
            if($paramFetcher->get("sameWorkHouse")!=null){
                $employer->setSameWorkHouse($paramFetcher->get("sameWorkHouse"));
            }
            $actualWorkplacesId=$paramFetcher->get('workId');
            $actualWorkplacesName = $paramFetcher->get('workName');
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
                        $view = View::create()->setData(array('url'=>$this->generateUrl('edit_profile', array('step'=>'2')),
                            'error'=>array('wokplaces'=>'you dont have those workplaces')))->setStatusCode(400);
                        return $view;
                    }

                }else{
                    $tempWorkplace=new Workplace();
                }
                $tempWorkplace->setName($actualWorkplacesName[$i]);
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
                if($employer->getRegisterState()==66){/*
                    $nowDate=new DateTime();
                    if(($user->getDateCreated()->diff($nowDate)->h)<48){
                        $response = $this->forward('RocketSellerTwoPickBundle:UserRest:postUpdateUserStatusTest', array('id'=>$user->getId(),'status'=>3));
                        if($response->getStatusCode()!=200){
                            $view->setStatusCode(400);
                            return $view;
                        }
                    }*/
                    $employer->setRegisterState(100);
                }
                $em->persist($user);
                $em->flush();
                if($employer->getEmployerHasEmployees()->count()==0){
                    $view->setData(array('url'=>$this->generateUrl('register_employee')) )->setStatusCode(200);
                }else{
                    $view->setData(array('url'=>$this->generateUrl('show_dashboard')) )->setStatusCode(200);
                }
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
     * @RequestParam(name="lastName1", nullable=false,   strict=true, description="lastName1.")
     * @RequestParam(name="documentType", nullable=false, strict=true, description="documentType.")
     * @RequestParam(name="document", nullable=false, strict=true, description="document.")
     * @return View
     */
    public function postInquiryDocumentAction(ParamFetcher $paramFetcher)
    {
        $view = View::create();
        if($this->getUser()==null){
            $view->setStatusCode(403)->setHeader("error","You are not allowed to get information");
            return $view;
        }

        $documentType=$paramFetcher->get('documentType');
        $document=$paramFetcher->get('document');
        $personRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Person');
        /** @var QueryBuilder  $query */
        $query = $personRepo->createQueryBuilder('c')
            ->where('c.documentType = :documentType AND c.document = :document')
            ->setParameter('documentType', $documentType)
            ->setParameter('document', $document)
            ->getQuery();


        /** @var Person $person */
        $person= $query->setMaxResults(1)->getOneOrNullResult();


        if ( $person!=null) {

            $view->setData(array(
                'names' => $person->getNames(),
                'lastName2' => $person->getLastName2(),
                'civilStatus' => $person->getCivilStatus(),
                'gender' => $person->getGender(),
                'documentExpeditionDate' => array(
                    'year'=>$person->getDocumentExpeditionDate()->format("Y"),
                    'month'=>intval($person->getDocumentExpeditionDate()->format("m")),
                    'day'=>intval($person->getDocumentExpeditionDate()->format("d")),),
                'documentExpeditionPlace' => $person->getDocumentExpeditionPlace(),
                'birthDate' => array(
                    'year'=>$person->getBirthDate()->format("Y"),
                    'month'=>intval($person->getBirthDate()->format("m")),
                    'day'=>intval($person->getBirthDate()->format("d")),),
                'birthCountry' => $person->getBirthCountry(),
                'birthDepartment' => $person->getBirthDepartment(),
                'birthCity' => $person->getBirthCity(),
                'mainAddress' => $person->getMainAddress(),
                'department' => $person->getDepartment(),
                'email' => $person->getEmail(),
                'city' => $person->getCity(),
                'phones' => $person->getPhones()->get(0)->getPhoneNumber(),
                'idEmployee' => $person->getEmployee()->getIdEmployee()
            ))->setStatusCode(200);
            return $view;
        } else {
            $view->setStatusCode(404)->setHeader("error","The person does not exist");
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