<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractRecord;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;

class ContractRestSecuredController extends FOSRestController
{
	/**
	 * modify contract and its employee info
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "modify contract and its employee info",
	 *   statusCodes = {
	 *     200 = "Created successfully",
	 *     400 = "Bad Request",
	 *   }
	 * )
	 *
	 * @param paramFetcher $paramFetcher ParamFetcher
	 *
	 * @RequestParam(name="contract_id", nullable=false, requirements="([0-9])+", description="Contract that is going to be modified")
	 * @RequestParam(name="last_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee Last name(only one).")
	 * @RequestParam(name="first_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee first name.")
	 * @RequestParam(name="document_type", nullable=true, requirements="([a-z|A-Z| ])+", description="Document type on two char format, if null CC will be used.")
	 * @RequestParam(name="document", nullable=true, requirements="([0-9])+", description="Employee document number")
	 * @RequestParam(name="gender", nullable=true, requirements="(MAS|FEM)", description="Employee gender(MAS or FEM).")
	 * @RequestParam(name="birth_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Employee birth day on the format DD-MM-YYYY.")
	 * @RequestParam(name="start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
	 * @RequestParam(name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Last work contract termination day(format: DD-MM-YYYY).")
	 * @RequestParam(name="worked_hours_days", nullable=true, requirements="([0-9])+", description="Number of hours worked on a day.")
	 * @RequestParam(name="liquidation_type", nullable=true, requirements="(J|M|Q)", strict=false, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
	 * @RequestParam(name="contract_type", nullable=true, requirements="([0-9])", strict=false, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
	 * @RequestParam(name="transport_aux", nullable=true, requirements="(S|N)", strict=false, description="Weather or not it needs transportation help, if empty it uses the law.")
	 * @RequestParam(name="payroll_type", nullable=true, requirements="4|6|1", strict=true, description="Payroll type, 4 for full time 6 part time 1 regular payroll.")
	 *
	 * @return View
	 */
	public function putModifyContractAction(ParamFetcher $paramFetcher)
	{
		$idContract = $paramFetcher->get('contract_id');
		
		/** @var Contract $currContract */
		$currContract = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Contract')->find($idContract);
		/** @var Person $employeePerson */
		$employeePerson = $currContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
		
		$lastName = $paramFetcher->get('last_name');
		$firstName = $paramFetcher->get('first_name');
		$documentType = $paramFetcher->get('document_type');
		$document = $paramFetcher->get('document');
		$gender = $paramFetcher->get('gender');
		$birthDate = $paramFetcher->get('birth_date');
		$startDate = $paramFetcher->get('start_date');
		$lastContractEndDate = $paramFetcher->get('last_contract_end_date');
		$workedHoursDays = $paramFetcher->get('worked_hours_days');
		$liquidationType = $paramFetcher->get('liquidation_type');
		$contractType = $paramFetcher->get('contract_type');
		$transportAux = $paramFetcher->get('transport_aux');
		$payrollType = $paramFetcher->get('payroll_type');
		$contractNumber = null;
		$paymentMethod = null;
		$society = null;
		$lastContractStartDate = null;
		
		$em = $this->getDoctrine()->getManager();
		
		$data = array(
			"employee_id" => $currContract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee(),
			"employee_id" => $currContract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee()
		);
		
		$ContractHasChanged = false;
		$employeeHasChanged = false;
		if($lastName) {
			$employeePerson->setLastName1($lastName);
			$employeeHasChanged = true;
			$data["last_name"] = $lastName;
		}
		if($firstName) {
			$employeePerson->setNames($firstName);
			$employeeHasChanged = true;
			$data["first_name"] = $firstName;
		}
		if($documentType) {
			$employeePerson->setDocumentType($documentType);
			$employeeHasChanged = true;
			$data["document_type"] = $documentType;
		}
		if($document) {
			$employeePerson->setDocument($document);
			$employeeHasChanged = true;
			$data["document"] = $document;
		}
		if($gender) {
			$employeePerson->setGender($gender);
			$employeeHasChanged = true;
			$data["gender"] = $gender;
		}
		if($birthDate) {
			$employeePerson->setBirthDate(new \Date($birthDate));
			$employeeHasChanged = true;
			$data["birth_date"] = $birthDate;
		}
		
		if($employeeHasChanged) {
			$em->persist($employeePerson);
		}
		
		if($startDate) {
			//TODO(a_santamaria) mandar cuando se revise que no daña nada en sql
//			$lastContractStartDate = $currContract->getStartDate();
			$currContract->setStartDate(new \Date($startDate));
			$ContractHasChanged = true;
			$data["start_date"] = $startDate;
		}
		if($lastContractEndDate) {
			$currContract->setEndDate(new \Date($lastContractEndDate));
			$ContractHasChanged = true;
			$data["last_contract_start_date"] = $lastContractEndDate;
		}
		//TODO  cuando se guarde este dato en la base de datos
//		if($workedHoursDays) {
//
//			$ContractHasChanged = true;
//		}
		if($liquidationType) {
			$frequency = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Frequency')
				->findOneBy(array('payroll_code' => $liquidationType));
			$currContract->setFrequencyFrequency($frequency);
			$ContractHasChanged = true;
			$data["liquidation_type"] = $liquidationType;
		}
		if($contractType) {
			$newContractType = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:ContractType')
				->findOneBy(array('payroll_code' => $contractType));
			$currContract->setContractTypeContractType($newContractType);
			$ContractHasChanged = true;
			$data["contract_type"] = $contractType;
		}
		if($transportAux) {
			$transportAid = $transportAux == 'S' ? 1 : 0;
			$currContract->setTransportAid($transportAid);
			$ContractHasChanged = true;
			$data["transport_aux"] = $transportAux;
		}
		if($payrollType) {
			$timeCommitment = null;
			if($payrollType == 4) {
				$timeCommitment = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:TimeCommitment')
					->findOneBy(array('code' => 'TC'));
			} elseif($payrollType == 6) {
				$timeCommitment = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:TimeCommitment')
					->findOneBy(array('code' => 'XD'));
			}
			$currContract->setTimeCommitmentTimeCommitment($timeCommitment);
			$ContractHasChanged = true;
			$data["payroll_type"] = $payrollType;
		}
		
		if($ContractHasChanged) {
			$em->persist($currContract);
		}
		
		$request = $this->container->get('request');
		$request->setMethod("POST");
		
		$request->request->add($data);
		
		/** @var View $result */
		$result = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyEmployee', array('_format' => 'json'));

		if($result->getStatusCode() != 200) {
			return $result;
		}
		
		$contractRecord = new ContractRecord();
		
		$contractRecord->setFrequencyFrequency($currContract->getFrequencyFrequency());
		$contractRecord->setContractTypeContractType($currContract->getContractTypeContractType());
		$contractRecord->setWorkplaceWorkplace($currContract->getWorkplaceWorkplace());
		$contractRecord->setSisben($currContract->getSisben());
		$contractRecord->setTransportAid($currContract->getTransportAid());
		$contractRecord->setSalary($currContract->getSalary());
		$contractRecord->setHolidayDebt($currContract->getHolidayDebt());
		$contractRecord->setEmployeeContractTypeEmployeeContractType($currContract->getEmployeeContractTypeEmployeeContractType());
		$contractRecord->setTimeCommitmentTimeCommitment($currContract->getTimeCommitmentTimeCommitment());
		$contractRecord->setPositionPosition($currContract->getPositionPosition());
		$contractRecord->setPayMethodPayMethod($currContract->getPayMethodPayMethod());
		$contractRecord->setStartDate($currContract->getStartDate());
		$contractRecord->setEndDate($currContract->getEndDate());
		$contractRecord->setTestPeriod($currContract->getTestPeriod());
		$contractRecord->setPlanillaTypePlanillaType($currContract->getPlanillaTypePlanillaType());
		$contractRecord->setWorkableDaysMonth($currContract->getWorkableDaysMonth());
		/** @var WeekWorkableDays $weekWorkableDay */
		foreach ($currContract->getWeekWorkableDays() as $weekWorkableDay) {
			$weekWorkableDayRecord = new WeekWorkableDaysRecord();
			$weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
			$weekWorkableDayRecord->setDayName($weekWorkableDay->getDayName());
			$weekWorkableDayRecord->setDayNumber($weekWorkableDay->getDayNumber());
			$contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
		}
		
		$documentStatus = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:DocumentStatusType')
										->findOneBy(array("documentStatusCode" => 'AMDCPE'));
		$contractRecord->setDocumentStatus($documentStatus);
		$now = new \DateTime();
		$contractRecord->setDateChangesApplied($now);
		$contractRecord->setToBeExecuted(0);
		
		$contractRecord->setContractContract($currContract);
		$em->persist($contractRecord);
		
		$em->flush();

		return $result;
	}
}