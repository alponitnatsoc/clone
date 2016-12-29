<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\CalculatorConstraints;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\ContractRecord;
use RocketSeller\TwoPickBundle\Entity\ContractType;
use RocketSeller\TwoPickBundle\Entity\Frequency;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\TimeCommitment;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Workplace;

class ContractRestSecuredController extends FOSRestController
{
    /**
     * create contract record for the contract and can be executed in demand or in a future date
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "create contract record for the contract and can be executed in demand or in a future date",
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
     * @RequestParam(name="end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Last work contract termination day(format: DD-MM-YYYY).")
     * @RequestParam(name="liquidation_type", nullable=true, requirements="(J|M|Q)", strict=false, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
     * @RequestParam(name="contract_type", nullable=true, requirements="([0-9])", strict=false, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
     * @RequestParam(name="transport_aid", nullable=true, requirements="([0-9])", strict=false, description="Employee lives in the house or not")
     * @RequestParam(name="sisben", nullable=true, requirements="([0-9])", strict=false, description="Employee has sisben")
     * @RequestParam(name="payroll_type", nullable=true, requirements="4|6|1", strict=true, description="Payroll type, 4 for full time 6 part time 1 regular payroll.")
     * @RequestParam(name="salary", nullable=true, requirements="([0-9])+(.[0-9]+)?", strict=true, description="salary, must be greater than the minimum salary")
     * @RequestParam(name="date_to_execute", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date where changes must be executed in contract(format: DD-MM-YYYY).")
     * @RequestParam(name="workable_days_month", nullable=true, requirements="([0-9])+", description="workable days of the month count")
     * @RequestParam(name="workplace_id", nullable=true, requirements="([0-9])+", description="new workplace for the contract")
     * @return View
     */
    public function putCreateContractRecordAction(ParamFetcher $paramFetcher)
    {
        $dateToExecute = $paramFetcher->get('date_to_execute');//getting the date where changes must be aplied
        $em = $this->getDoctrine()->getManager();//getting the entityManager
        $today = new DateTime();//getting todays date
        $contractRecord = new ContractRecord();//creating a new contract record
        /** @var Contract $actualContract */
        $actualContract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($paramFetcher->get("contract_id"));//getting the actual contract
        /** @var User $user */
        if($this->getUser()){//setting the user to the session user or backoffice
            $user = $this->getUser();
        }else{
            $user = $em->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array(
                "personPerson"=>$em->getRepository("RocketSellerTwoPickBundle:Person")->findOneBy(array("names"=>"Back","lastName1"=>"Office"))));
        }
        /** @var Person $employeePerson */
        $employeePerson = $actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();//getting the employee person
        $employeeHasChanged = false;//flag for employePerson changes
        $data = array(//setting initial data for SQL
            "employee_id" => $actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee(),
            "contract_number" => $actualContract->getIdContract(),
            "society"=>$actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdSqlSociety(),
        );
        if($paramFetcher->get("first_name")){//if firstName in paramfetcher
            $log = new Log($user,"Person","names",$employeePerson->getIdPerson(),$employeePerson->getNames(),
                $paramFetcher->get("first_name"),"Se modificó el Nombre desde el rest de modificar contrato");
            $employeePerson->setNames($paramFetcher->get("first_name"));
            $em->persist($log);
            $em->persist($employeePerson);
            $data["first_name"] = $paramFetcher->get('first_name');
            $employeeHasChanged = true;
        }
        if($paramFetcher->get("last_name")){//if lastName in paramfetcher
            $log = new Log($user,"Person","LastName1",$employeePerson->getIdPerson(),$employeePerson->getLastName1(),
                $paramFetcher->get("last_name"),"Se modificó el Apellido desde el rest de modificar contrato");
            $employeePerson->setLastName1($paramFetcher->get("last_name"));
            $em->persist($log);
            $em->persist($employeePerson);
            $data["last_name"] = $paramFetcher->get('last_name');
            $employeeHasChanged = true;
        }
        if($paramFetcher->get("document_type")){//if docType in paramfetcher
            $log = new Log($user,"Person","document_type",$employeePerson->getIdPerson(),$employeePerson->getDocumentType(),
                $paramFetcher->get("document_type"),"Se modificó el Tipo de Documento desde el rest de modificar contrato");
            $employeePerson->setDocumentType($paramFetcher->get("document_type"));
            $em->persist($log);
            $em->persist($employeePerson);
            if($paramFetcher->get("document_type")=="PASAPORTE"){
                $data["document_type"] = "PA";
            }else{
                $data["document_type"] = $paramFetcher->get("document_type");
            }
            $employeeHasChanged = true;
        }
        if($paramFetcher->get("document")){//if document in paramfetcher
            $log = new Log($user,"Person","document",$employeePerson->getIdPerson(),$employeePerson->getDocument(),
                $paramFetcher->get("document"),"Se modificó el Numero de documento desde el rest de modificar contrato");
            $employeePerson->setDocument($paramFetcher->get("document"));
            $em->persist($log);
            $em->persist($employeePerson);
            $data["document"] = $paramFetcher->get('document');
            $employeeHasChanged = true;
        }
        if($paramFetcher->get("gender")){//if gender in paramfetcher
            $log = new Log($user,"Person","gender",$employeePerson->getIdPerson(),$employeePerson->getGender(),
                $paramFetcher->get("gender"),"Se modificó el Genero desde el rest de modificar contrato");
            $employeePerson->setGender($paramFetcher->get("gender"));
            $em->persist($log);
            $em->persist($employeePerson);
            $data["gender"] = $paramFetcher->get('gender');
            $employeeHasChanged = true;
        }
        if($paramFetcher->get("birth_date")){//if birthDate in paramfetcher
            $log = new Log($user,"Person","birth_date",$employeePerson->getIdPerson(),$employeePerson->getBirthDate()->format("d-m-Y"),
                $paramFetcher->get("birth_date"),"Se modificó la fecha de nacimiento desde el rest de modificar contrato");
            $employeePerson->setBirthDate(new DateTime($paramFetcher->get("birth_date")));
            $em->persist($log);
            $em->persist($employeePerson);
            $data["birth_date"] = $paramFetcher->get('birth_date');
            $employeeHasChanged = true;
        }
        if($employeeHasChanged){
            $request = $this->container->get('request');
            $request->setMethod("POST");
            $request->request->add($data);
            /** @var View $result */
            $result = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyEmployee', array('_format' => 'json'));
            if($result->getStatusCode() != 200) {
                return $result;
            }else{
                $response["SQLChanges"]="OK";
                $em->flush();
            }
        }
        if(!$dateToExecute){
            $dateToExecute = $today;
        }else{
            $dateToExecute = new DateTime($dateToExecute);
            if($dateToExecute<$today){
                $dateToExecute=$today;
            }
        }
        $response["data"]=$data;
        $ContractHasChanged = false;
        if($paramFetcher->get("start_date")){//if startDate in paramfetcher
            $newStartDate = new DateTime($paramFetcher->get("start_date"));
            $contractRecord->setStartDate($newStartDate);
            if($actualContract->getStartDate()->format("d-m-Y") != $newStartDate->format("d-m-Y"))
                $ContractHasChanged = true;
        }else{
            $contractRecord->setStartDate($actualContract->getStartDate());
        }
        if($paramFetcher->get("end_date")){//if endDate in paramfetcher
            $newEndDate = new DateTime($paramFetcher->get("end_date"));
            $contractRecord->setEndDate($newEndDate);
            if($actualContract->getEndDate()){
                if($actualContract->getEndDate()->format("d-m-Y") != $newEndDate->format("d-m-Y"))
                    $ContractHasChanged = true;
            }else{
                $ContractHasChanged = true;
            }
        }else{
            if($actualContract->getEndDate()) $contractRecord->setEndDate($actualContract->getEndDate());
        }
        if($paramFetcher->get("liquidation_type")){//if liquidationType in paramfetcher
            $newEndDate = new DateTime($paramFetcher->get("liquidation_type"));
            /** @var Frequency $frequency */
            $frequency = $em->getRepository("RocketSellerTwoPickBundle:Frequency")->findOneBy(array('payroll_code'=>$paramFetcher->get("liquidation_type")));
            $contractRecord->setFrequencyFrequency($frequency);
            if($actualContract->getFrequencyFrequency() != $frequency)
                $ContractHasChanged = true;
        }else{
            $contractRecord->setFrequencyFrequency($actualContract->getFrequencyFrequency());
        }
        if($paramFetcher->get("contract_type")){//if contractType in paramfetcher
            /** @var ContractType $contractType */
            $contractType = $em->getRepository("RocketSellerTwoPickBundle:ContractType")->findOneBy(array("payroll_code"=>$paramFetcher->get("contract_type")));
            $contractRecord->setContractTypeContractType($contractType);
            if($actualContract->getContractTypeContractType() != $contractType)
                $ContractHasChanged = true;
        }else{
            $contractRecord->setContractTypeContractType($actualContract->getContractTypeContractType());
        }
        if($paramFetcher->get("transport_aid")){//if transportAid in paramfetcher
            $contractRecord->setTransportAid($paramFetcher->get("transport_aid"));
            if($actualContract->getTransportAid() != $paramFetcher->get("transport_aid"))
                $ContractHasChanged = true;
        }else{
            if($actualContract->getTransportAid())
                $contractRecord->setTransportAid($actualContract->getTransportAid());

        }
        if($paramFetcher->get("payroll_type")){//if payroll_type in paramfetcher
            $timeCommitment = null;
            if($paramFetcher->get("payroll_type") == 4){
                /** @var TimeCommitment $timeCommitment */
                $timeCommitment = $em->getRepository("RocketSellerTwoPickBundle:TimeCommitment")->findOneBy(array("code"=>'TC'));
            }elseif($paramFetcher->get("payroll_type") == 6){
                /** @var TimeCommitment $timeCommitment */
                $timeCommitment = $em->getRepository("RocketSellerTwoPickBundle:TimeCommitment")->findOneBy(array("code"=>'XD'));
            }
            $contractRecord->setTimeCommitmentTimeCommitment($timeCommitment);
            if($actualContract->getTimeCommitmentTimeCommitment() != $timeCommitment)
                $ContractHasChanged = true;
        }else{
            $contractRecord->setTimeCommitmentTimeCommitment($actualContract->getTimeCommitmentTimeCommitment());
        }
        if($paramFetcher->get("workplace_id")){//if workplace in paramfetcher
            /** @var Workplace $workplace */
            $workplace = $em->getRepository("RocketSellerTwoPickBundle:Workplace")->find($paramFetcher->get("workplace_id"));
            $contractRecord->setWorkplaceWorkplace($workplace);
            if($actualContract->getWorkplaceWorkplace() != $workplace)
                $ContractHasChanged = true;
        }else{
            $contractRecord->setWorkplaceWorkplace($actualContract->getWorkplaceWorkplace());
        }
        if($paramFetcher->get("sisben")){//if endDate in paramfetcher
            $contractRecord->setSisben($paramFetcher->get("sisben"));
            if($actualContract->getSisben()!= $paramFetcher->get("sisben"))
                $ContractHasChanged = true;
        }else{
            if($actualContract->getSisben()) $contractRecord->setSisben($actualContract->getSisben());
        }

