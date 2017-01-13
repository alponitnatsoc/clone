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
     * @RequestParam(name="its_internal", nullable=true, requirements="(Y|N)", strict=false, description="Employee lives in the house or not")
     * @RequestParam(name="sisben", nullable=true, requirements="([0-9])", strict=false, description="Employee has sisben")
     * @RequestParam(name="payroll_type", nullable=true, requirements="4|6|1", strict=true, description="Payroll type, 4 for full time 6 part time 1 regular payroll.")
     * @RequestParam(name="salary", nullable=true, requirements="([0-9])+(.[0-9]+)?", strict=true, description="salary, must be greater than the minimum salary")
     * @RequestParam(name="date_to_execute", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date where changes must be executed in contract(format: DD-MM-YYYY).")
     * @RequestParam(name="workable_days_month", nullable=true, requirements="([0-9])+", description="workable days of the month count")
     * @RequestParam(name="workplace_id", nullable=true, requirements="([0-9])+", description="new workplace for the contract")
     *  @RequestParam(name="force", nullable=true, requirements="(Y|N)", description="force contract record")
     * @return View
     */
    public function putCreateContractRecordAction(ParamFetcher $paramFetcher)
    {
        $dateToExecute = $paramFetcher->get('date_to_execute');//getting the date where changes must be aplied
        $em = $this->getDoctrine()->getManager();//getting the entityManager
        $today = new DateTime();//getting the actual date
        $contractRecord = new ContractRecord();//creating a new contract record

        //getting the actual contract
        /** @var Contract $actualContract */
        $actualContract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($paramFetcher->get("contract_id"));//getting the actual contract

        //finding the user if theres no user setting it to backoffice
        /** @var User $user */
        if($this->getUser()){//setting the user to the session user or backoffice
            $user = $this->getUser();
        }else{
            $user = $em->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>"backOfficeSymplifica@gmail.com"));
        }

        //getting the employee person
        /** @var Person $employeePerson */
        $employeePerson = $actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();//getting the employee person
        $employeeHasChanged = false;//flag for employePerson changes
        //setting initial data for SQL
        $data = array(
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

        //if at least one employee parameter has changed calling modify employee in SQL
        if($employeeHasChanged){
            $request = $this->container->get('request');
            $request->setMethod("POST");
            $request->request->add($data);
            /** @var View $result */
            $result = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyEmployee', array('_format' => 'json'));
            if($result->getStatusCode() != 200) {
                return $result;
            }else{
                $response["employee_person"][$employeePerson->getIdPerson()]["SQLChanges"]="OK";
                $em->flush();
            }
        }
        //getting the date changes must be aplied
        if(!$dateToExecute){
            $dateToExecute = $today;
        }else{
            $dateToExecute = new DateTime($dateToExecute);
            $contractRecord->setDateToBeAplied($dateToExecute);
        }

        $response["data"]=$data;

        $ContractHasChanged = false;

        if($paramFetcher->get("start_date")){//if startDate in paramfetcher
            $newStartDate = new DateTime($paramFetcher->get("start_date"));
            $contractRecord->setStartDate($newStartDate);
            //checking start_date has changed
            if($actualContract->getStartDate()->format("d-m-Y") != $newStartDate->format("d-m-Y"))
                $ContractHasChanged = true;
        }else{
            $contractRecord->setStartDate($actualContract->getStartDate());
        }

        if($paramFetcher->get("end_date")){//if endDate in paramfetcher
            $newEndDate = new DateTime($paramFetcher->get("end_date"));
            $contractRecord->setEndDate($newEndDate);
            //checking end date has changed
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

        if($paramFetcher->get("its_internal")){//if transportAid in paramfetcher
            if($paramFetcher->get("its_internal")=='Y'){
                $contractRecord->setTransportAid(1);
            }else{
                $contractRecord->setTransportAid(0);
            }
            if($actualContract->getTransportAid() != $contractRecord->getTransportAid())
                $ContractHasChanged = true;
        }else{
            if($actualContract->getTransportAid()){
                $contractRecord->setTransportAid($actualContract->getTransportAid());
            }else{
                //setting transport aid to 0 if partial time
                if($actualContract->getTimeCommitmentTimeCommitment()->getCode()=="XD"){
                    $contractRecord->setTransportAid(0);
                }
            }
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

        if($paramFetcher->get("sisben")){//if sisben in paramfetcher
            $contractRecord->setSisben($paramFetcher->get("sisben"));
            if($actualContract->getSisben()!= $paramFetcher->get("sisben"))
                $ContractHasChanged = true;
        }else{
            if($actualContract->getSisben()) $contractRecord->setSisben($actualContract->getSisben());
        }

        $minimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
        $newSalary = $paramFetcher->get("salary");
        $actualContractSalary = $actualContract->getSalary();
        $newWorkableDaysMonth = intval($paramFetcher->get("workable_days_month"));
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
            $contractRecord->setWorkableDaysMonth($newWorkableDaysMonth);
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
            $contractRecord->setWorkableDaysMonth($newWorkableDaysMonth);
        }
        $contractRecord->setSalary($newSalary);
        $ContractHasChanged=true;
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
        $contractRecord->setToBeExecuted(1);
        if($ContractHasChanged){
            $em->persist($contractRecord);
            $em->flush();
            if($today->format("d-m-Y") == $dateToExecute->format("d-m-Y") or $today>=$dateToExecute) {
                if($this->executeContractRecord($contractRecord)){
                    $response["done"]=true;
                }else{
                    $response["done"]=false;
                }
            }else{
                $response["to_do"]=true;
            }
        }
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($response);
        return $view;
    }

    private function executeContractRecord(ContractRecord $contractRecord){

        $em = $this->getDoctrine()->getManager();
        /** @var Contract $contract */
        $contract = $contractRecord->getContractContract();
        $newRecord = new ContractRecord();
        if($contract->getPayMethodPayMethod())
            $newRecord->setPayMethodPayMethod($contract->getPayMethodPayMethod());
        if($contractRecord->getPayMethodPayMethod())
            $contract->setPayMethodPayMethod($contractRecord->getPayMethodPayMethod());
        if($contract->getEmployerHasEmployeeEmployerHasEmployee())
            $newRecord->setEmployerHasEmployeeEmployeeHasEmployee($contract->getEmployerHasEmployeeEmployerHasEmployee());
        if($contractRecord->getEmployerHasEmployeeEmployeeHasEmployee())
            $contract->setEmployerHasEmployeeEmployerHasEmployee($contractRecord->getEmployerHasEmployeeEmployeeHasEmployee());
        if($contract->getContractTypeContractType())
            $newRecord->setContractTypeContractType($contract->getContractTypeContractType());
        if($contractRecord->getContractTypeContractType())
            $contract->setContractTypeContractType($contractRecord->getContractTypeContractType());
        if($contract->getFrequencyFrequency())
            $newRecord->setFrequencyFrequency($contract->getFrequencyFrequency());
        if($contractRecord->getFrequencyFrequency())
            $contract->setFrequencyFrequency($contractRecord->getFrequencyFrequency());
        if($contract->getSalary())
            $newRecord->setSalary($contract->getSalary());
        if($contractRecord->getSalary())
            $contract->setSalary($contractRecord->getSalary());
        if($contract->getWorkplaceWorkplace())
            $newRecord->setWorkplaceWorkplace($contract->getWorkplaceWorkplace());
        if($contractRecord->getWorkplaceWorkplace())
            $contract->setWorkplaceWorkplace($contractRecord->getWorkplaceWorkplace());
        if($contract->getWeekWorkableDays()->count()>0){
            /** @var WeekWorkableDays $weekWorkableDay */
            foreach ($contract->getWeekWorkableDays() as $weekWorkableDay) {
                $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                $weekWorkableDayRecord->setDayNumber($weekWorkableDay->getDayNumber());
                $weekWorkableDayRecord->setDayName($weekWorkableDay->getDayName());
                $weekWorkableDayRecord->setContractRecordContractRecord($newRecord);
                $newRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
            }
        }
        if($contractRecord->getWeekWorkableDaysRecord()->count()>0){
            /** @var WeekWorkableDaysRecord $weekWorkableDayRecord */
            foreach ($contractRecord->getWeekWorkableDaysRecord() as $weekWorkableDayRecord) {
                if(!$contract->getWorkableDayByDayNumber($weekWorkableDayRecord->getDayNumber())){
                    $weekWorkableDay = new WeekWorkableDays();
                    $weekWorkableDay->setDayNumber($weekWorkableDayRecord->getDayNumber());
                    $weekWorkableDay->setDayName($weekWorkableDayRecord->getDayName());
                    $weekWorkableDay->setContractContract($weekWorkableDayRecord->getContractRecordContractRecord()->getContractContract());
                    $contract->addWeekWorkableDay($weekWorkableDay);
                }
            }
        }
        if($contract->getWorkableDaysMonth())
            $newRecord->setWorkableDaysMonth($contract->getWorkableDaysMonth());
        if($contractRecord->getWorkableDaysMonth())
            $contract->setWorkableDaysMonth($contractRecord->getWorkableDaysMonth());
        if($contract->getTimeCommitmentTimeCommitment())
            $newRecord->setTimeCommitmentTimeCommitment($contract->getTimeCommitmentTimeCommitment());
        if($contractRecord->getTimeCommitmentTimeCommitment())
            $contract->setTimeCommitmentTimeCommitment($contractRecord->getTimeCommitmentTimeCommitment());
        if($contract->getSisben())
            $newRecord->setSisben($contract->getSisben());
        if($contractRecord->getSisben())
            $contract->setSisben($contractRecord->getSisben());
        if($contract->getTransportAid())
            $newRecord->setTransportAid($contract->getTransportAid());
        if($contractRecord->getTransportAid())
            $contract->setTransportAid($contractRecord->getTransportAid());
        if($contract->getHolidayDebt())
            $newRecord->setHolidayDebt($contract->getHolidayDebt());
        if($contractRecord->getHolidayDebt())
            $contractRecord->setHolidayDebt($contractRecord->getHolidayDebt());
        if($contract->getEmployeeContractTypeEmployeeContractType())
            $newRecord->setEmployeeContractTypeEmployeeContractType($contract->getEmployeeContractTypeEmployeeContractType());
        if($contractRecord->getEmployeeContractTypeEmployeeContractType())
            $contractRecord->setEmployeeContractTypeEmployeeContractType($contractRecord->getEmployeeContractTypeEmployeeContractType());
        if($contract->getTestPeriod())
            $newRecord->setTestPeriod($contract->getTestPeriod());
        if($contractRecord->getTestPeriod())
            $contract->setTestPeriod($contractRecord->getTestPeriod());
        if($contract->getEndDate())
            $newRecord->setEndDate($contract->getEndDate());
        if($contractRecord->getEndDate())
            $contract->setEndDate($contractRecord->getEndDate());
        if($contract->getStartDate())
            $newRecord->setStartDate($contract->getStartDate());
        if($contractRecord->getStartDate())
            $contract->setStartDate($contractRecord->getStartDate());
        if($contract->getWorkTimeStart())
            $newRecord->setWorkTimeStart($contract->getWorkTimeStart());
        if($contractRecord->getWorkTimeStart())
            $contract->setWorkTimeStart($contractRecord->getWorkTimeStart());
        if($contract->getWorkTimeEnd())
            $newRecord->setWorkTimeEnd($contract->getWorkTimeEnd());
        if($contractRecord->getWorkTimeEnd())
            $contract->setWorkTimeEnd($contractRecord->getWorkTimeEnd());
        if($contract->getPlanillaTypePlanillaType())
            $newRecord->setPlanillaTypePlanillaType($contract->getPlanillaTypePlanillaType());
        if($contractRecord->getPlanillaTypePlanillaType())
            $contract->setPlanillaTypePlanillaType($contractRecord->getPlanillaTypePlanillaType());
        $newRecord->setDateChangesApplied(new DateTime());
        $newRecord->setDateToBeAplied($contractRecord->getDateToBeAplied());
        $newRecord->setContractContract($contract);
        $newRecord->setToBeExecuted(0);
        if ($contractRecord->getEndDate()!= null){
            $newRecord->setAutoRenewalEndDate($contractRecord->getAutoRenewalEndDate());
        }
        $newRecord->setContractContract($contract);
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $employeePerson = $contractRecord->getEmployerHasEmployeeEmployeeHasEmployee()->getEmployeeEmployee()->getPersonPerson();
        if($employeePerson->getDocumentType() == "PASAPORTE"){
            $employeeDocType = "PA";
        }else{
            $employeeDocType = $employeePerson->getDocumentType();
        }
        $minimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
        if($contractRecord->getTransportAid()==1){
            $transportAid = 'N';
        }elseif($contractRecord->getSalary() >= $minimumSalary*2){
            $transportAid = 'N';
        }else{
            $transportAid = 'S';
        }
        if ($contractRecord->getTimeCommitmentTimeCommitment()->getCode() == "TC") {
            $payroll_type = 4;
            $value = $contractRecord->getSalary();
            $wokableDaysWeek = 6;
        } else {
            $payroll_type = 6;
            $value = $contractRecord->getSalary() / $contractRecord->getWorkableDaysMonth();
            $wokableDaysWeek = $contractRecord->getWorkableDaysMonth() / 4;
        }
        $employer = $contractRecord->getEmployerHasEmployeeEmployeeHasEmployee()->getEmployerEmployer();
        $endDate = $contractRecord->getEndDate();
        $request->request->add(array(
            "employee_id" => $contractRecord->getEmployerHasEmployeeEmployeeHasEmployee()->getIdEmployerHasEmployee(),
            "last_name" => $employeePerson->getLastName1(),
            "first_name" => $employeePerson->getNames(),
            "document_type" => $employeeDocType,
            "document" => $employeePerson->getDocument(),
            "gender" => $employeePerson->getGender(),
            "birth_date" => $employeePerson->getBirthDate()->format("d-m-Y"),
            "start_date" => $contractRecord->getStartDate()->format("d-m-Y"),
            "contract_number" => $contract->getIdContract(),
            "worked_hours_day" => 8,
            "payment_method" => "EFE",
            "liquidation_type" => $contractRecord->getFrequencyFrequency()->getPayrollCode(),
            "contract_type" => $contractRecord->getContractTypeContractType()->getPayrollCode(),
            "transport_aux" => $transportAid,
            "worked_days_week" => $wokableDaysWeek,
            "society" => $employer->getIdSqlSociety(),
            "payroll_type" => $payroll_type,
        ));
        if ($endDate != null) {
            $request->request->add(array(
                "last_contract_end_date" => $endDate->format("d-m-Y")
            ));
        }
        if($newRecord->getStartDate()!= $contractRecord->getStartDate()){
            $request->request->add(array(
                "last_contract_start_date" => $newRecord->getStartDate()->format("d-m-Y")
            ));
        }
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyEmployee', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return false;
        }
        /** @var User $user */
        if($this->getUser()){//setting the user to the session user or backoffice
            $user = $this->getUser();
        }else{
            $user = $em->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('emailCanonical'=>"backOfficeSymplifica@gmail.com"));
        }
        $log = new Log($user,"Contract","all",$contract->getIdContract()," ",
            " ","Se modificó el contrato al ejecutar un contract record");
        $em->persist($log);
        $em->flush();
        $request->setMethod("POST");
        $date = $contractRecord->getDateToBeAplied();
        $request->request->add(array(
            "employee_id" => $contractRecord->getEmployerHasEmployeeEmployeeHasEmployee()->getIdEmployerHasEmployee(),
            "value" => $value,
            "date_change" => $date->format("d-m-Y"),
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyFixedConcepts', array('request' => $request ), array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            return false;
        }
        $log = new Log($user,"Contract","Salary",$contract->getIdContract(),$newRecord->getSalary(),$contract->getSalary()
            ,"Se modificó el salario desde execute contract record");
        $em->persist($log);
        $em->persist($newRecord);
        $em->persist($contract);
        $em->remove($contractRecord);
        $em->flush();
        dump($contract->getIdContract());die;
        return true;
    }

