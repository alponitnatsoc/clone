<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\entity\Employee;
use RocketSeller\TwoPickBundle\entity\Employer;
use RocketSeller\TwoPickBundle\entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\pdf\PDF_HTML;


class EmployerController extends Controller
{
    public function indexAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
    }
    public function certificateAction(Request $request)
    {
    	if ($request->getMethod() == 'POST') {    		
	    	$idEmployee = $this->get('request')->request->get('employee');   	
            $employee = $this->loadClassById($idEmployee,"Employee");
            $certificate = $this->get('request')->request->get('certificate');
   			$person = $this->getUser()->getPersonPerson();
	        $employer = $this->loadClassByArray(array("personPerson"=>$person),"Employer"); 
            switch ($certificate) {
            	case '1':
            			$cert = "Certificado laboral";            			
            		break;
        		case '2':
            			$cert = "Certificado de aportes";
            		break;
        		case '3':
            			$cert = "Otro certificado";
            		break;
        		case '4':
            			$cert = "Otro certificado mas";
            		break;              
            }
            $content = $this->render('RocketSellerTwoPickBundle:Employer:cert.html.twig',array('employee'=>$employee , 'certificate'=>$cert,'employer'=>$employer))->getContent();                        
            //echo $content;
            //return $this->generatePdf($content);

            return $this->render('RocketSellerTwoPickBundle:Employer:generatedCertificate.html.twig',array('employee'=>$employee , 'certificate'=>$cert,'employer'=>$employer,'content'=>$content));
   		}else{
   			$person = $this->getUser()->getPersonPerson();
	        $employer = $this->loadClassByArray(array("personPerson"=>$person),"Employer");    	
	        $em = $this->getDoctrine()->getManager();
	        $employerHasEmployees = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
	            ->findByEmployerEmployer($employer);
	        
	    	return $this->render('RocketSellerTwoPickBundle:Employer:certificates.html.twig',array('employerHasEmployees'=>$employerHasEmployees));
   		}
    }
    public function viewDocumentsAction()
    {
        
        $documentTypeByEntity = array();
        $entityByEmployee = array();
        $documentTypeAll = array();
        $employees = array();
        $result = array();
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array('personPerson'=>$person),"Employer");
        $em = $this->getDoctrine()->getManager();
        $employerHasEmployees = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findByEmployerEmployer($employer);        
        foreach ($employerHasEmployees as $employerHasEmployee) {
            $employeeHasEntities = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                ->findByEmployeeEmployee($employerHasEmployee->getEmployeeEmployee());
            foreach ($employeeHasEntities as $entity) {
                $entitiesDocuments = $em->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                ->findByEntityEntity($entity->getEntityEntity());
                array_push($entityByEmployee, $entity);
                foreach ($entitiesDocuments as $document) {
                    array_push($documentTypeByEntity, $document);
                    array_push($documentTypeAll, $document);                                                    
                }                                   
            }
            array_push($employees, $employerHasEmployee->getEmployeeEmployee());         
        }
        
        foreach ($documentTypeAll as $document) {            
            if(!in_array($document->getDocumentTypeDocumentType(), $result)){
                array_push($result, $document->getDocumentTypeDocumentType());
            }
        }        
        $documentsPerEmployee = $this->fillArray($result,$entityByEmployee);
        $documentsByEmployee = $this->documentsTypeByEmployee($employees);                       
        return $this->render('RocketSellerTwoPickBundle:Employer:viewDocuments.html.twig',array('employees'=>$employees,'documentsByEmployee'=>$documentsByEmployee , 'result'=>$result , 'documentsPerEmployee'=>$documentsPerEmployee));
    }
    public function documentsTypeByEmployee($employees)
    {        
        $documentsByEmployee = array();
        $docs = array();        
        foreach ($employees as $employee) {            
            $person = $employee->getPersonPerson();
            $em = $this->getDoctrine()->getManager();
            $documents = $em->getRepository('RocketSellerTwoPickBundle:Document')
                ->findByPersonPerson($person);                           
            foreach ($documents as $document) {
                array_push($docs,$document->getDocumentTypeDocumentType());   
            }
            array_push($documentsByEmployee, $docs);
            $docs = array();                                
        }                
        return $documentsByEmployee;
    }
    public function removeDuplicated($employeeDocs)
    {        
        $nonRepeated = array();
        $employeeDoc = array();
        foreach ($employeeDocs as $documents) {

            foreach ($documents as $document) {
                if(!in_array($document->getName(), $employeeDoc)){                    
                    array_push($employeeDoc, $document);
                }
            }         
            array_push($nonRepeated, $employeeDoc);            
            $employeeDoc = array();
        }   
        
        return $nonRepeated;
    }
    public function fieldNotRequired($result,$documentsByEmployee)
    {
        $nonRepeated = array();
        $employeeDoc = array();
        foreach ($documentsByEmployee as $documents){            
            foreach ($result as $base) {
                if(in_array($base->getName(), $documents)){                    
                    array_push($employeeDoc, $base);
                }else{
                    array_push($employeeDoc,'-');
                }
            }                  
            array_push($nonRepeated, $employeeDoc);            
            $employeeDoc = array();

        }  
        return $nonRepeated;
    }
    public function fillArray($result,$entityByEmployee)
    {
        $filled = array();
        foreach ($entityByEmployee as $entityEmployee) {
            $docs = array();
            $employeeId = $entityEmployee->getEmployeeEmployee()->getIdEmployee();
            $empDocs = $this->employeeDocuments($entityEmployee);
            if(array_key_exists ($employeeId ,$filled))
            {
                foreach ($result as $base) {            
                    if(in_array($base->getName(), $empDocs)){
                        array_push($filled[$employeeId],$base);                    
                    }

                }                                 
            }else{
                $filled[$employeeId] = array();                
                foreach ($result as $base) {            
                    if(in_array($base->getName(), $empDocs)){
                        array_push($filled[$employeeId],$base);                    
                    }
                }                                                             
            }
        }        
        return $this->fieldNotRequired($result,$this->removeDuplicated($filled));
        //return $filled;
    }
    public function employeeDocuments($entityEmployee)
    {
        $empDocs = array();
        foreach ($entityEmployee->getEntityEntity()->getEntityHasDocumentType() as $document) 
        {  
            array_push($empDocs, $document->getDocumentTypeDocumentType()->getName());
        }
        return $empDocs;
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
    public function generatePdfAction(Request $request){
    	$idEmployee = $_REQUEST["id"];
    	$employee = $this->loadClassById($idEmployee,"Employee");
		$person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array("personPerson"=>$person),"Employer");
        $content = $this->render('RocketSellerTwoPickBundle:Employer:cert.html.twig',array('employee'=>$employee ,'employer'=>$employer))->getContent();                        
    	$pdf = new PDF_HTML();
    	$pdf->AddPage();
		$pdf->SetFont('Arial');		
		$pdf->WriteHTML($content);
		
		return new Response($pdf->Output(), 200, array(
        'Content-Type' => 'application/pdf'));
    }  
}