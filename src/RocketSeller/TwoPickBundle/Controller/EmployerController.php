<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Form\EmployerEdit;
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
    public function documentCompletionAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Employer:documentsCompletion.html.twig');
    }

    public function showDataAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $employer = $person->getEmployer();

        return $this->render('RocketSellerTwoPickBundle:Employer:showPerson.html.twig', array('employer' => $employer));
    }

    public function editEmployerAction()
    {
        $user = $this->getUser();
        /** @var Person $people */
        $people = $user->getPersonPerson();
        $employer = $people->getEmployer();
        if ($employer == null) {
            $employer = new Employer();
            $people->setEmployer($employer);
        }

        if (count($employer->getWorkplaces()) == 0) {
            $workplace = new Workplace();
            $employer->addWorkplace($workplace);
            $people->setEmployer($employer);
        }
        $employer->setEmployerType("persona");
        if ($people->getPhones()->count() == 0 || $people->getPhones() == null) {
            $phone = new Phone();
            $people->addPhone($phone);
        }

        $form = $this->createForm(new EmployerEdit(), $employer, array(
            'action' => $this->generateUrl('api_public_post_edit_person_submit_step3', array('format' => 'json')),
            'method' => 'POST',
        ));

        $form->get("documentExpeditionDate")->setData($people->getDocumentExpeditionDate());

        return $this->render(
                        'RocketSellerTwoPickBundle:Employer:editPerson.html.twig', array('form' => $form->createView())
        );
    }

    public function profileEmployerAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Employer:profile.html.twig');
    }

    public function registrationDocumentsAction()
    {
        $user = $this->getUser();
        $documents = array();
        $employees = array();
        $em = $this->getDoctrine()->getManager();
        $person = $this->getUser()->getPersonPerson();
        $employer = $em->getRepository('RocketSellerTwoPickBundle:Employer')
                ->findByPersonPerson($person);
        $employerHasEmployees = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findByEmployerEmployer($employer);
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee */
        foreach ($employerHasEmployees as $employerHasEmployee) {
            $emp = $employerHasEmployee->getEmployeeEmployee();
            $emp->idContract = $employerHasEmployee->getContractByState(1);
            array_push($employees, $emp);
        }
        array_push($documents, $em->getRepository('RocketSellerTwoPickBundle:DocumentType')
                        ->findByName('Cedula')[0]);
        array_push($documents, $em->getRepository('RocketSellerTwoPickBundle:DocumentType')
                        ->findByName('Rut')[0]);
        array_push($documents, $em->getRepository('RocketSellerTwoPickBundle:DocumentType')
                        ->findByName('Contrato')[0]);
        array_push($documents, $em->getRepository('RocketSellerTwoPickBundle:DocumentType')
                        ->findByName('Carta autorización Symplifica')[0]);
        $documentsTypeByEmployer = $this->documentsTypeByEmployer($person);
        $documentsTypeByEmployee = $this->documentsTypeByEmployee($employees);
        return $this->render('RocketSellerTwoPickBundle:Employer:registrationDocuments.html.twig', array(
                    'employer' => $person,
                    'documents' => $documents,
                    'employees' => $employees,
                    'documentsTypeByEmployee' => $documentsTypeByEmployee,
                    'documentsTypeByEmployer' => $documentsTypeByEmployer
        ));
    }

    public function documentsTypeByEmployer($person)
    {
        $documentsTypeByEmployer = array();
        $docs = array();
        $em = $this->getDoctrine()->getManager();
        $docs = $em->getRepository('RocketSellerTwoPickBundle:Document')
                ->findByPersonPerson($person);
        foreach ($docs as $doc) {
            array_push($documentsTypeByEmployer, $doc->getDocumentTypeDocumentType());
        }
        return $documentsTypeByEmployer;
    }

    public function certificateAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $idEmployee = $this->get('request')->request->get('employee');
            $employee = $this->loadClassById($idEmployee, "Employee");
            $idCertificate = $this->get('request')->request->get('certificate');
            $person = $this->getUser()->getPersonPerson();
            $employer = $this->loadClassByArray(array("personPerson" => $person), "Employer");

            $em = $this->getDoctrine()->getManager();

            /* @var $EmployerHasEmployee EmployerHasEmployee */
            $EmployerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                    ->findOneBy(array('employerEmployer' => $employer, 'employeeEmployee' => $employee));

            //$contrato = $EmployerHasEmployee->getContractByState(1);

            $contrato = $em->getRepository('RocketSellerTwoPickBundle:Contract')
                    ->findOneBy(array('employerHasEmployeeEmployerHasEmployee' => $EmployerHasEmployee, 'state' => 1));

            switch ($idCertificate) {
                case '1':
                    $nameCertificate = "Certificado laboral";
                    $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoLaboral.html.twig', array(
                                'employee' => $employee,
                                'certificate' => $nameCertificate,
                                'employer' => $employer,
                                'contrato' => $contrato
                                    )
                            )->getContent();
                    break;
                case '2':
                    $nameCertificate = "Certificado de aportes";
                    $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                                'employee' => $employee,
                                'certificate' => $nameCertificate,
                                'employer' => $employer)
                            )->getContent();
                    break;
                case '3':
                    $nameCertificate = "Certificado de ingresos y retenciones";
                    $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                                'employee' => $employee,
                                'certificate' => $nameCertificate,
                                'employer' => $employer)
                            )->getContent();
                    break;
                default:
                    $nameCertificate = "Otro certificado";
                    $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                                'employee' => $employee,
                                'certificate' => $nameCertificate,
                                'employer' => $employer)
                            )->getContent();
                    break;
            }

            $objCertificate = array('id' => $idCertificate, 'name' => $nameCertificate);
            //echo $content;
            //return $this->generatePdf($content);

            return $this->render('RocketSellerTwoPickBundle:Employer:generatedCertificate.html.twig', array(
                        'employee' => $employee,
                        'certificate' => $objCertificate,
                        'employer' => $employer,
                        'content' => $content));
        } else {
            /** @var Person $person */
            $person = $this->getUser()->getPersonPerson();
            $employer = $person->getEmployer();
            $ehes=$employer->getEmployerHasEmployees();
            $arraytoSend=new ArrayCollection();
            /** @var EmployerHasEmployee $ehe */
            foreach ($ehes as $ehe) {
                if($ehe->getState()>=4){
                    $arraytoSend->add($ehe);
                }
            }
            return $this->render('RocketSellerTwoPickBundle:Employer:certificates.html.twig', array('employerHasEmployees' => $arraytoSend));
        }
    }

    public function generateCertificateAction($idEmployee, $idCertificate)
    {
        $employee = $this->loadClassById($idEmployee, "Employee");
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array("personPerson" => $person), "Employer");

        $em = $this->getDoctrine()->getManager();

        $EmployerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findBy(array('employerEmployer' => $employer, 'employeeEmployee' => $employee));

        $contrato = $em->getRepository('RocketSellerTwoPickBundle:Contract')
                ->findOneBy(array('employerHasEmployeeEmployerHasEmployee' => $EmployerHasEmployee, 'state' => 1));

        switch ($idCertificate) {
            case '1':
                $nameCertificate = "Certificado laboral";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoLaboral.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $nameCertificate,
                            'employer' => $employer,
                            'contrato' => $contrato
                                )
                        )->getContent();
                break;
            case '2':
                $nameCertificate = "Certificado de aportes";
                break;
            case '3':
                $nameCertificate = "Certificado de ingresos y retenciones";
                break;
            default:
                $nameCertificate = "Otro certificado";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $nameCertificate,
                            'employer' => $employer)
                        )->getContent();
                break;
        }

        $objCertificate = array('id' => $idCertificate, 'name' => $nameCertificate);
        //echo $content;
        //return $this->generatePdf($content);

        return $this->render('RocketSellerTwoPickBundle:Employer:generatedCertificate.html.twig', array(
                    'employee' => $employee,
                    'certificate' => $objCertificate,
                    'employer' => $employer,
                    'content' => $content));
    }

    public function viewDocumentsAction()
    {
        $documentTypeByEntity = array();
        $entityByEmployee = array();
        $documentTypeByEntityEmployer = array();
        $documentTypeAll = array();
        $employees = array();
        $result = array();
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array('personPerson' => $person), "Employer");
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
            if (!in_array($document->getDocumentTypeDocumentType(), $result)) {
                array_push($result, $document->getDocumentTypeDocumentType());
            }
        }
        $employerHasEntity = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEntity')
                ->findByEmployerEmployer($employer);
        foreach ($employerHasEntity as $entity) {
            $entitiesDocumentsEmployer = $em->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                    ->findByEntityEntity($entity->getEntityEntity());
            foreach ($entitiesDocumentsEmployer as $document) {
                array_push($documentTypeByEntityEmployer, $document);
            }
        }
        foreach ($documentTypeByEntityEmployer as $document) {
            if (!in_array($document->getDocumentTypeDocumentType(), $result)) {
                array_push($result, $document->getDocumentTypeDocumentType());
            }
        }
        //documentos que necesita por empleado
        $documentsPerEmployee = $this->fillArray($result, $entityByEmployee);
        //documentos que tiene por empleado
        $documentsByEmployee = $this->documentsTypeByEmployee($employees);
        return $this->render('RocketSellerTwoPickBundle:Employer:viewDocuments.html.twig', array('employer' => $employer, 'employees' => $employees, 'documentsByEmployee' => $documentsByEmployee, 'result' => $result, 'documentsPerEmployee' => $documentsPerEmployee));
    }

    //se traen los documento por empleado
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
                if ($document->getStatus()) {
                    array_push($docs, $document->getDocumentTypeDocumentType());
                }
            }
            array_push($documentsByEmployee, $docs);
            $docs = array();
        }
        return $documentsByEmployee;
    }

    //se eliminan los documentos repetidos por empleado
    public function removeDuplicated($employeeDocs)
    {
        $nonRepeated = array();
        $employeeDoc = array();
        foreach ($employeeDocs as $documents) {

            foreach ($documents as $document) {
                if (!in_array($document->getName(), $employeeDoc)) {
                    array_push($employeeDoc, $document);
                }
            }
            array_push($nonRepeated, $employeeDoc);
            $employeeDoc = array();
        }

        return $nonRepeated;
    }

    //se llenan los documentos que no necesita el empleado con respecto
    //a los documentos necesaris de las entidades
    public function fieldNotRequired($result, $documentsByEmployee)
    {
        $nonRepeated = array();
        $employeeDoc = array();
        foreach ($documentsByEmployee as $documents) {
            foreach ($result as $base) {
                if (in_array($base->getName(), $documents)) {
                    array_push($employeeDoc, $base);
                } else {
                    array_push($employeeDoc, '-');
                }
            }
            array_push($nonRepeated, $employeeDoc);
            $employeeDoc = array();
        }
        return $nonRepeated;
    }

    //se llena array con documentos y empleados
    public function fillArray($result, $entityByEmployee)
    {
        $filled = array();
        foreach ($entityByEmployee as $entityEmployee) {
            $docs = array();
            $employeeId = $entityEmployee->getEmployeeEmployee()->getIdEmployee();
            $empDocs = $this->employeeDocuments($entityEmployee);
            if (array_key_exists($employeeId, $filled)) {
                foreach ($result as $base) {
                    if (in_array($base->getName(), $empDocs)) {
                        array_push($filled[$employeeId], $base);
                    }
                }
            } else {
                $filled[$employeeId] = array();
                foreach ($result as $base) {
                    if (in_array($base->getName(), $empDocs)) {
                        array_push($filled[$employeeId], $base);
                    }
                }
            }
        }
        return $this->fieldNotRequired($result, $this->removeDuplicated($filled));
    }

    //se agregan los documentos por empleado
    public function employeeDocuments($entityEmployee)
    {
        $empDocs = array();
        foreach ($entityEmployee->getEntityEntity()->getEntityHasDocumentType() as $document) {
            array_push($empDocs, $document->getDocumentTypeDocumentType()->getName());
        }
        return $empDocs;
    }

    public function loadClassByArray($array, $entity)
    {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->findOneBy($array);
        return $loadedClass;
    }

    public function loadClassById($parameter, $entity)
    {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->find($parameter);
        return $loadedClass;
    }

    public function generatePdfAction(Request $request)
    {
        $idEmployee = $this->get('request')->request->get('idEmployee');
        $employee = $this->loadClassById($idEmployee, "Employee");
        $certificate = $this->get('request')->request->get('idCertificate');
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array("personPerson" => $person), "Employer");

        $em = $this->getDoctrine()->getManager();

        $EmployerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findBy(array('employerEmployer' => $employer, 'employeeEmployee' => $employee));

        $contrato = $em->getRepository('RocketSellerTwoPickBundle:Contract')
                ->findOneBy(array('employerHasEmployeeEmployerHasEmployee' => $EmployerHasEmployee, 'state' => 1));

        switch ($certificate) {
            case '1':
                $cert = "Certificado laboral";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoLaboral.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer,
                            'contrato' => $contrato
                                )
                        )->getContent();
                break;
            case '2':
                $cert = "Certificado de aportes";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer)
                        )->getContent();
                break;
            case '3':
                $cert = "Certificado de ingresos y retenciones";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer)
                        )->getContent();
                break;
            default:
                $cert = "Otro certificado";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer)
                        )->getContent();
                break;
        }

        $pdf = new PDF_HTML();
        $pdf->AddPage();
        $pdf->SetFont('Arial');
        $pdf->WriteHTML($content);

        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

    public function generatePdfGetAction($idEmployee, $idCertificate)
    {
        $employee = $this->loadClassById($idEmployee, "Employee");
        $person = $this->getUser()->getPersonPerson();
        $employer = $this->loadClassByArray(array("personPerson" => $person), "Employer");

        $em = $this->getDoctrine()->getManager();

        $EmployerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findBy(array('employerEmployer' => $employer, 'employeeEmployee' => $employee));

        $contrato = $em->getRepository('RocketSellerTwoPickBundle:Contract')
                ->findOneBy(array('employerHasEmployeeEmployerHasEmployee' => $EmployerHasEmployee, 'state' => 1));

        switch ($idCertificate) {
            case '1':
                $cert = "Certificado laboral";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoLaboral.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer,
                            'contrato' => $contrato
                                )
                        )->getContent();
                break;
            case '2':
                $cert = "Certificado de aportes";
                break;
            case '3':
                $cert = "Certificado de ingresos y retenciones";
                break;
            default:
                $cert = "Otro certificado";
                $content = $this->render('RocketSellerTwoPickBundle:Employer:certificadoDefault.html.twig', array(
                            'employee' => $employee,
                            'certificate' => $cert,
                            'employer' => $employer)
                        )->getContent();
                break;
        }

        $pdf = new PDF_HTML();
        $pdf->AddPage();
        $pdf->SetFont('Arial');
        $pdf->WriteHTML($content);

        return new Response($pdf->Output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

}