//    /**
//     * Update minimum salary
//     *
//     * @ApiDoc(
//     *   resource = true,
//     *   description = "Update minimum salary",
//     *   statusCodes = {
//     *     200 = "ALL OK",
//     *     400 = "Bad Request",
//     *   }
//     * )
//     *
//     * @param paramFetcher $paramFetcher ParamFetcher
//     *
//     * @RequestParam(name="year", nullable=false, requirements="[0-9]{4}", description="Year for new minimum salary")
//     * @RequestParam(name="start_index", nullable=false, requirements="([0-9])+", description="index to start loop")
//     * @RequestParam(name="end_index", nullable=false, requirements="([0-9])+", description="index to end loop")
//     * @RequestParam(name="minimum_salary", nullable=true, requirements="([0-9])+(.[0-9]+)?", strict=true, description="new minimum salary")
//     * @return View
//     */
//    public function postUpdateMinimumSalaryAction(ParamFetcher $paramFetcher)
//    {
//
//        $em = $this->getDoctrine()->getManager();
//        if($paramFetcher->get("minimum_salary")){
//            $newMinimumSalary = $paramFetcher->get("minimum_salary");
//            /** @var CalculatorConstraints $calcConstr */
//            $calcConstr = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"));
//            if($newMinimumSalary>$calcConstr->getValue()){
//                $calcConstr->setValue($newMinimumSalary);
//                $em->persist($calcConstr);
//                $em->flush();
//            }
//        }else{
//            $newMinimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
//        }
//        $minimumSalaryPerDay = $newMinimumSalary/30;
//        $contracts = $em->getRepository("RocketSellerTwoPickBundle:Contract")->findAll();
//        $today = new DateTime();
//        /** @var Contract $contract */
//        foreach ($contracts as $contract){
//            $ehe = $contract->getEmployerHasEmployeeEmployerHasEmployee();
//            $employer = $ehe->getEmployerEmployer();
//            if(!($ehe->getExistentSQL() == 1 and $ehe->getState() >= 4 and $employer->getIdSqlSociety()!=null))
//                continue;
//            /** @var TimeCommitment $timeCommitment */
//            $timeCommitment = $contract->getTimeCommitmentTimeCommitment();
//            if($timeCommitment->getCode() == "XD"){
//                $actualContractPerDaySalary = $contract->getSalary()/$contract->getWorkableDaysMonth();
//                if($actualContractPerDaySalary<=$minimumSalaryPerDay){
//                    $request = $this->container->get('request');
//                    $request->setMethod("PUT");
//                    $request->request->add(array(
//                        'date_to_execute'=>$today->format("d-m-Y"),
//                        'contract_id'=>$contract->getIdContract(),
//                        'salary'=>$minimumSalaryPerDay
//                    ));
//                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ContractRestSecured:putCreateContractRecord',
//                        array('request' => $request ), array('_format' => 'json'));
//                    if ($insertionAnswer->getStatusCode() != 200) {
//                        $response["contracts"][$contract->getIdContract()]="fail";
//                    }else{
//                        $response["contracts"][$contract->getIdContract()]="yay";
//                    }
//                }else{
//                    $response["contracts"][$contract->getIdContract()]="no entro";
//                }
//            }else{
//                $actualContractSalary = $contract->getSalary();
//                if($actualContractSalary<=$minimumSalary){
//                    $request = $this->container->get('request');
//                    $request->setMethod("PUT");
//                    $request->request->add(array(
//                        'date_to_execute'=>"01-01-2017",
//                        'contract_id'=>$contract->getIdContract(),
//                        'salary'=>$minimumSalary
//                    ));
//                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ContractRestSecured:putCreateContractRecord',
//                        array('request' => $request ), array('_format' => 'json'));
//                    if ($insertionAnswer->getStatusCode() != 200) {
//                        $response["contracts"][$contract->getIdContract()]="fail";
//                    }else{
//                        $response["contracts"][$contract->getIdContract()]="yay";
//                    }
//                }else{
//                    $response["contracts"][$contract->getIdContract()]="no entro";
//                }
//
//            }
//        }
//        $response['done']=true;
//        $view = View::create();
//        $view->setStatusCode(200);
//        $view->setData($response);
//        return $view;
//    }

