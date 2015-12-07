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
        $documentTypeByEmployee = array();
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array('personPerson'=>$person),"Employer");
        $em = $this->getDoctrine()->getManager();
        $employerHasEmployees = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findByEmployerEmployer($employer);        
        foreach ($employerHasEmployees as $employerHasEmployee) {
            $employeeHasEntities = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                ->findByEmployeeEmployee($employerHasEmployee->getEmployeeEmployee());
            array_push($documentTypeByEmployee, $employeeHasEntities);
            foreach ($employeeHasEntities as $EmployeeHasEntity) {
                # code...
            }

        }        
        return $this->render('RocketSellerTwoPickBundle:Employer:viewDocuments.html.twig',array('employerHasEmployees'=>$employerHasEmployees , 'documentTypeByEmployee'=>$documentTypeByEmployee));
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