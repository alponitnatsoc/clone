<?php

namespace RocketSeller\TwoPickBundle\Traits;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\PilaDetail;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;

trait PayrollMethodsTrait
{

    use NoveltyTypeMethodsTrait,
        LiquidationMethodsTrait;

    /**
     * Obtener informacion de nomina
     *
     * @param Employer $employer
     * @param bool $payrollToPay
     * @return ArrayCollection
     */
    public function getInfoPayroll(Employer $employer, $payrollToPay = false)
    {
        /* @var $employerHasEmployees \Doctrine\Common\Collections\Collection */
        $employerHasEmployees = $employer->getEmployerHasEmployees();
        $em = $this->getDoctrine()->getManager();
        $pods = new ArrayCollection();
        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployee = $employerHasEmployees->first();
        $podsPila= array();
        //$podsPila['n'];this is "n" for normal planilla
        //the idea is to create the bucnh of pods pila that are needed, and in the caculation apply the logic

        do {
            $tempPod = $this->getInfoEmployee($employerHasEmployee, $podsPila);
            if ($tempPod != null) {
                $pods->add($tempPod);
            }
        } while ($employerHasEmployee = $employerHasEmployees->next());
        if (count($pods)>0) {
            //do this foreach  podspila
            /** @var PurchaseOrdersDescription $value */
            // $key would dbe the type of planilla that is set
            foreach ($podsPila as $key => $value ) {
                //calculate the mora and add the date to pay
                if ($value->getValue() > 0) {
                    if($value->getPurchaseOrders()!=null&&$value->getPurchaseOrders()->getPurchaseOrdersStatus()!=null&&$value->getPurchaseOrders()->getPurchaseOrdersStatus()->getIdNovoPay()=="P1")
                        continue;
                    if ($value->getPurchaseOrdersStatus()!=null&&($value->getPurchaseOrdersStatus()->getIdNovoPay() == "-1" || $value->getPurchaseOrdersStatus()->getIdNovoPay() == "S2" || $value->getPurchaseOrdersStatus()->getIdNovoPay() == "00")){

                    }else {
                        /** @var UtilsController $utils */
                        $utils = $this->get('app.symplifica_utils');
                        $productPILA = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array('simpleName' => 'PP'));
                        $value->setProductProduct($productPILA);
                        $dateToday = new \DateTime();
                        $value->setDescription("Pago de Aportes a Seguridad Social mes " . $utils->month_number_to_name($dateToday->format("m")));
                        $entity = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
                        $pos = $entity->findOneBy(array('idNovoPay' => 'P1')); // Estado pendiente por pago
                        $value->setPurchaseOrdersStatus($pos);
                        //seeking fot the wished date
                        $todayPlus = new DateTime();
                        $todayPlus->modify('+1 day');
                        $request = $this->container->get('request');
                        $request->setMethod("GET");
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysToDate',array('dateStart'=>$todayPlus->format("Y-m-d"),'days'=>3), array('_format' => 'json'));
                        if ($insertionAnswer->getStatusCode() != 200) {
                            return false;
                        }
                        $permittedDate=new DateTime(json_decode($insertionAnswer->getContent(),true)['date']);
                        $value->setDateToPay($permittedDate);
                        $pods->add($value);
                    }
                }
            }
            foreach ($pods as $pod) {
                $em->persist($pod);
            }
            $em->flush();
            return $pods;
        }
        return null;
    }

    /**
     * Funcion que verifica si el payroll enviado es del periodo actual
     * @param Payroll $payroll
     * @return bool
     */
    private function checkActivePayroll(Payroll $payroll)
    {
        $dateToday = new \DateTime();
        if($payroll->getContractContract()->getFrequencyFrequency()->getPayrollCode()=="M"){
            $todayPeriod=4;
            $dayToPay=25;
        }else{
            $todayPeriod = $dateToday->format("d") >= 16 ? 4 : 2;
            $dayToPay= $dateToday->format("d") >= 16 ? 25 : 12;
        }
        if ($dateToday->format("Y") == $payroll->getYear() && $dateToday->format("m") == $payroll->getMonth() && $payroll->getPeriod() == $todayPeriod) {
            $ambiente = $this->container->getParameter('ambiente');
            if($ambiente=="produccion"){
                $request = $this->container->get('request');
                $request->setMethod("GET");
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$dateToday->format("Y-m-d"),'dateEnd'=>$dateToday->format("Y-m-").$dayToPay), array('_format' => 'json'));
                if ($insertionAnswer->getStatusCode() != 200) {
                    return true;
                }
                $insertionAnswer = json_decode($insertionAnswer->getContent(), true);
                if($insertionAnswer["days"]>5){
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    /**
     *
     * @param EmployerHasEmployee $employerHasEmployee
     * @param array $podsPila
     * @return null|PurchaseOrdersDescription
     */
    private function getInfoEmployee(EmployerHasEmployee $employerHasEmployee, array &$podsPila)
    {
        if ($employerHasEmployee->getState() >= 4) {
            $contracts = $employerHasEmployee->getContracts();
            /* @var $contract Contract */
            foreach ($contracts as $contract) {
                if ($contract->getState() > 0) {
                    /** @var UtilsController $utils */
                    $utils = $this->get('app.symplifica_utils');
                    /* @var Payroll $payroll */
                    $payroll = $contract->getActivePayroll();
                    if (!$this->checkActivePayroll($payroll))
                        break;
                    if(count($payroll->getPurchaseOrdersDescription())>0){
                        /** @var PurchaseOrdersDescription $tempPOD */
                        $tempPOD=$payroll->getPurchaseOrdersDescription()->get(0);
                        //id de pago realizadoo o id de procesando no se muestra
                        if($tempPOD->getPurchaseOrders()!=null&&$tempPOD->getPurchaseOrders()->getPurchaseOrdersStatus()->getIdNovoPay()=="P1")
                            break;
                        if($tempPOD->getPurchaseOrdersStatus()!=null&&($tempPOD->getPurchaseOrdersStatus()->getIdNovoPay()=="-1"||$tempPOD->getPurchaseOrdersStatus()->getIdNovoPay()=="S2"||$tempPOD->getPurchaseOrdersStatus()->getIdNovoPay()=="00"))
                            break;
                    }else{
                        $tempPOD = new PurchaseOrdersDescription();
                    }
                    $detailNomina = $this->getInfoNominaSQL($employerHasEmployee);
                    $totalLiquidation = $this->totalLiquidation($detailNomina);
                    if($totalLiquidation==0)
                        break;
	                
	                  $currentNovelties = $payroll->getNovelties();
	                  $vacacionesAPorUnidades = [];
	                  /** @var Novelty $currentNovelty */
	                  foreach ($currentNovelties as $currentNovelty) {
	                  	if($currentNovelty->getNoveltyTypeNoveltyType()->getSimpleName() == 'VACA') {
	                  		$realDays = intVal($currentNovelty->getDateEnd()->diff($currentNovelty->getDateStart())->format("%d")) + 1;
	                  		if(array_key_exists($realDays, $vacacionesAPorUnidades))
			                    $vacacionesAPorUnidades[$realDays]++;
			                  else
				                  $vacacionesAPorUnidades[$realDays] = 1;
		                  }
	                  }
	                  $noveltyTypeVacacionesAdelantadas = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NoveltyType")->findOneBy(array('simpleName' => 'VACA'));
                    //checking if any new stuff was added to this payroll
                    /** @var ArrayCollection $novelties */
                    $novelties=$totalLiquidation["novelties"];
                    $sqlNovelties=$payroll->getSqlNovelties();
                    /** @var Novelty $nowNovelty */
                    for($z=0;$z<$novelties->count();$z++){
                        if($sqlNovelties->count()<=$z){
                            //add and end
                            $sqlNovelties->add($novelties->get($z));
                        }else{
                            /** @var Novelty $actNovel */
                            $actNovel=$sqlNovelties->get($z);
                            $actNovel->setSqlValue($novelties->get($z)->getSqlValue());
	                          $actNovel->setNoveltyTypeNoveltyType($novelties->get($z)->getNoveltyTypeNoveltyType());
                            $actNovel->setName($actNovel->getNoveltyTypeNoveltyType()->getName());
                            $actNovel->setSqlNovConsec($novelties->get($z)->getSqlnovConsec());
                            $actNovel->setUnits($novelties->get($z)->getUnits());
														
	                          //if licencia remunerada corresponds to vacaciones adelantadas change type
		                        if($novelties->get($z)->getNoveltyTypeNoveltyType()->getSimpleName() == 'LR') {
			                        $keyUnits = $novelties->get($z)->getUnits();
			                        if(array_key_exists($keyUnits, $vacacionesAPorUnidades) && $vacacionesAPorUnidades[$keyUnits] > 0) {
				                        $actNovel->setNoveltyTypeNoveltyType($noveltyTypeVacacionesAdelantadas);
				                        $actNovel->setName($noveltyTypeVacacionesAdelantadas->getName());
				                        $vacacionesAPorUnidades[$keyUnits]--;
			                        }
		                        }
                        }
                    }
                    $em=$this->getDoctrine()->getManager();
                    /** @var Novelty $sqlNovelty */
                    foreach ($sqlNovelties as $sqlNovelty) {
                        $sqlNovelty->setSqlPayrollPayroll($payroll);
                        $em->persist($sqlNovelty);
                    }
                    $em->flush();


                    $productNomina = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array('simpleName' => 'PN'));
                    $tempPOD->setPayrollPayroll($payroll);
                    $tempPOD->setProductProduct($productNomina);
                    $person = $employerHasEmployee->getEmployeeEmployee()->getPersonPerson();
                    $tempPOD->setDescription("Pago Nómina " .$utils->mb_capitalize(explode(" ", $person->getNames())[0]) ." ". $utils->mb_capitalize($person->getLastName1())." ". $utils->period_number_to_name($payroll->getPeriod()). " " . $utils->month_number_to_name( $payroll->getMonth()) );
                    $tempPOD->setValue($totalLiquidation["total"]);

                    if ($payroll->getPeriod() == 4) {
                        $pila=$payroll->getPila();
                        //do the logic for each planilla here
                        $planillaCode=$payroll->getContractContract()->getPlanillaTypePlanillaType()->getCode();
                        $totalPila=$this->getTotalPILA($employerHasEmployee, $payroll );
                        //this is for the first case and when the pila is already set in the pod
                        if(!($pila!=null&&$pila->getPurchaseOrders()!=null&&
                            $pila->getPurchaseOrders()->getPurchaseOrdersStatus()!=null&&
                            $pila->getPurchaseOrders()->getPurchaseOrdersStatus()->getIdNovoPay()=="P1")){
                            if($pila!=null&&(!isset($podsPila[$planillaCode]))){
                                $podsPila[$planillaCode]=$pila;
                                $podsPila[$planillaCode]->setValue($totalPila['total']);

                            }else{
                                if($pila!=null&&isset($podsPila[$planillaCode])){
                                    $podsPila[$planillaCode]->setValue($totalPila['total'] + $podsPila[$planillaCode]->getValue());
                                }
                                //this is for the literal first case of the currend pod type
                                elseif((!isset($podsPila[$planillaCode]))){
                                    $podsPila[$planillaCode] = new PurchaseOrdersDescription();
                                    $podsPila[$planillaCode]->addPayrollsPila($payroll);
                                    $podsPila[$planillaCode]->setValue($totalPila['total']);
                                }
                                elseif($pila==null){
                                    $podsPila[$planillaCode]->addPayrollsPila($payroll);
                                    $podsPila[$planillaCode]->setValue($totalPila['total'] + $podsPila[$planillaCode]->getValue());
                                }
                            }
                        }
                    }
                    return $tempPOD;
                }
            }
        }
        return null;
    }

    /**
     *
     * @param int $idUser id del usuario a buscar
     * @return User|null
     */
    private function getUserById($idUser)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
            array('id' => $idUser)
        );
    }

    /**
     *
     * @param int $idEmployer id del usuario a buscar
     * @return User|null
     */
    private function getEmployerById($idEmployer)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\Employer')->findOneBy(
            array('idEmployer' => $idEmployer)
        );
    }

    private function getSalary($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '1') {
                    return (int)ceil($value['NOMI_VALOR_LOCAL']);
                }
            }
        }
        return false;
    }

    private function getTotalAportes($dataNomina)
    {
        if ($dataNomina && !empty($dataNomina)) {
            $aporteSalud = $aportePension = 0;
            foreach ($dataNomina as $key => $value) {
                if (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3010') {
                    $aporteSalud = $value['NOMI_VALOR_LOCAL'];
                } elseif (isset($value["CON_CODIGO"]) && $value["CON_CODIGO"] == '3020') {
                    $aportePension = $value["NOMI_VALOR_LOCAL"];
                }
            }
            return array(
                'total' => ceil($aporteSalud + $aportePension),
                'salud' => ceil($aporteSalud),
                'pension' => ceil($aportePension)
            );
        }
        return false;
    }

    private function getTotalPILA($employerHasEmployee, Payroll $payroll=null)
    {
        $total = $pensionCia = $pensionEmp = $saludCia = $saludEmp = $arlCia = $arlEmp = $parafCia = $parafEmp = 0;
        /** @var ObjectRepository $entityRepo */
        $entityRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Entity");
        $pension=$salud=$arl=$parafiscal=null;
        if ($employerHasEmployee) {
            $pila = $this->getInfoPilaSQL($employerHasEmployee);
            if ($pila) {
                foreach ($pila as $key => $value) {
                    $total += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                    $total += isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    if ($value['TENT_CODIGO'] == 'AFP') {
                        $valueCia=$valueEmp=0;
                        if($pension==null){
                            $pension = new PilaDetail();
                        }
                        else{
		                        $valueCia = $pension->getSqlValueCia();
		                        $valueEmp = $pension->getSqlValueEmp();
                        }
                        $entity=$entityRepo->findOneBy(array("payroll_code"=>$value["ENT_CODIGO"]));
                        $pension->setEntityEntity($entity);
                        $pension->setPayrollPayroll($payroll);
                        $pension->setSqlValueCia(isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] + $valueCia: 0);
                        $pension->setSqlValueEmp(isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] + $valueEmp: 0);
                        $pensionCia += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $pensionEmp += isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'ARP') {
                        $valueCia=$valueEmp=0;
                        if($arl==null){
                            $arl = new PilaDetail();
                        }
                        else{
		                        $valueCia = $arl->getSqlValueCia();
		                        $valueEmp = $arl->getSqlValueEmp();
                        }
                        $entity=$entityRepo->findOneBy(array("payroll_code"=>$value["ENT_CODIGO"]));
                        $arl->setEntityEntity($entity);
                        $arl->setPayrollPayroll($payroll);
                        $arl->setSqlValueCia(isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] + $valueCia: 0);
                        $arl->setSqlValueEmp(isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] + $valueEmp: 0);
                        $arlEmp += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $arlCia += isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'EPS' || $value['TENT_CODIGO'] == 'ARS') {
                        $valueCia=$valueEmp=0;
                        if($salud==null){
                            $salud = new PilaDetail();
                        }
                        else{
		                        $valueCia = $salud->getSqlValueCia();
		                        $valueEmp = $salud->getSqlValueEmp();
                        }
                        $entity=$entityRepo->findOneBy(array("payroll_code"=>$value["ENT_CODIGO"]));
                        $salud->setEntityEntity($entity);
                        $salud->setPayrollPayroll($payroll);
                        $salud->setSqlValueCia(isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] + $valueCia: 0);
                        $salud->setSqlValueEmp(isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] + $valueEmp: 0);
                        $saludEmp += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $saludCia += isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    } elseif ($value['TENT_CODIGO'] == 'PARAFISCAL') {
                        $valueCia=$valueEmp=0;
                        if($parafiscal==null){
                            $parafiscal = new PilaDetail();
                        }
                        else{
		                        $valueCia = $parafiscal->getSqlValueCia();
		                        $valueEmp = $parafiscal->getSqlValueEmp();
                        }
                        $entity=$entityRepo->findOneBy(array("payroll_code"=>$value["ENT_CODIGO"]));
                        $parafiscal->setEntityEntity($entity);
                        $parafiscal->setPayrollPayroll($payroll);
                        $parafiscal->setSqlValueCia(isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] + $valueCia: 0);
                        $parafiscal->setSqlValueEmp(isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] + $valueEmp: 0);
                        $parafEmp += isset($value['APR_APORTE_EMP']) ? $value['APR_APORTE_EMP'] : 0;
                        $parafCia += isset($value['APR_APORTE_CIA']) ? $value['APR_APORTE_CIA'] : 0;
                    }
                }
                if($payroll!=null){
                    $details=$payroll->getPilaDetails();
                    /** @var ObjectManager $em */
                    $em = $this->getDoctrine()->getManager();
                    if($details->count()!=0){
                        /** @var PilaDetail $pilaDetail */
                        foreach ($details as $pilaDetail) {
                            if($pilaDetail->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=="EPS"||
                                $pilaDetail->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=="ARS"){
                                $pilaDetail->setEntityEntity($salud->getEntityEntity());
                                $pilaDetail->setPayrollPayroll($salud->getPayrollPayroll());
                                $pilaDetail->setSqlValueCia($salud->getSqlValueCia());
                                $pilaDetail->setSqlValueEmp($salud->getSqlValueEmp());
                                $em->persist($pilaDetail);
                            }elseif ($pilaDetail->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=="ARP"){
                                $pilaDetail->setEntityEntity($arl->getEntityEntity());
                                $pilaDetail->setPayrollPayroll($arl->getPayrollPayroll());
                                $pilaDetail->setSqlValueCia($arl->getSqlValueCia());
                                $pilaDetail->setSqlValueEmp($arl->getSqlValueEmp());
                                $em->persist($pilaDetail);
                            }elseif ($pilaDetail->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=="AFP"){
                                $pilaDetail->setEntityEntity($pension->getEntityEntity());
                                $pilaDetail->setPayrollPayroll($pension->getPayrollPayroll());
                                $pilaDetail->setSqlValueCia($pension->getSqlValueCia());
                                $pilaDetail->setSqlValueEmp($pension->getSqlValueEmp());
                                $em->persist($pilaDetail);
                            }elseif($pilaDetail->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=="PARAFISCAL"){
                                $pilaDetail->setEntityEntity($parafiscal->getEntityEntity());
                                $pilaDetail->setPayrollPayroll($parafiscal->getPayrollPayroll());
                                $pilaDetail->setSqlValueCia($parafiscal->getSqlValueCia());
                                $pilaDetail->setSqlValueEmp($parafiscal->getSqlValueEmp());
                                $em->persist($pilaDetail);
                            }
                        }
                    }else{
                        if($pension!=null)
                            $em->persist($pension);
                        if($salud!=null)
                            $em->persist($salud);
                        if($arl!=null)
                            $em->persist($arl);
                        if($parafiscal!=null)
                            $em->persist($parafiscal);
                    }
                    $em->flush();
                }
            }
        }

        return array(
            'total' => (int)ceil($total),
            'pension' => array(
                'cia' => (int) ceil($pensionCia),
                'emp' => (int) ceil($pensionEmp)),
            'salud' => array(
                'cia' => (int) ceil($saludCia),
                'emp' => (int) ceil($saludEmp)),
            'arl' => array(
                'cia' => (int) ceil($arlCia),
                'emp' => (int) ceil($arlEmp)),
            'parafiscales' => array(
                'cia' => (int) ceil($parafCia),
                'emp' => (int) ceil($parafEmp))
        );
    }

    /**
     * Trae la informacion del empleado desde el ws de nomina de SQL
     *
     * @param EmployerHasEmployee $employerHasEmployee
     * @return type
     */
    private function getInfoNominaSQL(EmployerHasEmployee $employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getIdEmployerHasEmployee();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollMethodRest:getGeneralPayrolls', array(
            'employeeId' => $employeeId,
            'period' => null,
            'month' => null,
            'year' => null
        ), array('_format' => 'json')
        );

        return json_decode($generalPayroll->getContent(), true);
    }

    /**
     * Trae la informacion del empleado desde el ws de nomina de SQL
     *
     * @param EmployerHasEmployee $employerHasEmployee
     * @return type
     */
    private function getInfoPilaSQL(EmployerHasEmployee $employerHasEmployee)
    {
        $employeeId = $employerHasEmployee->getIdEmployerHasEmployee();

        $generalPayroll = $this->forward('RocketSellerTwoPickBundle:PayrollRest:getExternalEntitiesLiquidation', array(
            'employeeId' => $employeeId
        ), array('_format' => 'json')
        );

        return json_decode($generalPayroll->getContent(), true);
    }
    private function caculateSymplificaFee($user){

    }

}