//    public function postExecuteContractRecordsAction(ParamFetcher $paramFetcher)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $conRecord = $em->getRepository("RocketSellerTwoPickBundle:ContractRecord")->findAll();
//
//    }


    /**
     * Correct Contract minimum salary date
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Correct Contract minimum salary date",
     *   statusCodes = {
     *     200 = "ALL OK",
     *     400 = "Bad Request",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="start_index", nullable=false, requirements="([0-9])+", description="index to start loop")
     * @RequestParam(name="end_index", nullable=false, requirements="([0-9])+", description="index to end loop")
     * @return View
     */
    public function postCorrectContractsMinimumSalaryAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $conRecord = $em->getRepository("RocketSellerTwoPickBundle:ContractRecord")->findAll();
        $response["start"]="OK";
        $count = 1;
        $start_index = intval($paramFetcher->get("start_index"));
        $end_index = intval($paramFetcher->get("end_index"));
        foreach ($conRecord as $contractRecord) {
            if($count >= $start_index and $count<= $end_index){
                if($contractRecord->getToBeExecuted()==0){
                    /** @var Contract $contract */
                    $contract = $contractRecord->getContractContract();
                    $contractRecord->setDateToBeAplied(new DateTime("2017-01-01"));
                    if($contract->getTimeCommitmentTimeCommitment()->getCode()=="XD"){
                        $value = $contract->getSalary()/$contract->getWorkableDaysMonth();
                    }else{
                        $value = $contract->getSalary();
                    }
                    $request = $this->container->get('request');
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "employee_id" => $contractRecord->getEmployerHasEmployeeEmployeeHasEmployee()->getIdEmployerHasEmployee(),
                        "value" => $value,
                        "date_change" => "01-01-2017",
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PayrollRest:postModifyFixedConcepts', array('request' => $request ), array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        if($insertionAnswer->getStatusCode() == 404){
                            $response[$contract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee()]["SQL_salary"]="NOT FOUND";
                        }else{
                            $response[$contract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee()]["SQL_salary"]="NOT OK";
                        }
                    }else{
                        $response[$contract->getEmployerHasEmployeeEmployerHasEmployee()->getIdEmployerHasEmployee()]["SQL_salary"]="OK";
                        $em->persist($contractRecord);
                    }
                }
                if($count%20 == 0)$em->flush();
            }
            $count++;
        }
        if($end_index%20 != 0){
            $em->flush();
        }
        $em->flush();
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($response);
        return $view;

    }
}