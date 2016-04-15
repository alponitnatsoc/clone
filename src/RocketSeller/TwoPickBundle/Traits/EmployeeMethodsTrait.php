<?php

namespace RocketSeller\TwoPickBundle\Traits;

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

    protected function validateDocuments(User $user)
    {
        $employerHasEmployees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployee = $employerHasEmployees->first();
        do {
            $employee = $employerHasEmployee->getEmployeeEmployee();
            $this->validateDocumentsEmployee($employee);
            $this->validateEntitiesEmployee($employee);
        } while ($employerHasEmployee = $employerHasEmployees->next());

        $this->validateDocumentsEmployer($employerHasEmployee->getEmployerEmployer());
        return true;
    }

    protected function validateDocumentsEmployee(Employee $realEmployee)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $person = $realEmployee->getPersonPerson();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);

        $docs = array('Cedula' => false, 'Contrato' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " . $person->getFullName();
                    $documentType = 'Cedula';
                } elseif ($type == 'Contrato') {
                    $msj = "Subir copia del contrato de " . $person->getFullName();
                    $documentType = 'Contrato';
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url);
            }
        }
    }

    protected function validateEntitiesEmployee(Employee $realEmployee)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $personEmployee = $realEmployee->getPersonPerson();
        $employeeHasEntityRepo = $em->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity');
        $entities_b = $employeeHasEntityRepo->findByEmployeeEmployee($realEmployee);
        if (gettype($entities_b) != "array") {
            $entities[] = $entities;
        } else {
            $entities = $entities_b;
        }
        //foreach ($entities as $key => $value) {
        $msj = "Subir documentos de " . $personEmployee->getFullName() . " para afiliarlo a las entidades.";
        $url = $this->generateUrl("show_documents", array('id' => $personEmployee->getIdPerson()));
        $this->createNotification($user->getPersonPerson(), $msj, $url);
        //}
    }

    protected function validateDocumentsEmployer(Employer $realEmployer)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $person = $realEmployer->getPersonPerson();
        $documentsRepo = $em->getRepository('RocketSellerTwoPickBundle:Document');
        $documents = $documentsRepo->findByPersonPerson($person);

        $docs = array('Cedula' => false, 'RUT' => false, 'Carta autorización Symplifica' => false);
        foreach ($docs as $type => $status) {
            foreach ($documents as $key => $document) {
                if ($type == $document->getDocumentTypeDocumentType()->getName()) {
                    $docs[$type] = true;
                    break;
                }
            }
            if (!$docs[$type]) {
                $msj = "";
                if ($type == 'Cedula') {
                    $msj = "Subir copia del documento de identidad de " . $person->getFullName();
                    $documentType = 'Cedula';
                } elseif ($type == 'RUT') {
                    $msj = "Subir copia del RUT de " . $person->getFullName();
                    $documentType = 'Contrato';
                } elseif ($type == 'Carta autorización Symplifica') {
                    $msj = "Subir carta de autorización symplifica de " . $person->getFullName();
                    $documentType = 'Carta autorización Symplifica';
                }
                $documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findByName($documentType)[0];
                $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                //$url = $this->generateUrl("api_public_post_doc_from");
                $this->createNotification($user->getPersonPerson(), $msj, $url);
            }
        }
    }

    protected function createNotification($person, $descripcion, $url, $action = "Subir")
    {
        $notification = new Notification();
        $notification->setPersonPerson($person);
        $notification->setStatus(1);
        $notification->setType('alert');
        $notification->setDescription($descripcion);
        $notification->setRelatedLink($url);
        $notification->setAccion($action);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
    }

}
