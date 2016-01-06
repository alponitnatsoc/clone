<?php

namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Form\PayMethod;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Form\EmployeeBeneficiaryRegistration;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Employee controller.
 *
 */
class EmployeeController extends Controller
{

    /**
     * Lists all Employee entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('RocketSellerTwoPickBundle:Employee')->findAll();

        return $this->render('RocketSellerTwoPickBundle:Employee:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Employee entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RocketSellerTwoPickBundle:Employee')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Employee entity.');
        }

        return $this->render('RocketSellerTwoPickBundle:Employee:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
    * Maneja el registro de un beneficiario a un empleado con los datos básicos,
    * @param el Request que maneja el form que se imprime
    * @return La vista de el formulario de la nueva persona
    **/
    public function addBeneficiaryAction(Request $request, Employee $employee, Entity $entity=null) {
        if(is_null($entity)) {
            $entities = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                ->findByEmployeeEmployee($employee);
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiarySelectEntity.html.twig',
                array(
                    'entities' => $entities,
                    'employee' => $employee
                )
            );
        } else {
            $beneficiary = new Beneficiary();
            $form = $this->createForm(new EmployeeBeneficiaryRegistration(), $beneficiary);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $employeeBeneficiary = new EmployeeHasBeneficiary();
                $employeeBeneficiary->setEmployeeEmployee($employee);
                $employeeBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                $employeeBeneficiary->setEntityEntity($entity);
                $em = $this->getDoctrine()->getManager();
                $em->persist($beneficiary);
                $em->flush();
                $em->persist($employeeBeneficiary);
                $em->flush();
                return $this->redirectToRoute('manage_employees');
            }
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig',
                array('form' => $form->createView())
            );
        }
    }


    /**
    * Maneja el registro de un beneficiario a un empleado con los datos básicos,
    * @param el Request que maneja el form que se imprime
    * @return La vista de el formulario de la nueva persona
    **/
    public function manageBeneficiaryAction(Request $request, Employee $employee, Beneficiary $beneficiary=null) {
        if(is_null($beneficiary)) {
            $beneficiaries = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
                ->findByEmployeeEmployee($employee);
                
                return $this->render(
                    'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig',
                    array(
                        'beneficiaries' => $beneficiaries,
                        'employee' => $employee
                    )
                );
        } else {
            $form = $this->createForm(new EmployeeBeneficiaryRegistration(), $beneficiary);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $employeeBeneficiary = new EmployeeHasBeneficiary();
                $employeeBeneficiary->setEmployeeEmployee($employee);
                $employeeBeneficiary->setBeneficiaryBeneficiary($beneficiary);
                $em = $this->getDoctrine()->getManager();
                $em->persist($beneficiary);
                $em->flush();
                $em->persist($employeeBeneficiary);
                $em->flush();
                return $this->redirectToRoute('manage_employees');
            }
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig',
                array('form' => $form->createView())
            );
        }
    }


        /**
    * el dashboard de los empleados de cada empleador que le permite editar la información
    * y agregar nuevos empleados
    * TODO eliminar empleados
    * @return La vista de el formulario manager
    **/
    public function manageEmployeesAction()
    {
        $user=$this->getUser();
        $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        return $this->render(
            'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                'employees'=>$employeesData)
        );
    }
    /**
    * Maneja el formulario de un nuevo empleado
    * @param el Request y el Id del empleado, si lo desean editar
    * @return La vista de el formulario de la nuevo empleado
    **/
    public function newEmployeeAction( $id)
    {
        /** @var User $user */
        $user=$this->getUser();
        $employee=null;
        if ($id==-1) {
            $employee= new Employee();
        }else{
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
            //verify if the Id exists or it belongs to the logged user
            /** @var Employee $employee */
            $employee= $repository->find($id);
            /** @var EmployerHasEmployee $ee */
            $idEmployer=$user->getPersonPerson()->getEmployer()->getIdEmployer();
            $flag=false;
            foreach($employee->getEmployeeHasEmployers() as $ee){
                if($ee->getEmployerEmployer()->getIdEmployer()==$idEmployer){
                    $flag=true;
                    break;
                }
            }
            if($employee==null||!$flag){
                $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                return $this->render(
                    'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                    'employees'=>$employeesData));
            }
        }
        $userWorkplaces= $user->getPersonPerson()->getEmployer()->getWorkplaces();
        $tempPerson=$employee->getPersonPerson();
        if($tempPerson==null){
            $tempPerson=new Person();
            $employee->setPersonPerson($tempPerson);
        }
        if($tempPerson->getPhones()->count()==0){
            $tempPerson->addPhone(new Phone());
        }
        $form = $this->createForm(new PersonEmployeeRegistration($id,$userWorkplaces), $employee, array(
            'action' => $this->generateUrl('api_public_post_new_employee_submit'),
            'method' => 'POST',
        ));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:EmployeeForm.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
     * Retorna los campos especificos para el metodo de pago solicitado
     *
     * @param $id
     * @return Response
     */
    public function postPayMethodAction($id){
        $repository=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PayMethodFields");
        $fields=$repository->findBy(array('payTypePayType'=> $id));
        $options=array();
        foreach($fields as $field){
            $options[]=$field;
        }
        $form = $this->createForm(new PayMethod($fields));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:generalFormRender.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Muestra los beneficiarios del empleado
     * @return la vista de los beneficiarios
     */
    public function showBeneficiaryAction($id)
    {
        $employee = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Employee')
        ->find($id);
        if($employee){
            $beneficiaries = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
            ->findByEmployeeEmployee($employee);            
            if($beneficiaries){
                return $this->render(
                'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig',
                array('beneficiaries' => $beneficiaries,
                      'employee' => $employee
                        ));
            }else{
                throw $this->createNotFoundException('Unable to find Beneficiaries.');
            }
        }else{

        }
    }
    public function loginAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {

            $document = $this->get('request')->request->get('document');
            $cellphone = $this->get('request')->request->get('cellphone');
            $lastName1 = $this->get('request')->request->get('lastName1');

            $em = $this->getDoctrine()->getManager();            
            $person = $this->loadClassByArray(array('document'=>$document ,'phone'=>$cellphone , 'lastName1' =>$lastName1),"Person");
            $employee = $this->loadClassByArray(array('personPerson'=>$person),"Employee");
            if($employee){
                $code = rand (100000 , 999999);
                $employee->setTwoFactorCode($code);
                $twilio = $this->get('twilio.api');

                $twilio->account->messages->sendMessage(                                     
                        "+19562671001",                                         
                        "+57".$cellphone,                                         
                        "Hola este es tu codigo de autenticación: ".$code
                    );
                $em->flush($employee);                                         
                return $this->render('RocketSellerTwoPickBundle:Employee:loginEmployee2.html.twig',
                    array('employee'=>$employee)
                    );
                return $this->redirect('employee_login_two_auth', array('employee' => $employee));               
            }else{
                throw $this->createNotFoundException('Unable to find Employee.');
            }
                
        }else{
            return $this->render('RocketSellerTwoPickBundle:Employee:loginEmployee.html.twig');    
        }        
    }

    public function twoFactorLoginAction($id,Request $request)
    {
        $employee = $this->loadClassById($id,"Employee");
        if ($request->getMethod() == 'POST') {            
            $code = $this->get('request')->request->get('codigoTwo');
            $id = $request->query->get('id');       
            if($code == $employee->getTwoFactorCode()){
                return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
            }else{                
                var_dump($id);
                throw $this->createNotFoundException('Unable to find.');
            }                
        }
    }

    public function loadClassByArray($array, $entity)
    {
        $loadedClass = $this->getdoctrine()
        ->getRepository('RocketSellerTwoPickBundle:'.$entity)
        ->findOneBy($array);
        return $loadedClass;
    }
    public function loadClassById($parameter, $entity)
    {
        $loadedClass = $this->getdoctrine()
        ->getRepository('RocketSellerTwoPickBundle:'.$entity)
        ->find($parameter);
        return $loadedClass;
    } 
    public function missingDocumentsAction($employee)
    {
        $documentTypeByEntity = array();
        $entityByEmployee = array();
        $beneficiaries = array();
        $documentTypeAll = array();
        $employees = array();
        $result = array();
        $em = $this->getDoctrine()->getManager();
        $employee = $em->getRepository('RocketSellerTwoPickBundle:Employee')
                ->find($employee);
        $employeeHasBeneficiaries = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
                ->findByEmployeeEmployee($employee); 
        foreach ($employeeHasBeneficiaries as $employeeHasBeneficiary) {                    
                    $entitiesDocuments = $em->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                        ->findByEntityEntity($employeeHasBeneficiary->getEntityEntity());
                    foreach ($entitiesDocuments as $document) {                        
                        array_push($documentTypeByEntity, $document);
                        array_push($documentTypeAll, $document);     
                    }
                array_push($beneficiaries,$employeeHasBeneficiary->getBeneficiaryBeneficiary());
               }               
        foreach ($documentTypeAll as $document) {            
            if(!in_array($document->getDocumentTypeDocumentType(), $result)){
                array_push($result, $document->getDocumentTypeDocumentType());
            }
        }         
        $documentsPerBeneficiary = $this->fillArray($result,$employeeHasBeneficiaries);
        $documentsByBeneficiary = $this->documentsTypeByBeneficiary($beneficiaries);                               
       return $this->render('RocketSellerTwoPickBundle:Employee:beneficiaryDocuments.html.twig', array('beneficiaries'=>$beneficiaries,'result'=>$result ,'documentsPerBeneficiary'=>$documentsPerBeneficiary , 'documentsByBeneficiary'=>$documentsByBeneficiary));
    }
    public function documentsTypeByBeneficiary($beneficiaries)
    {        
        $documentsByBeneficiary = array();
        $docs = array();        
        foreach ($beneficiaries as $beneficiary) {            
            $person = $beneficiary->getPersonPerson();
            $em = $this->getDoctrine()->getManager();
            $documents = $em->getRepository('RocketSellerTwoPickBundle:Document')
                ->findByPersonPerson($person);                           
            foreach ($documents as $document) {
                array_push($docs,$document->getDocumentTypeDocumentType());   
            }
            array_push($documentsByBeneficiary, $docs);
            $docs = array();                                
        }                
        return $documentsByBeneficiary;
    }
    //se eliminan los documentos repetidos por empleado 
    public function removeDuplicated($beneficiaryDocs)
    {        
        $nonRepeated = array();
        $beneficiaryDoc = array();
        foreach ($beneficiaryDocs as $documents) {

            foreach ($documents as $document) {
                if(!in_array($document->getName(), $beneficiaryDoc)){                    
                    array_push($beneficiaryDoc, $document);
                }
            }         
            array_push($nonRepeated, $beneficiaryDoc);            
            $beneficiaryDoc = array();
        }   
        
        return $nonRepeated;
    }
    //se llenan los documentos que no necesita el empleado con respecto
    //a los documentos necesaris de las entidades
    public function fieldNotRequired($result,$documentsByBeneficiary)
    {
        $nonRepeated = array();
        $beneficiaryDoc = array();
        foreach ($documentsByBeneficiary as $documents){            
            foreach ($result as $base) {
                if(in_array($base->getName(), $documents)){                    
                    array_push($beneficiaryDoc, $base);
                }else{
                    array_push($beneficiaryDoc,'-');
                }
            }                  
            array_push($nonRepeated, $beneficiaryDoc);            
            $beneficiaryDoc = array();

        }  
        return $nonRepeated;
    }
    public function benefDocs($employeeHasBeneficiary){
        $benefDocs = array();
        $em = $this->getDoctrine()->getManager();
        $entitiesHasDocumentType = $em->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                        ->findByEntityEntity($employeeHasBeneficiary->getEntityEntity());
        foreach ($entitiesHasDocumentType as $entityHasDocumentType) {
            array_push($benefDocs, $entityHasDocumentType->getDocumentTypeDocumentType());
        }
        return $benefDocs;
    }
    public function fillArray($result,$employeeHasBeneficiaries)
    {
        $filled = array();        
        foreach ($employeeHasBeneficiaries as $employeeHasBeneficiary) {
            $docs = array();
            $beneficiaryId = $employeeHasBeneficiary->getBeneficiaryBeneficiary()->getIdBeneficiary();
            $benefDocs = $this->benefDocs($employeeHasBeneficiary);
            if(array_key_exists ($beneficiaryId ,$filled))
            {
                foreach ($result as $base) {            
                    if(in_array($base->getName(), $benefDocs)){
                        array_push($filled[$employeeId],$base);                    
                    }

                }                                 
            }else{
                $filled[$beneficiaryId] = array();                
                foreach ($result as $base) {            
                    if(in_array($base->getName(), $benefDocs)){
                        array_push($filled[$beneficiaryId],$base);                    
                    }
                }                                                             
            }
        }   
        return $this->fieldNotRequired($result,$this->removeDuplicated($filled));
    }
    public function employeeDocumentsAction($id)
    {
        $keys = array();
        $documentsPerEmployee = array();
        $person = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Person')
        ->find($id);
        $documentsEmployee = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Document')
        ->findByPersonPerson($person);
        $employee = $this->loadClassByArray(array('personPerson'=>$person),'Employee');
            $employeeHasEntities = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
            ->findByEmployeeEmployee($employee);
            $documentTypeByEmployee = array();
            foreach ($employeeHasEntities as $employeeHasEntity) {
                $entity = $employeeHasEntity->getEntityEntity();
                $entityHasDocumentsType = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                ->findByEntityEntity($entity);
                foreach ($entityHasDocumentsType as $entityHasDocumentType) {                    
                    array_push($documentTypeByEmployee,$entityHasDocumentType->getDocumentTypeDocumentType());
                }            
            }                                  
            foreach ($documentsEmployee as $doc) {
                if ($doc->getStatus()) {  
                    $documentsPerEmployee[$doc->getIdDocument()] = $doc->getDocumentTypeDocumentType();                                  
                    //array_push($documentsPerEmployee,$doc->getDocumentTypeDocumentType());
                }                 
            }
            $documentTypeByEmployee = $this->simpleRemoveDuplicated($documentTypeByEmployee);                           
        foreach ($documentTypeByEmployee as $document) {  
                $aux = array_search($document, $documentsPerEmployee);          
                if (!is_null($aux)) {
                    array_push($keys,array_search($document, $documentsPerEmployee));
                } 
        }
        return $this->render('RocketSellerTwoPickBundle:Employee:documents.html.twig',array('documentTypeByEmployee'=>$documentTypeByEmployee , 'employee'=>$employee ,'documentsPerEmployee'=>$documentsPerEmployee,'keys'=>$keys));
    }  
    public function simpleRemoveDuplicated($array)
    {
        $docs = array();
        foreach ($array as $doc) {
            if (!in_array($doc,$docs)) {
                array_push($docs,$doc);
            }
        }
        return $docs;
    }   
}
