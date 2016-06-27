<?php

namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Controller\UtilsController;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Notification;

trait EmployeeMethodsTrait
{

    protected function getEmployeeDetails($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        $employee = $repository->find($id);

        return $employee;
    }

    protected function getEmployeeEps($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "EPS") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function getEmployeeAfp($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "AFP") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function getEmployeeFces($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
        /** @var \RocketSeller\TwoPickBundle\Entity\Employee $employee */
        $employee = $repository->find($id);
        $entities = $employee->getEntities();
        /** @var \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity */
        foreach ($entities as $entity) {
            if ($entity->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode() == "FCES") {
                return $entity->getEntityEntity();
            }
        }

        return null;
    }

    protected function validateDocuments(User $user)
    {
        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        $employerHasEmployee = $employerHasEmployees->first();
        $this->validateDocumentsEmployer($user, $employerHasEmployee->getEmployerEmployer());
        do {
            if ($employerHasEmployee->getState() < 2)
                continue;
            $employee = $employerHasEmployee->getEmployeeEmployee();
            $this->validateDocumentsEmployee($user, $employee);
            //$this->validateEntitiesEmployee($user, $employee);
        } while ($employerHasEmployee = $employerHasEmployees->next());

        return true;
    }

    protected function validateDocumentsEmployee(User $user, Employee $realEmployee)
    {
        // = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $employer = $user->getPersonPerson()->getEmployer();
        $person = $realEmployee->getPersonPerson();

        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->findOneBy(array(
            'employerEmployer' => $employer,
            'employeeEmployee' => $realEmployee,
            'state' => 3
        ));
        /** @var Contract $contract */
        $contract = $em->getRepository('RocketSellerTwoPickBundle:Contract')->findOneBy(array(
            'employerHasEmployeeEmployerHasEmployee' => $employerHasEmployee,
            'state' => 3
        ));
        $utils = $this->get('app.symplifica_utils');
        $docs = array('Cedula' => false, 'Contrato' => false,'Carta autorización Symplifica'=>false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            // {{ path('download_document', {'id': employees[0].personPerson.idPerson , 'idDocument':doc.idDocument}) }}
            if (!$docs[$type]) {
                $msj = "";
                $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
                $dAction=null;
                $nAction="Subir";
                $dUrl=null;
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'Cedula';
                } elseif ($type == 'Contrato') {
                    $documentType = 'Contrato';
                    if($employerHasEmployee->getLegalFF()==1){
                        $msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    }else{
                        $msj = "Contrato de ". $utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                        $nAction="Ver";
                    }
//                    $documentType = 'Contrato';
//                    $dAction="Bajar";
//                    $dUrl = $this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));

                } elseif ($type == 'Carta autorización Symplifica') {
                    $documentType = 'Carta autorización Symplifica';
                    $msj = "Subir copia de la Carta autorización Symplifica de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $dAction="Bajar";
                    $dUrl = $this->generateUrl("download_documents", array('id' => $employerHasEmployee->getIdEmployerHasEmployee(), 'ref' => "aut-afiliacion-ss", 'type' => 'pdf'));
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                if($nAction=="Ver"){
                    $url = $this->generateUrl("view_document_contract_state");
                }
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url, $documentType,$nAction,$dAction,$dUrl);
            }
        }
    }

    protected function validateEntitiesEmployee(User $user, Employee $realEmployee)
    {
        //$user = $this->getUser();
        //$em = $this->getDoctrine()->getManager();
        $personEmployee = $realEmployee->getPersonPerson();
        //$employeeHasEntityRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity');
        //$entities_b = $employeeHasEntityRepo->findByEmployeeEmployee($realEmployee);
        //if (gettype($entities_b) != "array") {
        //    $entities[] = $entities_b;
        //} else {
        //    $entities = $entities_b;
        //}
        //foreach ($entities as $key => $value) {
        $msj = "Subir documentos de " .explode(" ",$personEmployee->getNames())[0]." ". $personEmployee->getLastName1(). " para afiliarlo a las entidades.";
        $url = $this->generateUrl("show_documents", array('id' => $personEmployee->getIdPerson()));
        $this->createNotification($user->getPersonPerson(), $msj, $url, null, "Ir");
        //}
    }

    protected function validateDocumentsEmployer(User $user, Employer $realEmployer)
    {
        //$user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $person = $realEmployer->getPersonPerson();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');
        $docs = array('Cedula' => false, 'RUT' => false, 'Mandato' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
                $dAction=null;
                $dUrl=null;
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'Cedula';
                } elseif ($type == 'RUT') {
                    $msj = "Subir copia del RUT de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                    $documentType = 'RUT';
                } elseif ($type == 'Mandato'){
                  $documentType = 'Mandato';
                  $msj = "Subir mandato firmado de " .$utils->mb_capitalize(explode(" ",$person->getNames())[0]." ". $person->getLastName1());
                  $dUrl = $this->generateUrl("download_documents", array('id' => $person->getIdPerson(), 'ref' => "mandato", 'type' => 'pdf'));
                  $dAction="Bajar";
                }
                $documentType = $documentTypeRepo->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url, $documentType,"Subir",$dAction,$dUrl);
            }
        }
    }

    protected function createNotification($person, $descripcion, $url, $documentType = null, $action = "Subir",$dAction=null,$dUrl=null)
    {
        $notification = new Notification();
        $notification->setPersonPerson($person);
        $notification->setStatus(1);
        $notification->setDocumentTypeDocumentType($documentType);
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $notification->setDownloadAction($dAction);
        $notification->setDownloadLink($dUrl);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
    }

}