        $minimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
        $newWorkableDaysMonth = $paramFetcher->get("workable_days_month");
        $newSalary = $paramFetcher->get("salary");
        $actualContractSalary = $actualContract->getSalary();
        $actualContractWorkableDaysOfMonth = $actualContract->getWorkableDaysMonth();
        if(!$newSalary)
            $newSalary = $actualContractSalary;
        if(!$newWorkableDaysMonth)
            $newWorkableDaysMonth = $actualContractWorkableDaysOfMonth;
        /** @var TimeCommitment $timeCommitment */
        $timeCommitment = $contractRecord->getTimeCommitmentTimeCommitment();
        if($timeCommitment->getCode() == "XD"){
            $minimumSalaryPerDay = $minimumSalary/30;
            $actualContractPerDaySalary = $actualContractSalary/$actualContractWorkableDaysOfMonth;
            $newSalaryPerDay = $newSalary/$newWorkableDaysMonth;
            if($actualContract->getWorkableDaysMonth() != $newWorkableDaysMonth){
                $contractRecord->setWorkableDaysMonth($newWorkableDaysMonth);
                $ContractHasChanged=true;
            }
            if($newSalaryPerDay < $actualContractPerDaySalary)
                $newSalaryPerDay = $actualContractPerDaySalary;
            if($newSalaryPerDay < $minimumSalaryPerDay)
                $newSalaryPerDay = $minimumSalaryPerDay;
            $newSalary = $newSalaryPerDay*$newWorkableDaysMonth;
        }else{
            if($newSalary < $actualContractSalary)
                $newSalary = $actualContractSalary;
            if($newSalary < $minimumSalary)
                $newSalary = $minimumSalary;
        }
        if($newSalary != $actualContractSalary){
            $contractRecord->setSalary($newSalary);
            $ContractHasChanged=true;
        }
        $contractRecord->setEmployerHasEmployeeEmployeeHasEmployee($actualContract->getEmployerHasEmployeeEmployerHasEmployee());
        $contractRecord->setEmployeeContractTypeEmployeeContractType($actualContract->getEmployeeContractTypeEmployeeContractType());
        $contractRecord->setHolidayDebt($actualContract->getHolidayDebt());
        if($actualContract->getWeekWorkableDays()->count()>0){
            /** @var WeekWorkableDays $weekWorkableDay */
            foreach ($actualContract->getWeekWorkableDays() as $weekWorkableDay) {
                $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                $weekWorkableDayRecord->setDayName($weekWorkableDay->getDayName());
                $weekWorkableDayRecord->setDayNumber($weekWorkableDay->getDayNumber());
                $em->persist($weekWorkableDayRecord);
                $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
            }
        }
        $contractRecord->setContractContract($actualContract);
        $contractRecord->setTimeCommitmentTimeCommitment($actualContract->getTimeCommitmentTimeCommitment());
        $contractRecord->setPositionPosition($actualContract->getPositionPosition());
        $contractRecord->setPayMethodPayMethod($actualContract->getPayMethodPayMethod());
        $contractRecord->setTestPeriod($actualContract->getTestPeriod());
        $contractRecord->setPlanillaTypePlanillaType($actualContract->getPlanillaTypePlanillaType());
        $contractRecord->setToBeExecuted(true);
        if($ContractHasChanged){
            $em->persist($contractRecord);
            $em->flush();
        }
        if($today->format("d-m-Y") == $dateToExecute->format("d-m-Y")) {
            $response["done"]=true;
        }else{
            $response["to_do"]=true;
        }

        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($response);
        return $view;
    }

