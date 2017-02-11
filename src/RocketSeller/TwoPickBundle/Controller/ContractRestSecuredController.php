<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\Criteria;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @RequestParam(name="frequency", nullable=true, requirements="(J|M|Q)", strict=false, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
     * @RequestParam(name="contract_type", nullable=true, requirements="(TF|TI)", strict=false, description="Contract type of the employee, TF termino fijo TI for termino indefinido")
     * @RequestParam(name="its_internal", nullable=true, requirements="(Y|N)", strict=false, description="Employee lives in the house or not")
     * @RequestParam(name="sisben", nullable=true, requirements="(Y|N)", strict=false, description="Employee has sisben")
     * @RequestParam(name="time_commitment", nullable=true, requirements="TC|XD", strict=true, description="TC tiempo completo, XD por dias.")
     * @RequestParam(name="salary", nullable=true, requirements="([0-9])+(.[0-9]+)?", strict=true, description="salary, must be greater than the minimum salary")
     * @RequestParam(name="date_to_execute", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date where changes must be executed in contract(format: DD-MM-YYYY).")
     * @RequestParam(name="workable_days_month", nullable=true, requirements="([0-9])+", description="workable days of the month count")
     * @RequestParam(name="workplace_id", nullable=true, requirements="([0-9])+", description="new workplace for the contract")
     * @RequestParam(name="week_workable_days",nullable=true, requirements="([a-z|A-Z|;]+)", description="workable days of the week separated by ")
     * @requestParam(name="works_saturday",nullable=true, requirements="(Y|N)",description="only for full time contracts if they work on saturdays")
     * @RequestParam(name="force", nullable=true, requirements="(Y|N)", description="force contract record")
     * @return View
     */
    public function putCreateContractRecordAction(ParamFetcher $paramFetcher)
    {
        if(!$paramFetcher->get('date_to_execute')){
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData("Error: El parametro date_to_execute es obligatorio");
            return $view;
        }
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
        $response["changes"]=array();

        $ContractHasChanged = false;

        if($paramFetcher->get("start_date")){//if startDate in paramfetcher
            $newStartDate = new DateTime($paramFetcher->get("start_date"));
            $contractRecord->setStartDate($newStartDate);
            //checking start_date has changed
            if($actualContract->getStartDate()->format("d-m-Y") != $newStartDate->format("d-m-Y")){
                $ContractHasChanged = true;
                $response["changes"]["start_date"]='changed from '.$actualContract->getStartDate()->format("d-m-Y").' to '.$newStartDate->format("d-m-Y");
            }

        }else{
            $contractRecord->setStartDate($actualContract->getStartDate());
        }

        if($paramFetcher->get("end_date")){//if endDate in paramfetcher
            $newEndDate = null;
            if($paramFetcher->get("end_date")!='00-00-0000'){
                $newEndDate = new DateTime($paramFetcher->get("end_date"));
                $contractRecord->setEndDate($newEndDate);
            }
            //checking end date has changed
            if($actualContract->getEndDate()){
                if($newEndDate!= null and $actualContract->getEndDate()->format("d-m-Y") != $newEndDate->format("d-m-Y")){
                    $ContractHasChanged = true;
                    $response["changes"]["end_date"]='changed from '.$actualContract->getEndDate()->format("d-m-Y").' to '.$newEndDate->format("d-m-Y");
                }elseif($newEndDate == null and $actualContract->getEndDate()!= null){
                    $ContractHasChanged = true;
                    $response["changes"]["end_date"]='changed from '.$actualContract->getEndDate()->format("d-m-Y").' to NULL';
                }

            }else{
                $ContractHasChanged = true;
                $response["changes"]["end_date"]='changed from null to '.$newEndDate->format("d-m-Y");
            }
        }else{
            if($actualContract->getEndDate()) $contractRecord->setEndDate($actualContract->getEndDate());
        }

        if($paramFetcher->get("frequency")){//if frecuency in paramfetcher
            /** @var Frequency $frequency */
            $frequency = $em->getRepository("RocketSellerTwoPickBundle:Frequency")->findOneBy(array('payroll_code'=>$paramFetcher->get("frequency")));
            $contractRecord->setFrequencyFrequency($frequency);
            if($actualContract->getFrequencyFrequency() != $frequency){
                $response["changes"]["frequency"]='changed from '.$actualContract->getFrequencyFrequency()->getPayrollCode().' to '.$paramFetcher->get("frequency");
                $ContractHasChanged = true;
            }
        }else{
            $contractRecord->setFrequencyFrequency($actualContract->getFrequencyFrequency());
        }

        if($paramFetcher->get("time_commitment")){//if payroll_type in paramfetcher
            $timeCommitment = null;
            if($paramFetcher->get("time_commitment") == 'TC'){
                /** @var TimeCommitment $timeCommitment */
                $timeCommitment = $em->getRepository("RocketSellerTwoPickBundle:TimeCommitment")->findOneBy(array("code"=>'TC'));
            }elseif($paramFetcher->get("time_commitment") == 'XD'){
                /** @var TimeCommitment $timeCommitment */
                $timeCommitment = $em->getRepository("RocketSellerTwoPickBundle:TimeCommitment")->findOneBy(array("code"=>'XD'));
            }
            $contractRecord->setTimeCommitmentTimeCommitment($timeCommitment);
            if($actualContract->getTimeCommitmentTimeCommitment() != $timeCommitment){
                $response["changes"]["time_commitment"]='changed from '.$actualContract->getTimeCommitmentTimeCommitment()->getCode().' to '.$timeCommitment->getCode();
                $ContractHasChanged = true;
            }

        }else{
            $contractRecord->setTimeCommitmentTimeCommitment($actualContract->getTimeCommitmentTimeCommitment());
        }
        if($paramFetcher->get("contract_type")){//if contractType in paramfetcher
            if($paramFetcher->get("contract_type")=='TF'){
                $contract_type_payroll_code = 2;
            }else{
                $contract_type_payroll_code = 1;
            }
            /** @var ContractType $contractType */
            $contractType = $em->getRepository("RocketSellerTwoPickBundle:ContractType")->findOneBy(array("payroll_code"=>$contract_type_payroll_code));
            $contractRecord->setContractTypeContractType($contractType);
            if($actualContract->getContractTypeContractType() != $contractType){
                if($contractType->getPayrollCode()=='2' and $paramFetcher->get("end_date")!=null){
                    $view =  View::create();
                    $view->setStatusCode(400);
                    $view->setData(array('Error'=>"El parametro end_date es obligatorio si el tipo de contrato es termino fijo"));
                    return $view;
                }elseif($contractType->getPayrollCode()=='1'){
                    $view =  View::create();
                    $view->setStatusCode(400);
                    $view->setData(array('Error'=>"Este cambio no esta soportado no se puede cambiar de termino fijo a termino indefinido"));
                    return $view;
                }
                $response["changes"]["frequency"]='changed from '.$actualContract->getContractTypeContractType()->getName().' to '.$contractType->getName();
                $ContractHasChanged = true;
            }

        }else{
            $contractRecord->setContractTypeContractType($actualContract->getContractTypeContractType());
        }

        if($paramFetcher->get("its_internal")){
            if($paramFetcher->get("its_internal")=='Y'){
                $contractRecord->setTransportAid(1);
            }else{
                $contractRecord->setTransportAid(0);
            }
            if($actualContract->getTransportAid() != $contractRecord->getTransportAid()){
                if($paramFetcher->get("its_internal")=='Y'){
                    $response["changes"]["its_internal"]='changed from external to internal';
                }else{
                    $response["changes"]["its_internal"]='changed from internal to external';
                }
                $ContractHasChanged = true;
            }
        }else{
            if($actualContract->getTransportAid()!= null ){
                $contractRecord->setTransportAid($actualContract->getTransportAid());
            }else{
                //setting transport aid to 0 if partial time
                if($contractRecord->getTimeCommitmentTimeCommitment()->getCode()=="XD"){
                    $contractRecord->setTransportAid(0);
                }
            }
        }

        if($paramFetcher->get("workplace_id")){//if workplace in paramfetcher
            /** @var Workplace $workplace */
            $workplace = $em->getRepository("RocketSellerTwoPickBundle:Workplace")->find($paramFetcher->get("workplace_id"));
            if($workplace==null){
                $view = View::create();
                $view->setStatusCode(404);
                $view->setData(array('Error'=>"No se encontro ningun lugar de trabajo con id ".$paramFetcher->get("workplace_id")));
                return $view;
            }
            if($workplace->getEmployerEmployer()!= $actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()){
                $view = View::create();
                $view->setStatusCode(400);
                $view->setData(array('Error'=>"El lugar de trabajo ".$paramFetcher->get("workplace_id")." no pertenece al empleador ".$actualContract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getIdEmployer()));
                return $view;
            }
            $contractRecord->setWorkplaceWorkplace($workplace);
            if($actualContract->getWorkplaceWorkplace() != $workplace){
                $response["changes"]["workplace_id"]='changed from '.$actualContract->getWorkplaceWorkplace()->getIdWorkplace().' to '.$workplace->getIdWorkplace();
                $ContractHasChanged = true;
            }

        }else{
            $contractRecord->setWorkplaceWorkplace($actualContract->getWorkplaceWorkplace());
        }

        if($paramFetcher->get("sisben")){//if sisben in paramfetcher
            if($paramFetcher->get("sisben")=='Y'){
                $sisben = 1;
            }else{
                $sisben = 0;
            }
            $contractRecord->setSisben($sisben);
            if($actualContract->getSisben()!= $sisben){
                $ContractHasChanged = true;
            }

        }else{
            if($actualContract->getSisben()) $contractRecord->setSisben($actualContract->getSisben());
        }

        if($paramFetcher->get("works_saturday")){
            if($paramFetcher->get("works_saturday")=='Y' and $actualContract->getTimeCommitmentTimeCommitment()->getCode()=='TC'){
                $contractRecord->setWorksSaturday(1);
                if($actualContract->getWorksSaturday()!= 1){
                    $response["changes"]["works_saturday"]='changed from '.$actualContract->getWorksSaturday().' to 1 Yes';
                    $ContractHasChanged=true;
                }
            }else{
                if($actualContract->getTimeCommitmentTimeCommitment()->getCode()=='XD'){
                    $contractRecord->setWorksSaturday(0);
                    if($actualContract->getWorksSaturday()!= 0){
                        $response["changes"]["works_saturday"]='changed from '.$actualContract->getWorksSaturday().' to 0 Not asked';
                        $ContractHasChanged = true;
                    }
                }else{
                    $contractRecord->setWorksSaturday(-1);
                    if($actualContract->getWorksSaturday()!= 1) {
                        $response["changes"]["works_saturday"]='changed from '.$actualContract->getWorksSaturday().' to -1 No';
                        $ContractHasChanged = true;
                    }
                }
            }
        }else{
            if($actualContract->getWorksSaturday()){
                $contractRecord->setWorksSaturday($actualContract->getWorksSaturday());
            }else{
                if($actualContract->getTimeCommitmentTimeCommitment()->getCode()=='TC'){
                    $response["changes"]["works_saturday"]='changed from NULL to 0 Not asked';
                    $contractRecord->setWorksSaturday(0);
                }else{
                    $response["changes"]["works_saturday"]='changed from NULL to -1 No';
                    $contractRecord->setWorksSaturday(-1);
                }
                $ContractHasChanged = true;
            }
        }
        $newDaysFlag = false;
        if($paramFetcher->get("week_workable_days")){
            if($actualContract->getTimeCommitmentTimeCommitment()->getCode()!='TC'){
                $workableDays = $paramFetcher->get("week_workable_days");
                $days = explode(';',$workableDays);
                if(count($days)!=count($actualContract->getWeekWorkableDays()))$ContractHasChanged = true;
                if($paramFetcher->get("workable_days_month")){
                    if(count($days)*4!=$paramFetcher->get("workable_days_month")){
                        $view = View::create();
                        $view->setStatusCode(400);
                        if(count($days)==1){
                            $errorMessage = ''."El numero de week_workable_days enviados no coincide con los dias trabajados al mes. Para ".count($days)." dia los workable_days_month deben ser ".(count($days)*4);
                        }else{
                            $errorMessage = ''."El numero de week_workable_days enviados no coincide con los dias trabajados al mes. Para ".count($days)." dias los workable_days_month deben ser ".(count($days)*4);
                        }
                        $view->setData(array('Error'=>$errorMessage));
                        return $view;
                    }
                }else{
                    $newWorkableDaysMonth = count($days)*4;
                    $newDaysFlag = true;
                }
                $response["changes"]["week_workable_days"] = '';
                foreach ($days as $day){
                    switch ($day){
                        case 'lunes':
                            if($actualContract->getWorkableDayByDayNumber(1)== null){
                                $response["changes"]["week_workable_days"].='lunes agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(1);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'martes':
                            if($actualContract->getWorkableDayByDayNumber(2)== null){
                                $ContractHasChanged = true;
                                $response["changes"]["week_workable_days"].='martes agregado, ';
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(2);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'miercoles':
                            if($actualContract->getWorkableDayByDayNumber(3)== null){
                                $response["changes"]["week_workable_days"].='miercoles agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(3);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'jueves':
                            if($actualContract->getWorkableDayByDayNumber(4)== null){
                                $response["changes"]["week_workable_days"].='jueves agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(4);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'viernes':
                            if($actualContract->getWorkableDayByDayNumber(5)== null){
                                $response["changes"]["week_workable_days"].='viernes agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(5);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'sabado':
                            if($actualContract->getWorkableDayByDayNumber(6)== null){
                                $response["changes"]["week_workable_days"].='sabado agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(6);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        case 'domingo':
                            if($actualContract->getWorkableDayByDayNumber(7)== null){
                                $response["changes"]["week_workable_days"].='domingo agregado, ';
                                $ContractHasChanged = true;
                            }
                            $weekWorkableDayRecord = new WeekWorkableDaysRecord();
                            $weekWorkableDayRecord->setContractRecordContractRecord($contractRecord);
                            $weekWorkableDayRecord->setDayName($day);
                            $weekWorkableDayRecord->setDayNumber(7);
                            $em->persist($weekWorkableDayRecord);
                            $contractRecord->addWeekWorkableDaysRecord($weekWorkableDayRecord);
                            break;
                        default:
                            $view = View::create();
                            $view->setStatusCode(400);
                            $view->setData("Error: Error en el parametro week_workable_days los valores validos son lunes, martes, miercoles, jueves, viernes, sabado, domingo separados por ; ");
                            return $view;
                            break;
                    }
                }
            }
        }else{
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
        }

        $minimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
        $newSalary = $paramFetcher->get("salary");
        $actualContractSalary = $actualContract->getSalary();
        if(!$newDaysFlag) $newWorkableDaysMonth = intval($paramFetcher->get("workable_days_month"));
        $actualContractWorkableDaysOfMonth = $actualContract->getWorkableDaysMonth();
        /** @var TimeCommitment $timeCommitment */
        $timeCommitment = $contractRecord->getTimeCommitmentTimeCommitment();
        if(!$newWorkableDaysMonth)
            $newWorkableDaysMonth = $actualContractWorkableDaysOfMonth;
        if($newWorkableDaysMonth != $actualContractWorkableDaysOfMonth){
            if($contractRecord->getTimeCommitmentTimeCommitment()->getCode()=='XD'){
                if(!$paramFetcher->get("week_workable_days")){
                    $view = View::create();
                    $view->setStatusCode(400);
                    $view->setData(array('Error'=>"Error no se pueden cambiar los workable_days_month de un contrato por dias sin enviar los nuevos week_workable_days."));
                    return $view;
                }
            }else{
                $view = View::create();
                $view->setStatusCode(400);
                $view->setData(array('Error'=>"Error no se pueden cambiar los workable_days_month de un contrato a tiempo completo."));
                return $view;
            }
        }
        if(!$newSalary){
            if($timeCommitment->getCode() == "XD"){
                $newSalary = ($actualContractSalary/$actualContractWorkableDaysOfMonth)*$newWorkableDaysMonth;
            }else{
                $newSalary = $actualContractSalary;
            }
        }

        if($timeCommitment->getCode() == "XD"){
            $minimumSalaryPerDay = $minimumSalary/30;
            $actualContractPerDaySalary = $actualContractSalary/$actualContractWorkableDaysOfMonth;
            $newSalaryPerDay = $newSalary/$newWorkableDaysMonth;
            if($newSalaryPerDay < $minimumSalaryPerDay)
                $newSalaryPerDay = $minimumSalaryPerDay;
            if($newSalaryPerDay < $actualContractPerDaySalary)
                $newSalaryPerDay = $actualContractPerDaySalary;
            $newSalary = $newSalaryPerDay*$newWorkableDaysMonth;
        }else{
            if($newSalary < $actualContractSalary)
                $newSalary = $actualContractSalary;
            if($newSalary < $minimumSalary)
                $newSalary = $minimumSalary;
        }
        $contractRecord->setWorkableDaysMonth($newWorkableDaysMonth);
        $contractRecord->setSalary($newSalary);
        if($actualContractSalary!= $newSalary){
            $response["changes"]["salary"]='Changed from '.$actualContract->getSalary().' to '.$newSalary;
            if($contractRecord->getTimeCommitmentTimeCommitment()->getCode()=='XD'){
                $response["changes"]["salary_per_day"]='Changed from '.$actualContractPerDaySalary.' to '.$newSalaryPerDay;
            }
            $ContractHasChanged=true;
        }
        if($actualContractWorkableDaysOfMonth != $newWorkableDaysMonth){
            $response["changes"]["workable_days_month"]='Changed from '.$actualContractWorkableDaysOfMonth.' to '.$newWorkableDaysMonth;
            $ContractHasChanged=true;
        }
        $contractRecord->setEmployerHasEmployeeEmployeeHasEmployee($actualContract->getEmployerHasEmployeeEmployerHasEmployee());
        $contractRecord->setEmployeeContractTypeEmployeeContractType($actualContract->getEmployeeContractTypeEmployeeContractType());
        $contractRecord->setHolidayDebt($actualContract->getHolidayDebt());
        $contractRecord->setContractContract($actualContract);
        $contractRecord->setTimeCommitmentTimeCommitment($actualContract->getTimeCommitmentTimeCommitment());
        $contractRecord->setPositionPosition($actualContract->getPositionPosition());
        $contractRecord->setPayMethodPayMethod($actualContract->getPayMethodPayMethod());
        if($contractRecord->getContractTypeContractType()->getPayrollCode()==1){
            $newTestPeriod = $contractRecord->getStartDate()->modify("+2 month");
        }else{
            $endTestPeriod2 = new DateTime(date('Y-m-d', strtotime('+' . intval($contractRecord->getStartDate()->diff($contractRecord->getEndDate())->format("%a") / 5) . ' day', strtotime($contractRecord->getStartDate()->format("Y-m-d")))));
            $newTestPeriod = new DateTime(date('Y-m-d', strtotime('+2 month', strtotime($contractRecord->getStartDate()->format("Y-m-d")))));
            if ($endTestPeriod2 < $newTestPeriod) {
                $newTestPeriod = $endTestPeriod2;
            }
        }
        $contractRecord->setTestPeriod($newTestPeriod);
        if($newTestPeriod!=$actualContract->getTestPeriod()){
            $ContractHasChanged=true;
            $response["changes"]["test_period"]='Changed from '.$actualContract->getTestPeriod()->format("d-m-Y").' to '.$newTestPeriod->format("d-m-Y");
        }
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

    /**
     * executes the contract record if the date to be executed is lower or equal to de actual date
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "executes the contract record if the date to be executed is lower or equal to de actual date",
     *   statusCodes = {
     *     200 = "Created successfully",
     *     400 = "Bad Request",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     * @RequestParam(name="contract_record_id", nullable=false, requirements="([0-9])+", description="Contract Record to be executed")
     *
     * @return View
     */
    public function postExecuteContractRecordAction(ParamFetcher $paramFetcher){
        $em = $this->getDoctrine()->getManager();
        if(!$paramFetcher->get('contract_record_id')){
            $view = View::create();
            $view->setStatusCode(400);
            return $view;
        }
        /** @var ContractRecord $contractRecord */
        $contractRecord = $em->getRepository("RocketSellerTwoPickBundle:ContractRecord")->find($paramFetcher->get('contract_record_id'));
        $today = new DateTime();
        if ($contractRecord->getDateToBeAplied()->format("d-m-Y")==$today->format("d-m-Y") or $contractRecord->getDateToBeAplied()<$today){
            $resp = $this->executeContractRecord($contractRecord);
            if($resp){
                $view = View::create();
                $view->setStatusCode(200);
                $view->setData(array("executed"=>true));
                return $view;
            }else{
                $view = View::create();
                $view->setStatusCode(400);
                $view->setData(array("executed"=>false,'error'=>"something go wrong executing the contract record with id: ".$paramFetcher->get('contract_record_id')));
                return $view;
            }
        }else{
            $view = View::create();
            $view->setStatusCode(400);
            $view->setData(array("executed"=>false,'error'=>'Date to be executed is greater than actual date'));
            return $view;
        }
    }

    /**
     * Executes all contract records pending for the actual date
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes all contract records pending for the actual date",
     *   statusCodes = {
     *     200 = "Created successfully",
     *     400 = "Bad Request",
     *   }
     * )
     *
     * @return View
     */
    public function putExecuteAllPendingContractRecordsAction(){
        $em = $this->getDoctrine()->getManager();
        $date = new DateTime();
        $criteria = Criteria::create()->where(Criteria::expr()->lte('dateToBeAplied',$date))->andWhere(Criteria::expr()->eq('toBeExecuted',1));
        $contractRecords = $em->getRepository("RocketSellerTwoPickBundle:ContractRecord")->matching($criteria);
        $response = array();
        $response["contractRecords"] = array();
        /** @var ContractRecord $contractRecord */
        foreach ($contractRecords as $contractRecord) {
            $res =  $this->executeContractRecord($contractRecord);
            if($res){
                $response["contractRecords"][$contractRecord->getIdContractRecord()]["executed"]=true;
            }else{
                $response["contractRecords"][$contractRecord->getIdContractRecord()]["executed"]=false;
                $response["contractRecords"][$contractRecord->getIdContractRecord()]["error"]="Something went wrong executing this contract record please try it manually";
            }
        }
        $response["cronExecutedAt"] = $date;
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
        if($contract->getWorksSaturday())
            $newRecord->setWorksSaturday($contract->getWorksSaturday());
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
            /** @var WeekWorkableDays $weekWorkableDay */
            foreach ($contract->getWeekWorkableDays() as $weekWorkableDay) {
                $contract->removeWeekWorkableDay($weekWorkableDay);
                $em->remove($weekWorkableDay);
            }
            $em->flush();
            /** @var WeekWorkableDaysRecord $weekWorkableDayRecord */
            foreach ($contractRecord->getWeekWorkableDaysRecord() as $weekWorkableDayRecord) {
                $weekWorkableDay = new WeekWorkableDays();
                $weekWorkableDay->setDayNumber($weekWorkableDayRecord->getDayNumber());
                $weekWorkableDay->setDayName($weekWorkableDayRecord->getDayName());
                $weekWorkableDay->setContractContract($weekWorkableDayRecord->getContractRecordContractRecord()->getContractContract());
                $contract->addWeekWorkableDay($weekWorkableDay);
            }
        }
        if($contract->getWorkableDaysMonth())
            $newRecord->setWorkableDaysMonth($contract->getWorkableDaysMonth());
        if($contractRecord->getWorksSaturday())
            $contract->setWorksSaturday($contractRecord->getWorksSaturday());
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
        if($contractRecord->getTransportAid()!=null)
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
        if($contract->getPositionPosition())
            $newRecord->setPositionPosition($contract->getPositionPosition());
        if($contractRecord->getPositionPosition())
            $contract->setPositionPosition($contractRecord->getPositionPosition());
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
        if($contract->getWorksSaturday()==1){
            $saturday = 2;
        } else {
            $saturday = 1;
        }
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
            "cal_type"=> $saturday
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
        return true;
    }

    /**
     * Update minimum salary
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Update minimum salary",
     *   statusCodes = {
     *     200 = "ALL OK",
     *     400 = "Bad Request",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="year", nullable=false, requirements="[0-9]{4}", description="Year for new minimum salary")
     * @RequestParam(name="start_index", nullable=false, requirements="([0-9])+", description="index to start loop")
     * @RequestParam(name="end_index", nullable=false, requirements="([0-9])+", description="index to end loop")
     * @RequestParam(name="minimum_salary", nullable=true, requirements="([0-9])+(.[0-9]+)?", strict=true, description="new minimum salary")
     * @return View
     */
    public function postUpdateMinimumSalaryAction(ParamFetcher $paramFetcher)
    {

        $em = $this->getDoctrine()->getManager();
        if($paramFetcher->get("minimum_salary")){
            $newMinimumSalary = $paramFetcher->get("minimum_salary");
            /** @var CalculatorConstraints $calcConstr */
            $calcConstr = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"));
            if($newMinimumSalary>$calcConstr->getValue()){
                $calcConstr->setValue($newMinimumSalary);
                $em->persist($calcConstr);
                $em->flush();
            }
        }else{
            $newMinimumSalary = $em->getRepository("RocketSellerTwoPickBundle:CalculatorConstraints")->findOneBy(array("name"=>"smmlv"))->getValue();
        }
        $minimumSalaryPerDay = $newMinimumSalary/30;
        $contracts = $em->getRepository("RocketSellerTwoPickBundle:Contract")->findAll();
        $today = new DateTime();
        /** @var Contract $contract */
        foreach ($contracts as $contract){
            $ehe = $contract->getEmployerHasEmployeeEmployerHasEmployee();
            $employer = $ehe->getEmployerEmployer();
            if(!($ehe->getExistentSQL() == 1 and $ehe->getState() >= 4 and $employer->getIdSqlSociety()!=null))
                continue;
            /** @var TimeCommitment $timeCommitment */
            $timeCommitment = $contract->getTimeCommitmentTimeCommitment();
            if($timeCommitment->getCode() == "XD"){
                $actualContractPerDaySalary = $contract->getSalary()/$contract->getWorkableDaysMonth();
                if($actualContractPerDaySalary<=$minimumSalaryPerDay){
                    $request = $this->container->get('request');
                    $request->setMethod("PUT");
                    $request->request->add(array(
                        'date_to_execute'=>$today->format("d-m-Y"),
                        'contract_id'=>$contract->getIdContract(),
                        'salary'=>$minimumSalaryPerDay
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ContractRestSecured:putCreateContractRecord',
                        array('request' => $request ), array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        $response["contracts"][$contract->getIdContract()]="fail";
                    }else{
                        $response["contracts"][$contract->getIdContract()]="yay";
                    }
                }else{
                    $response["contracts"][$contract->getIdContract()]="no entro";
                }
            }else{
                $actualContractSalary = $contract->getSalary();
                if($actualContractSalary<=$newMinimumSalary){
                    $request = $this->container->get('request');
                    $request->setMethod("PUT");
                    $request->request->add(array(
                        'date_to_execute'=>"01-01-2017",
                        'contract_id'=>$contract->getIdContract(),
                        'salary'=>$newMinimumSalary
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ContractRestSecured:putCreateContractRecord',
                        array('request' => $request ), array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() != 200) {
                        $response["contracts"][$contract->getIdContract()]="fail";
                    }else{
                        $response["contracts"][$contract->getIdContract()]="yay";
                    }
                }else{
                    $response["contracts"][$contract->getIdContract()]="no entro";
                }

            }
        }
        $response['done']=true;
        $view = View::create();
        $view->setStatusCode(200);
        $view->setData($response);
        return $view;
    }

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