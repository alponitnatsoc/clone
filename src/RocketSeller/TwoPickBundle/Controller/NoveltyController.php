<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Application\Sonata\MediaBundle\Document\Media;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PayrollDetail;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\NoveltyForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoveltyController extends Controller {

    public function selectNoveltyAction($idPayroll, Request $request) {
        $noveltyTypeRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NoveltyType");
        $noveltyTypes=$noveltyTypeRepo->findAll();
        $noveltyTypesGroups=array();
        $noveltyTypeToShow=new ArrayCollection();

        $payRollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
	      /** @var Payroll $payRol */
        $payRol = $payRollRepo->find($idPayroll);
        $empId = $payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee();

        /** @var NoveltyType $NT */
        foreach ($noveltyTypes as $NT) {
            if($NT->getGrupo()!="no_show" && strpos($NT->getDisplayOn(), $payRol->getContractContract()->getTimeCommitmentTimeCommitment()->getCode() ) !== false){
                $noveltyTypeToShow->add($NT);
            }

            if(!isset($noveltyTypesGroups[$NT->getGrupo()])&&$NT->getGrupo()!="no_show" && strpos($NT->getDisplayOn(), $payRol->getContractContract()->getTimeCommitmentTimeCommitment()->getCode() ) !== false ){
                $noveltyTypesGroups[$NT->getGrupo()]=$NT->getGrupo();
            }
        }

        $form = $this->createFormBuilder()
                ->setAction("/novelty/add/$idPayroll/")
                ->setMethod('POST')
                ->add('noveltyTypeGroup', 'choice', array(
                    'choices' => $noveltyTypesGroups,
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Tipo de novedad',
                    'property_path' => 'noveltyTypeNoveltyType'))
                ->add('noveltyType', 'entity', array(
                    'class' => 'RocketSellerTwoPickBundle:NoveltyType',
                    'choices' =>$noveltyTypeToShow,
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'label' => 'Tipo de novedad',
                    'property_path' => 'noveltyTypeNoveltyType'))
                ->add('save', 'submit', array(
                    'label' => 'Siguiente',
                ))
                ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var NoveltyType $noveltyType */
            $noveltyType = $form->get('noveltyType')->getData();

            return $this->redirectToRoute('novelty_add', array(
                        'idPayroll' => $idPayroll,
                        'noveltyTypeId' => $noveltyType->getIdNoveltyType()), 301);
        }
        $options = $form->get('noveltyType')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();
        return $this->render('RocketSellerTwoPickBundle:Novelty:selectNovelty.html.twig', array(
            'form' => $form->createView(),
            'choices' => $choices,
            'empId' => $empId));
    }

    /**
     * @param $idPayroll
     * @param $noveltyTypeId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addNoveltyAction($idPayroll, $noveltyTypeId, Request $request) {
    	  $payRollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        /** @var Payroll $payRol */
        $payRol = $payRollRepo->find($idPayroll);
        if ($payRol == null) {
            return $this->redirectToRoute('ajax', array(), 301);
        }
        $payRollDetail = new PayrollDetail();
        $novelty = new Novelty();
        $novelty->setPayrollDetailPayrollDetail($payRollDetail);
        $noveltyTypeRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');
        /** @var NoveltyType $noveltyType */
        $noveltyType = $noveltyTypeRepo->find($noveltyTypeId);
        if ($noveltyType == null) {
            return $this->redirectToRoute('novelty_select', array('idPayroll' => $idPayroll), 301);
        }

      /*  if($noveltyType->getName() == "Terminar contrato" ){

          $idToCall = $payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getIdEmployee();
          return $this->redirectToRoute('final_liquidation', array(
                      'id' => $idToCall));
        }*/

        $novelty->setNoveltyTypeNoveltyType($noveltyType);
        $requiredDocuments = $noveltyType->getRequiredDocuments();
        $hasDocuments = false;
        $user = $this->getUser();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach ($requiredDocuments as $rd) {
            $hasDocuments = true;
            $tempDoc = new Document();
            if ($rd->getPersonType() == "employer") {
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson());
            } else if ($rd->getPersonType() == "employee") {
                $tempDoc->setPersonPerson($payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson());
            } else {
                $tempDoc->setContractContract($payRol->getContractContract());
            }
            $tempDoc->setDocumentTypeDocumentType($rd->getDocumentTypeDocumentType());
            $tempDoc->setName(str_replace(" ", "_", $rd->getDocumentTypeDocumentType()->getName()));
            $tempDoc->setStatus(true);
            $novelty->addDocument($tempDoc);
        }
        $requiredFields = $noveltyType->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields, /*$hasDocuments*/ false,$this->generateUrl("novelty_add",array("noveltyTypeId"=>$noveltyType->getIdNoveltyType(),"idPayroll"=>$idPayroll)),$idPayroll), $novelty);// This is because Camilo wanted that its simple to the user to create novelties
        $form->handleRequest($request);
        if ($form->isValid()) {
	        $isOkForm = $this->validateAndPersistNovelty($novelty, $payRol, $noveltyType);
	        if($isOkForm[0] == true){
		        return $this->redirectToRoute('show_dashboard',array(),301);
	        }
          else {
	          return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array(
		          'form' => $form->createView(),
		          'errno' => $isOkForm[1]
	          ));
          }
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array('form' => $form->createView()));
    }

    public function editNoveltyAction($noveltyId, $notificationReferenced, Request $request) {

        $noveltyRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Novelty');
        /** @var Novelty $novelty */
        $novelty = $noveltyRepo->find($noveltyId);
        if ($novelty == null) {
            return $this->redirectToRoute('ajax', array(), 301);
        }
        if ($novelty->getDocuments()->count() == 0)
            $hasDocuments = false;
        else
            $hasDocuments = true;

        $requiredFields = $novelty->getNoveltyTypeNoveltyType()->getRequiredFields();

        $form = $this->createForm(new NoveltyForm($requiredFields, $hasDocuments), $novelty);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($novelty);
            $em->flush();
            if (!$this->checkNoveltyFulfilment($novelty, $form)) {
                if ($notificationReferenced != -1) {
                    $user = $this->getUser();
                    $notificationRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification");
                    /** @var Notification $notification */
                    $notification = $notificationRepo->find($notificationReferenced);
                    $notification=$this->createNotification($notification,0,new \DateTime(),null,"Los datos de la novedad se llenaron correctamente",null,null,null,$user->getPersonPerson());
                    $em->persist($notification);
                    $em->flush();
                }
            } else {
                if ($notificationReferenced == -1) {
                    /** @var User $user */
                    $user = $this->getUser();
                    $notification=$this->createNotification(null,1,null,"","Faltan llenar algunos datos de la novedad " . $novelty->getName(),"Novedad Incompleta","Completar","alert",$user->getPersonPerson());
                    $em->persist($notification);
                    $em->flush();
                    $notification->setRelatedLink($this->generateUrl("novelty_edit", array('noveltyId' => $novelty->getIdNovelty(), 'notificationReferenced' => $notification->getId())));
                    $em->persist($notification);
                    $em->flush();
                }
            }
            return $this->redirectToRoute('ajax', array(), 301);
        }
        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Notification $notification
     * @param $status
     * @param $sawDate
     * @param $relatedLink
     * @param $description
     * @param $title
     * @param $action
     * @param $type
     * @param Person $person
     * @return Notification
     */
    private function createNotification($notification=null, $status, $sawDate,$relatedLink,$description, $title, $action,$type, $person) {
        $notification= $notification==null?new Notification():$notification;
        $notification->setAccion($action);
        $notification->setRelatedLink($relatedLink);
        $notification->setSawDate($sawDate);
        $notification->setStatus($status);
        $notification->setDescription($description);
        $notification->setTitle($title);
        $notification->setType($type);
        $notification->setPersonPerson($person);
        return $notification;
    }

    /**
     *
     * @param Novelty $novelty
     * @param Form $form
     * @return bool
     */
    private function checkNoveltyFulfilment($novelty) {
        //check each document
        $noveltyType = $novelty->getNoveltyTypeNoveltyType();
        $documents = $novelty->getDocuments();
        $requiredDocuments = $noveltyType->getRequiredDocuments();
        /** @var NoveltyTypeHasDocumentType $rd */
        foreach ($requiredDocuments as $rd) {
            /** @var Document $document */
            foreach ($documents as $document) {
                if ($rd->getDocumentTypeDocumentType() == $document->getDocumentTypeDocumentType()) {
                    if ($document->getMediaMedia() == null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function setWorkedDaysAction($idNovelty) {
        $user = $this->getUser();

        $noveltyRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Novelty");
        $novelty=$noveltyRepo->findOneBy(array("idNovelty"=>$idNovelty));

        $employerPersonID = $novelty->getSqlPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()->getIdPerson();

        if( $employerPersonID != $user->getPersonPerson()->getIdPerson() ){
          return $this->redirectToRoute('show_dashboard');
        }

        //necesito retornar el num de dias y todo lo necesario para llamar el POST que actualice los dias
        return $this->render('RocketSellerTwoPickBundle:Novelty:changeDays.html.twig', array(
            'novelty' => $novelty,
            'employee' => $novelty->getSqlPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()));
    }

    public function  updateWorkedDaysAction($empId, $idNovelty, $daysAmount, $idPerson = -1){

      $request = $this->container->get('request');

      $noveltyRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Novelty");
      $novelty=$noveltyRepo->findOneBy(array('idNovelty' => $idNovelty));

      if($idPerson == -1) {
        $user = $this->getUser();
        $idPerson = $user->getPersonPerson()->getIdPerson();
      }

      if($idPerson != $novelty->getSqlPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()->getIdPerson()){
        return $this->redirectToRoute('show_dashboard');
      }

      //retrieve the novelty info in order to be restored later
      $request->setMethod("GET");
      $info = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getEmployeeNovelty',array(
          "employeeId" => $empId
          ), array('_format' => 'json'));

      $info = json_decode($info->getContent(),true);

      //First deletes on SQL the stored novelty
      $request->setMethod("POST");
      $request->request->add(array(
          "employee_id"=>$empId,
          "novelty_consec"=>$info['NOV_CONSEC']
      ));
      $deleteAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postDeleteNoveltyEmployee', array('request'=>$request), array('_format' => 'json'));
      if($deleteAnswer->getStatusCode()!=200){
          return $this->redirectToRoute('show_dashboard');
      }

      //now adds to SQL the same novelty but with another units value

      $request->setMethod("POST");
      $request->request->add(array(
          "employee_id"=>$empId,
          "novelty_concept_id"=>1,
          "unity_numbers"=>$daysAmount,
          "novelty_start_date"=>date("d-m-Y", strtotime($info['NOV_FECHA_DESDE_CAUSA'])),
          "novelty_end_date"=> date("d-m-Y", strtotime($info['NOV_FECHA_HASTA_CAUSA'])),
          "novelty_base"=>$novelty->getSqlValue() / $novelty->getUnits()
      ));

      $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postAddNoveltyEmployee', array('request' => $request ), array('_format' => 'json'));
      if($insertionAnswer->getStatusCode()!=200){
          return $this->redirectToRoute('show_dashboard');
      }

      return $this->redirectToRoute('payroll');
    }

    public function validateAndPersistNoveltyAction($idPayroll, $idNoveltyType, $noveltyFields){
      $noveltyType = $this->getDoctrine()
          ->getRepository('RocketSellerTwoPickBundle:NoveltyType')
          ->find($idNoveltyType);

      $payroll = $this->getDoctrine()
          ->getRepository('RocketSellerTwoPickBundle:Payroll')
          ->find($idPayroll);
      $novelty = new Novelty();
      $novelty->setNoveltyTypeNoveltyType($noveltyType);
      $novelty->setPayrollDetailPayrollDetail(new PayrollDetail());

      $novelty->setName($noveltyType->getName());

      if(array_key_exists('dateStart', $noveltyFields))
        $novelty->setDateStart(DateTime::createFromFormat('m/d/Y', $noveltyFields['dateStart']));

      if(array_key_exists('dateEnd', $noveltyFields))
        $novelty->setDateEnd(DateTime::createFromFormat('m/d/Y', $noveltyFields['dateEnd']));

      if(array_key_exists('units', $noveltyFields))
        $novelty->setUnits($noveltyFields['units']);

      if(array_key_exists('amount', $noveltyFields))
        $novelty->setAmount($noveltyFields['amount']);

      if(array_key_exists('description', $noveltyFields))
        $novelty->setDescription($noveltyFields['description']);

      return $this->validateAndPersistNovelty($novelty, $payroll, $noveltyType);
    }

    /**
     * @param Novelty $novelty
     * @param Payroll $payRol
     * @param NoveltyType $noveltyType
     * @return array
     */
    public function validateAndPersistNovelty($novelty, $payRol, $noveltyType){
    	$answerArr = array();
	    array_push($answerArr,true); //Index 0 - Determines if is false or true the validation
	    array_push($answerArr, ""); //Index 1 - If index 0 is false, sets a custom error msg to display to the user
	
	    if($novelty->getNoveltyTypeNoveltyType()->getSimpleName() == 'VAC' &&
		    $payRol->getContractContract()->getTimeCommitmentTimeCommitment()->getCode() == 'XD')
	    {
		
		    $context = array( 'emailType' => 'reportVacacionesXD',
			    'payrollId' => $payRol->getIdPayroll(),
			    'contractId' => $payRol->getContractContract()->getIdContract(),
			    'startDate' => $novelty->getDateStart(),
			    'endDate' => $novelty->getDateEnd(),
			    'employerName' => $payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson()->getFullName(),
			    'employeeName' => $payRol->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName(),
		    );
		
		    $smailer = $this->get('symplifica.mailer.twig_swift');
		    $send = $smailer->sendEmailByTypeMessage($context);
		
		    $answerArr[0] = false;
		    $answerArr[1] = "Actualmente no puedes reportar vacaciones de tus empleados por dias por este medio. Por favor comunicate con nosotros al telefono 3504612013.";
		    return $answerArr;
	    }
	    
	    $utils = $this->get('app.symplifica_utils');
        $em = $this->getDoctrine()->getManager();

        $constrainList = $novelty->getNoveltyTypeNoveltyType()->getRequiredFields();

	    /** @var NoveltyTypeFields $constrain */
	    foreach ($constrainList as $constrain){
	    	if($constrain->getColumnName() == "date_start" && $constrain->getDisplayable() == true){
	    		if($novelty->getDateStart() == null){
	    			$answerArr[0] = false;
				    $answerArr[1] = "No pueden haber campos vacios, completelos antes de continuar";
				    return $answerArr;
			    }
			    else{

			    	//Calls dedicated function to evaluate the constrain of Dates
				    $isValidD = $utils->novelty_date_constrain_to_date_validation($constrain->getNoveltyDataConstrain(), $novelty->getDateStart(),$payRol);
				    if($isValidD[0] == false){
				    	return $isValidD;
				    }
			    }
		    }

		    //The last if is necessary since the vacation end date has a special condition
		    if($constrain->getColumnName() == "date_end" && $constrain->getDisplayable() == true && $novelty->getNoveltyTypeNoveltyType()->getPayrollCode() != 145){
		    	if($novelty->getDateEnd() == null){
				    $answerArr[0] = false;
				    $answerArr[1] = "No pueden haber campos vacios, completelos antes de continuar";
				    return $answerArr;
			    }
			    else{
				    //Calls dedicated function to evaluate the constrain of Dates
				    $isValidD = $utils->novelty_date_constrain_to_date_validation($constrain->getNoveltyDataConstrain(), $novelty->getDateEnd(),$payRol);
				    if($isValidD[0] == false){
					    return $isValidD;
				    }
			    }
		    }

		    if($constrain->getColumnName() == "amount" && $constrain->getDisplayable() == true){
		    	if($novelty->getAmount() == null){
				    $answerArr[0] = false;
				    $answerArr[1] = "No pueden haber campos vacios, completelos antes de continuar";
				    return $answerArr;
			    }
			    else{
			    	//Calls dedicated function to evaluate the constrain of Amount
				    $isValidA = $utils->novelty_amount_constrain_validation($constrain->getNoveltyDataConstrain(),$novelty->getAmount(),$payRol->getContractContract());

				    if($isValidA[0] == false){
				    	return $isValidA;
				    }
			    }
		    }

		    if($constrain->getColumnName() == "units" && $constrain->getDisplayable() == true){
		    	if($novelty->getUnits() == null){
		    		$answerArr[0] = false;
				    $answerArr[1] = "No pueden haber campos vacios, completelos antes de continuar";
				    return $answerArr;
			    }
			    else{
			    	//Calls dedicated function to evaluate the constrain of Units
				    $isValidU = $utils->novelty_units_constrain_validation($constrain->getNoveltyDataConstrain(),$novelty->getUnits());
				    if($isValidU[0] == false){
				    	return $isValidU;
				    }
			    }
		    }

		    //String just needs to not be empty
		    if($constrain->getColumnName() == "description" && $constrain->getDisplayable() == true){
		    	if($novelty->getDescription() == null){
		    		$answerArr[0] = false;
				    $answerArr[1] = "No pueden haber campos vacios, completelos antes de continuar";
				    return $answerArr;
			    }
		    }
	    }

	    //si es una novedad de vacaciones
	    if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode()==145){
	    	if($novelty->getDateEnd() < $novelty->getDateStart()){
	    		$answerArr[0] = false;
			    $answerArr[1] = "El rango de fechas para las vacaciones no es valido, la fecha de finalización no puede ser menor que la de inicio";
			    return $answerArr;
		    }

		    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getValidVacationDaysContract', array(
		    	"dateStart" => $novelty->getDateStart()->format("Y-m-d"),
			    "dateEnd" => $novelty->getDateEnd()->format("Y-m-d"),
			    "contractId" => $payRol->getContractContract()->getIdContract(),
			    "payrollId" => "-1",
		    ), array('_format' => 'json'));

            $numDaysVacation = json_decode($insertionAnswer->getContent(), true)["days"];
            /** @var array $dateToCheck */
            $dateToCheck = json_decode($insertionAnswer->getContent(), true)["dateToCheck"];
		    if($numDaysVacation > 45){
			    $answerArr[0] = false;
			    $answerArr[1] = "El rango de fechas excede el máximo número de días permitido (45)";
			    return $answerArr;
		    }
		    if($numDaysVacation == 0) {
                $answerArr[0] = false;
                $answerArr[1] = "El rango de fechas no contiene dias de trabajo del empleado";
                return $answerArr;
            }

		    /** @var Contract $contract */
		    $contract = $contractRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")->find($payRol->getContractContract()->getIdContract());
            $holidayDebt = $contract->getHolidayDebt();

            //special case of vacaciones (vacaciones adelantadas)
		    if($holidayDebt < $numDaysVacation) {
		        if(floor($holidayDebt) <= 0) {

                    //change novelty type to vacaciones adelantadas
                    $noveltyTypeVacacionesAdelantadas = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType')->findOneBy(array('simpleName' => 'VACA'));
                    $novelty->setNoveltyTypeNoveltyType($noveltyTypeVacacionesAdelantadas);
                    $novelty->setName($noveltyTypeVacacionesAdelantadas->getName());
                    $payRol->addNovelty($novelty);
                    $em->persist($payRol);
                    $em->flush();

                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getAddNoveltySql', array(
                        "idNovelty" => $novelty->getIdNovelty()), array('_format' => 'json'));

                    if($insertionAnswer->getStatusCode() != 201){
                        $payRol->removeNovelty($novelty);
                        $em->remove($novelty);
                        $em->persist($payRol);
                        $em->flush();

                        $answerArr[0] = false;
                        $answerArr[1] = "No se pudo agregar la novedad, intente más tarde, si el problema persiste, pongase en contacto con nosotros";
                        return $answerArr;
                    }

                    $em->persist($novelty);
                    $contract->setHolidayDebt($holidayDebt - $numDaysVacation);

                    $em->persist($contract);
                    $em->flush();

                    return $answerArr;

                } else {
                    $daysNormalVacaciones = intval(floor($holidayDebt));
                    $daysVacacionesAdelantadas = $numDaysVacation - $daysNormalVacaciones;

                    $lastDayVacaciones = new DateTime($dateToCheck[$daysNormalVacaciones-1]);
                    $firstDayVacacionesAdelantadas = new DateTime($dateToCheck[$daysNormalVacaciones]);
                    $lastDayVacacionesAdelantadas = new DateTime($dateToCheck[count($dateToCheck)-1]);


                    //add $holidayDebt ($daysNormalVacaciones) as vacaiones to SQL
                    $novelty->setName($noveltyType->getName());
                    //change end date and number of days
                    $novelty->setDateEnd($lastDayVacaciones);
                    $novelty->setUnits($daysNormalVacaciones);

                    $payRol->addNovelty($novelty);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($payRol);
                    $em->flush();

                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getAddNoveltySql', array(
                        "idNovelty" => $novelty->getIdNovelty()), array('_format' => 'json'));

                    if($insertionAnswer->getStatusCode() != 201){
                        $payRol->removeNovelty($novelty);
                        $em->remove($novelty);
                        $em->persist($payRol);
                        $em->flush();

                        $answerArr[0] = false;
                        $answerArr[1] = "No se pudo agregar la novedad, intente más tarde, si el problema persiste, pongase en contacto con nosotros";
                        return $answerArr;
                    }

                    $contract->setHolidayDebt($contract->getHolidayDebt() - $daysNormalVacaciones);
                    $em->persist($contract);
                    $em->flush();

                    //add remainding dates as vacaciones adelantadas to SQL
                    $noveltyTypeVacacionesAdelantadas = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType')->findOneBy(array('simpleName' => 'VACA'));
                    $noveltyVacacionesAdelantadas = new Novelty();
                    $noveltyVacacionesAdelantadas->setNoveltyTypeNoveltyType($noveltyTypeVacacionesAdelantadas);
                    $noveltyVacacionesAdelantadas->setUnits($daysVacacionesAdelantadas);

                    $noveltyVacacionesAdelantadas->setDateStart($firstDayVacacionesAdelantadas);
                    $noveltyVacacionesAdelantadas->setDateEnd($lastDayVacacionesAdelantadas);
                    $noveltyVacacionesAdelantadas->setUnits($daysVacacionesAdelantadas);

                    $noveltyVacacionesAdelantadas->setName($noveltyTypeVacacionesAdelantadas->getName());
                    $payRol->addNovelty($noveltyVacacionesAdelantadas);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($payRol);
                    $em->flush();

                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getAddNoveltySql', array(
                        "idNovelty" => $noveltyVacacionesAdelantadas->getIdNovelty()), array('_format' => 'json'));

                    if($insertionAnswer->getStatusCode() != 201){
                        $payRol->removeNovelty($noveltyVacacionesAdelantadas);
                        $em->remove($noveltyVacacionesAdelantadas);
                        $em->persist($payRol);
                        $em->flush();

                        $answerArr[0] = false;
                        $answerArr[1] = "No se pudo agregar la novedad, intente más tarde, si el problema persiste, pongase en contacto con nosotros";
                        return $answerArr;
                    }

                    $contract->setHolidayDebt($contract->getHolidayDebt() - $daysVacacionesAdelantadas);

                    $em->persist($contract);
                    $em->flush();

                    return $answerArr;
                }
            } else {
                //all days will be reported as vacaciones normales
                $novelty->setUnits($numDaysVacation);
            }
	    }

        $novelty->setName($noveltyType->getName());
        $em->persist($novelty);
        $em->flush();

        $payRol->addNovelty($novelty);
        $em = $this->getDoctrine()->getManager();
        $em->persist($payRol);
        $em->flush();

	    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getAddNoveltySql', array(
	    	"idNovelty" => $novelty->getIdNovelty()), array('_format' => 'json'));

	    if($insertionAnswer->getStatusCode() != 201){
	    	$payRol->removeNovelty($novelty);
		    $em->remove($novelty);
		    $em->persist($payRol);
		    $em->flush();

		    $answerArr[0] = false;
		    $answerArr[1] = "No se pudo agregar la novedad, intente más tarde, si el problema persiste, pongase en contacto con nosotros";
		    return $answerArr;
	    }

	    //update holidayDebt if novelty is vacaciones
        if($novelty->getNoveltyTypeNoveltyType()->getPayrollCode()==145) {
            $contract = $contractRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract")
                            ->find($payRol->getContractContract()->getIdContract());
            $contract->setHolidayDebt($contract->getHolidayDebt() - $novelty->getUnits());
            $em->persist($contract);
            $em->flush();
        }
	    //TODO Update when documents are reworked

//	    if(!$this->checkNoveltyFulfilment($novelty)){
//	    	//Crea las notificaciones de los documentos asociados
//		    /** @var User $user */
//		    $user = $this->getUser();
//		    $notification = $this->createNotification(null,1,null,"","Faltan llenar algunos datos de la novedad" . $novelty->getName(),"Novedad Incompleta", "Completar", "alert", $user->getPersonPerson());
//		    $em->persist($notification);
//		    $em->flush();
//		    $notification->setRelatedLink($this->generateUrl("novelty_edit",array('noveltyId' => $novelty->getIdNovelty(), 'notificationReferenced' => $notification->getId())));
//
//		    $em->persist($notification);
//		    $em->flush();
//	    }

	    return $answerArr;
    }
}