//    private function executeContractRecord(ContractRecord $contractRecord){
//
//
//        $contractRecord->setFrequencyFrequency($actualContract->getFrequencyFrequency());
//        $contractRecord->setContractTypeContractType($actualContract->getContractTypeContractType());
//        $contractRecord->setEmployeeContractTypeEmployeeContractType($actualContract->getEmployeeContractTypeEmployeeContractType());
//        $contractRecord->setWorkplaceWorkplace($actualContract->getWorkplaceWorkplace());
//        $contractRecord->setSisben($actualContract->getSisben());
//        $contractRecord->setTransportAid($actualContract->getTransportAid());
//        $contractRecord->setSalary($actualContract->getSalary());
//        $contractRecord->setHolidayDebt($actualContract->getHolidayDebt());
//        if($actualContract->getWeekWorkableDays()->count()>0){
//            /** @var WeekWorkableDays $weekWorkableDay */
//            foreach ($actualContract->getWeekWorkableDays() as $weekWorkableDay) {
//                $weekWorkableDayRecord = new WeekWorkableDaysRecord();
//                $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
//                $weekWorkableDayRecord->setDayName($weekWorkableDay->getDayName());
//                $weekWorkableDayRecord->setDayNumber($weekWorkableDay->getDayNumber());
//                $em->persist($weekWorkableDayRecord);
//                $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
//            }
//        }
//        $contractRecord->setTimeCommitmentTimeCommitment($actualContract->getTimeCommitmentTimeCommitment());
//        $contractRecord->setPositionPosition($actualContract->getPositionPosition());
//        $contractRecord->setPayMethodPayMethod($actualContract->getPayMethodPayMethod());
//        $contractRecord->setStartDate($actualContract->getStartDate());
//        if($actualContract->getEndDate())$contractRecord->setEndDate($actualContract->getEndDate());
//        $contractRecord->setTestPeriod($actualContract->getTestPeriod());
//        $contractRecord->setWorkTimeStart($actualContract->getWorkTimeStart());
//        $contractRecord->setWorkTimeEnd($actualContract->getWorkTimeEnd());
//        $contractRecord->setWorkableDaysMonth($actualContract->getWorkableDaysMonth());
//        $contractRecord->setPlanillaTypePlanillaType($actualContract->getPlanillaTypePlanillaType());
//        $contractRecord->setDateChangesApplied(new \DateTime($dateToExecute));
//        $contractRecord->setToBeExecuted(false);
//        if($actualContract->getEndDate()){
//            $dateInterval = date_diff($actualContract->getStartDate(),$actualContract->getEndDate());
//            $endDate = $actualContract->getEndDate();
//            $contractRecord->setAutoRenewalEndDate($endDate->modify("+".$dateInterval->d." days +".
//                $dateInterval->m." month +".$dateInterval->y." years"));
//        }
//        $contractRecord->setContractContract($actualContract);
//        $em->persist($contractRecord);
//        return true;
//    }
//


}