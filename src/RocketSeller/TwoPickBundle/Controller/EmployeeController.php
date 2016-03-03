<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EntityType;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\WeekWorkableDays;
use RocketSeller\TwoPickBundle\Form\AffiliationEmployerEmployee;
use RocketSeller\TwoPickBundle\Form\AfiliationEmployerEmployee;
use RocketSeller\TwoPickBundle\Form\EmployeeBeneficiaryRegistration;
use RocketSeller\TwoPickBundle\Form\PayMethod;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\EmployeeProfileEdit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

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
                    'entity' => $entity,
        ));
    }

    /**
     * Cambia el estado del contrato para activarlo o desactivarlo
     * @param string $id id del contrato
     * @return type
     */
    public function changeStateEmployeeAction($id)
    {
//$user = $this->getUser();
        /** @var Contract $contract */
        $employerEmployee = $this->getEmployerEmployee($id);
        $employerEmployee->setState(!$employerEmployee->getState());
        $em = $this->getDoctrine()->getManager();
        $em->persist($employerEmployee);
        $em->flush();
        return new JsonResponse(array('state' => ($employerEmployee->getState() ? 'Activo' : 'Inactivo')));
    }

    /**
     * 
     * @param string $id EmployerHasEmployee
     * @return EmployerHasEmployee
     */
    private function getEmployerEmployee($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee');
        $employerEmployee = $repository->find($id);
        return $employerEmployee;
    }

    /**
     * Maneja el registro de un beneficiario a un empleado con los datos básicos,
     * @param el Request que maneja el form que se imprime
     * @return La vista de el formulario de la nueva persona
     * */
    public function addBeneficiaryAction(Request $request, Employee $employee, Entity $entity = null)
    {
        if (is_null($entity)) {
            $entities = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                    ->findByEmployeeEmployee($employee);
            return $this->render(
                            'RocketSellerTwoPickBundle:Registration:addBeneficiarySelectEntity.html.twig', array(
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
                            'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig', array('form' => $form->createView())
            );
        }
    }

    /**
     * Maneja el registro de un beneficiario a un empleado con los datos básicos,
     * @param el Request que maneja el form que se imprime
     * @return La vista de el formulario de la nueva persona
     * */
    public function manageBeneficiaryAction(Request $request, Employee $employee, Beneficiary $beneficiary = null)
    {
        if (is_null($beneficiary)) {
            $beneficiaries = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
                    ->findByEmployeeEmployee($employee);

            return $this->render(
                            'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig', array(
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
                            'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig', array('form' => $form->createView())
            );
        }
    }

    private function getConfigData()
    {
        $configRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config");
        $configDataTmp = $configRepo->findAll();
        $configData = array();
        if ($configDataTmp) {
            foreach ($configDataTmp as $key => $value) {
                $configData[$value->getName()] = $value->getValue();
            }
        }
        return $configData;
    }

    public function matrixChooseAction($tab)
    {
        /** @var User $user */
        $user = $this->getUser();
        $person=$user->getPersonPerson();
        $employer = $user->getPersonPerson()->getEmployer();
        $employerHasEmployees = $employer->getEmployerHasEmployees();
        $entityTypeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EntityType");
        $entityTypes = $entityTypeRepo->findAll();
        $configData = $this->getConfigData();
        $pensions = null;
        $eps = null;
        $severances = null;
        $arls = null;


        /** @var EntityType $entityType */
        foreach ($entityTypes as $entityType) {
            if ($entityType->getName() == (isset($configData['EPS']) ? $configData['EPS'] : "EPS")) {
                $eps = $entityType->getEntities();
            }
            if ($entityType->getName() == (isset($configData['ARL']) ? $configData['ARL'] : "ARL")) {
                $arls = $entityType->getEntities();
            }
            if ($entityType->getName() == (isset($configData['Pension']) ? $configData['Pension'] : "Pension")) {
                $pensions = $entityType->getEntities();
            }
            if ($entityType->getName() == (isset($configData['CC Familiar']) ? $configData['CC Familiar'] : "CC Familiar")) {
                $severances = $entityType->getEntities();
            }
        }
        if ($employer->getEconomicalActivity() == null) {
            $employer->setEconomicalActivity(isset($configData['RUT Actividad Economica']) ? $configData['RUT Actividad Economica'] : "2435");
        }
        $form = $this->createForm(new AffiliationEmployerEmployee($eps, $pensions, $severances, $arls), $employer, array(
            'action' => $this->generateUrl('api_public_post_matrix_choose_submit'),
            'method' => 'POST',
        ));
        $employees = $form->get('employerHasEmployees');
        foreach ($employees as $employee) {
            /** @var EmployerHasEmployee $eHE */
            foreach ($employerHasEmployees as $eHE) {
                if ($eHE->getIdEmployerHasEmployee() == $employee->get('idEmployerHasEmployee')->getData()) {
                    $employee->get('nameEmployee')->setData($eHE->getEmployeeEmployee()->getPersonPerson()->getNames());
                    $eHEEntities = $eHE->getEmployeeEmployee()->getEntities();
                    if ($eHE->getEmployeeEmployee()->getAskBeneficiary()) {
                        $employee->get('beneficiaries')->setData($eHE->getEmployeeEmployee()->getAskBeneficiary());
                    } else {
                        $employee->get('beneficiaries')->setData('-1');
                    }
                    if ($eHEEntities->count() != 0) {
                        /** @var EmployeeHasEntity $enti */
                        foreach ($eHEEntities as $enti) {
                            if ($enti->getEntityEntity()->getEntityTypeEntityType()->getName() == "EPS") {
                                $employee->get('wealth')->setData($enti->getEntityEntity());
                            }
                            if ($enti->getEntityEntity()->getEntityTypeEntityType()->getName() == "Pension") {
                                $employee->get('pension')->setData($enti->getEntityEntity());
                            }
                        }
                    }
                    break;
                }
            }
        }
        $empEntities = $employer->getEntities();
        if ($empEntities->count() != 0) {
            /** @var EmployerHasEntity $enti */
            foreach ($empEntities as $enti) {
                if ($enti->getEntityEntity()->getEntityTypeEntityType()->getName() == "ARL") {
                    $form->get('arl')->setData($enti->getEntityEntity());
                }
                if ($enti->getEntityEntity()->getEntityTypeEntityType()->getName() == "CC Familiar") {
                    $form->get('severances')->setData($enti->getEntityEntity());
                }
            }
        }
        $personEmployer = $employer->getPersonPerson();
        $employerFullName = $personEmployer->getNames() . " " . $personEmployer->getLastName1() . " " . $personEmployer->getLastName2();

        return $this->render(
                        'RocketSellerTwoPickBundle:Registration:afiliation.html.twig', array(
                    'form' => $form->createView(),
                    'numberOfEmployees' => $employerHasEmployees->count(),
                    'employerName' => $employerFullName,
                    'employerDocument' => $personEmployer->getDocument(),
                    'employerDocumentType' => $personEmployer->getDocumentType(),
                    'tab' => $tab)
        );
    }

    /**
     * el dashboard de los empleados de cada empleador que le permite editar la información
     * y agregar nuevos empleados
     * TODO eliminar empleados
     * @return La vista de el formulario manager
     * */
    public function manageEmployeesAction()
    {
        $user = $this->getUser();
        $employeesData = $registerState = null;

        if ($user) {
            if ($user->getPersonPerson()->getEmployer()) {
                $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                $registerState = $user->getPersonPerson()->getEmployer()->getRegisterState();
                return $this->render(
                                'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig', array(
                            'employees' => $employeesData,
                            'user' => $user,
                            'registerState' => $registerState
                ));
            } else {
                return $this->redirectToRoute('ajax');
            }
        } else {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * Maneja el formulario de un nuevo empleado
     * @param el Request y el Id del empleado, si lo desean editar
     * @return La vista de el formulario de la nuevo empleado
     * */
    public function newEmployeeAction($id, $tab)
    {
        /** @var User $user */
        $user = $this->getUser();
        $employee = null;
        $employerHasEmployee = null;
        if ($id == -1) {
            $employee = new Employee();
        } else {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
            //verify if the Id exists or it belongs to the logged user
            /** @var Employee $employee */
            $employee = $repository->find($id);
            /** @var EmployerHasEmployee $ee */
            $idEmployer = $user->getPersonPerson()->getEmployer()->getIdEmployer();
            $flag = false;
            foreach ($employee->getEmployeeHasEmployers() as $ee) {
                if ($ee->getEmployerEmployer()->getIdEmployer() == $idEmployer) {
                    $employerHasEmployee = $ee;
                    $flag = true;
                    break;
                }
            }
            if ($employee == null || !$flag) {
                $employeesData = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                return $this->render(
                                'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig', array(
                            'employees' => $employeesData));
            }
        }
        $userWorkplaces = $user->getPersonPerson()->getEmployer()->getWorkplaces();
        $tempPerson = $employee->getPersonPerson();
        if ($tempPerson == null) {
            $tempPerson = new Person();
            $employee->setPersonPerson($tempPerson);
        }
        if ($tempPerson->getPhones()->count() == 0) {
            $tempPerson->addPhone(new Phone());
        }
        $form = $this->createForm(new PersonEmployeeRegistration($id, $userWorkplaces), $employee, array(
            'action' => $this->generateUrl('api_public_post_new_employee_submit'),
            'method' => 'POST',
        ));
        $todayPlus=new \DateTime();
        $todayPlus->setDate(intval($todayPlus->format("Y"))+1,$todayPlus->format("m"),$todayPlus->format("d"));
        $form->get('employeeHasEmployers')->get("startDate")->setData(new \DateTime());
        $form->get('employeeHasEmployers')->get("endDate")->setData($todayPlus);
        if ($employerHasEmployee != null) {
            $contracts = $employerHasEmployee->getContracts();
            if ($contracts->count() != 0) {
                $currentContract = null;
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    if ($contract->getState() == "Active")
                        $currentContract = $contract;
                }

                $form->get('employeeHasEmployers')->setData($currentContract);
                $payType = $contract->getPayMethodPayMethod();
                if ($payType != null) {
                    $form->get('employeeHasEmployers')->get("payMethod")->setData($contract->getPayMethodPayMethod()->getPayTypePayType());
                }
                $form->get('employeeHasEmployers')->get("weekWorkableDays")->setData($contract->getWorkableDaysMonth() / 4);
                if($contract->getWorkableDaysMonth()!=null)
                    $form->get('employeeHasEmployers')->get("salaryD")->setData(intval($contract->getSalary()/$contract->getWorkableDaysMonth()));
                $form->get('idContract')->setData($currentContract->getIdContract());
            }
        }
        $options = $form->get('employeeHasEmployers')->get('payMethod')->getConfig()->getOptions();
        $choices = $options['choice_list']->getChoices();
        return $this->render('RocketSellerTwoPickBundle:Registration:EmployeeForm.html.twig', array(
                    'form' => $form->createView(),
                    'tab' => $tab,
                    'choices' => $choices,
        ));
    }

    /**
     * Retorna los campos especificos para el metodo de pago solicitado
     *
     * @param $id
     * @return Response
     */
    public function postPayMethodAction($id, $idContract)
    {
        $repository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PayMethodFields");
        $payTypeRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PayType");
        $contractRepository = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Contract");
        /** @var PayType $payType */
        $payType = $payTypeRepository->find($id);
        /** @var Contract $contract */
        $contract = $contractRepository->find($idContract);
        $fields = $repository->findBy(array('payTypePayType' => $id));
        $options = array();
        foreach ($fields as $field) {
            $options[] = $field;
        }
        if ($contract == null || $contract->getPayMethodPayMethod() == null) {
            $form = $this->createForm(new PayMethod($fields));
        } else {
            $form = $this->createForm(new PayMethod($fields), $contract->getPayMethodPayMethod());
        }
        return $this->render(
                        'RocketSellerTwoPickBundle:Registration:payTypeFormRender.html.twig', array('form' => $form->createView(),
                    'payType' => $payType)
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
        if ($employee) {
            $beneficiaries = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployeeHasBeneficiary')
                    ->findByEmployeeEmployee($employee);
            if ($beneficiaries) {
                return $this->render(
                                'RocketSellerTwoPickBundle:Employee:employeeBeneficiary.html.twig', array('beneficiaries' => $beneficiaries,
                            'employee' => $employee
                ));
            } else {
                throw $this->createNotFoundException('Unable to find Beneficiaries.');
            }
        } else {
            
        }
    }

    public function loginAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {

            $document = $this->get('request')->request->get('document');
            $cellphone = $this->get('request')->request->get('cellphone');
            $lastName1 = $this->get('request')->request->get('lastName1');

            $em = $this->getDoctrine()->getManager();
            $person = $this->loadClassByArray(array('document' => $document, 'lastName1' => $lastName1), "Person");
            if (!$person) {
                throw $this->createNotFoundException('Unable to find Person.');
            }
            $phones = $person->getPhones();
            foreach ($phones as $phone) {
                if ($phone->getPhoneNumber() == $cellphone) {
                    $employee = $this->loadClassByArray(array('personPerson' => $person), "Employee");
                    $sendPhone = $phone;
                }
            }
            if ($employee) {
                $code = rand(100000, 999999);
                $employee->setTwoFactorCode($code);
                $twilio = $this->get('twilio.api');

                $twilio->account->messages->sendMessage(
                        "+19562671001", "+57" . $cellphone, "Hola este es tu codigo de autenticación: " . $code
                );
                $em->flush($employee);
                return $this->render('RocketSellerTwoPickBundle:Employee:loginEmployee2.html.twig', array('employee' => $employee, 'sendPhone' => $sendPhone)
                );
                return $this->redirect('employee_login_two_auth', array('employee' => $employee));
            } else {
                throw $this->createNotFoundException('Unable to find Employee.');
            }
        } else {
            return $this->render('RocketSellerTwoPickBundle:Employee:loginEmployee.html.twig');
        }
    }

    public function dashboardAction($id)
    {
        $employee = $this->loadClassById($id, "Employee");

        return $this->render('RocketSellerTwoPickBundle:Employee:dashboardEmployee.html.twig', array('employee' => $employee));
    }

    public function ProfileAction($id)
    {
        $employee = $this->loadClassById($id, "Employee");

        return $this->render('RocketSellerTwoPickBundle:Employee:profile.html.twig', array('employee' => $employee));
    }

    public function editProfileAction($idPerson, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($idPerson);
        $employee = $this->loadClassByArray(array("personPerson" => $person), "Employee");
        if (!$person) {
            throw $this->createNotFoundException(
                    'No news found for id ' . $id
            );
        }
        $form = $this->createForm(new EmployeeProfileEdit(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('employee_dashboard', array('id' => $employee->getIdEmployee()));
        }
        return $this->render(
                        'RocketSellerTwoPickBundle:Employee:editProfile.html.twig', array('form' => $form->createView())
        );
    }

    public function shareProfileAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $employee = $em->getRepository('RocketSellerTwoPickBundle:Employee')->find($id);

        if ($request->getMethod() == 'POST') {
            $invitationEmail = $this->get('request')->request->get('email');

            $smailer = $this->get('symplifica.mailer.twig_swift');
            $send = $smailer->sendEmail($this->getUser(), "FOSUserBundle:Invitation:email.txt.twig", "from.email@com.co", $toEmail);
        } else {
            return $this->render(
                            'RocketSellerTwoPickBundle:Employee:shareProfile.html.twig', array('employee' => $employee));
        }
    }

    public function generateCertificateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $employee = $em->getRepository('RocketSellerTwoPickBundle:Employee')->find($id);
        if ($request->getMethod() == 'POST') {
            $idEmployer = $this->get('request')->request->get('employer');
            $employer = $em->getRepository('RocketSellerTwoPickBundle:Employer')->find($idEmployer);
            $employerHasEmployee = $this->loadClassByArray(array(
                'employeeEmployee' => $employee,
                'employerEmployer' => $employer
                    ), 'employerHasEmployee');
            $contratos = $employerHasEmployee->getContracts();
            if ($contratos) {
                foreach ($contratos as $contrato) {
                    if ($contrato->getState()) {
                        $contract = $contrato;
                        $html = $this->renderView('RocketSellerTwoPickBundle:Certificates:laboralCertificate.html.twig', array(
                            'employee' => $employee,
                            'employer' => $employer,
                            'contract' => $contract
                        ));
                        return new Response(
                                $this->get('knp_snappy.pdf')->getOutputFromHtml($html), 200, array(
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment;
filename = "certificadoLaboral.pdf"'
                                )
                        );
                    }
                }
                throw $this->createNotFoundException('Unable to find contract active.');
            } else {
                throw $this->createNotFoundException('Unable to find contract.');
            }
        } else {
            return $this->render(
                            'RocketSellerTwoPickBundle:Employee:certificate.html.twig', array('employee' => $employee));
        }
    }

    public function twoFactorLoginAction($id, Request $request)
    {
        $employee = $this->loadClassById($id, "Employee");
        if ($request->getMethod() == 'POST') {
            $code = $this->get('request')->request->get('codigoTwo');
            $id = $request->query->get('id');
            if ($code == $employee->getTwoFactorCode()) {
                return $this->redirectToRoute('employee_dashboard', array('id' => $employee->getIdEmployee()));
            } else {
                throw $this->createNotFoundException('Unable to find employee code.');
            }
        }
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
            array_push($beneficiaries, $employeeHasBeneficiary->getBeneficiaryBeneficiary());
        }
        foreach ($documentTypeAll as $document) {
            if (!in_array($document->getDocumentTypeDocumentType(), $result)) {
                array_push($result, $document->getDocumentTypeDocumentType());
            }
        }
        $documentsPerBeneficiary = $this->fillArray($result, $employeeHasBeneficiaries);
        $documentsByBeneficiary = $this->documentsTypeByBeneficiary($beneficiaries);
        return $this->render('RocketSellerTwoPickBundle:Employee:beneficiaryDocuments.html.twig', array('beneficiaries' => $beneficiaries, 'result' => $result, 'documentsPerBeneficiary' => $documentsPerBeneficiary, 'documentsByBeneficiary' => $documentsByBeneficiary));
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
                array_push($docs, $document->getDocumentTypeDocumentType());
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
                if (!in_array($document->getName(), $beneficiaryDoc)) {
                    array_push($beneficiaryDoc, $document);
                }
            }
            array_push($nonRepeated, $beneficiaryDoc);
            $beneficiaryDocs = array();
        }

        return $nonRepeated;
    }

    //se llenan los documentos que no necesita el empleado con respecto
    //a los documentos necesaris de las entidades
    public function fieldNotRequired($result, $documentsByBeneficiary)
    {
        $nonRepeated = array();
        $beneficiaryDoc = array();
        foreach ($documentsByBeneficiary as $documents) {
            foreach ($result as $base) {
                if (in_array($base->getName(), $documents)) {
                    array_push($beneficiaryDoc, $base);
                } else {
                    array_push($beneficiaryDoc, '-');
                }
            }
            array_push($nonRepeated, $beneficiaryDoc);
            $beneficiaryDoc = array();
        }
        return $nonRepeated;
    }

    public function benefDocs($employeeHasBeneficiary)
    {
        $benefDocs = array();
        $em = $this->getDoctrine()->getManager();
        $entitiesHasDocumentType = $em->getRepository('RocketSellerTwoPickBundle:EntityHasDocumentType')
                ->findByEntityEntity($employeeHasBeneficiary->getEntityEntity());
        foreach ($entitiesHasDocumentType as $entityHasDocumentType) {
            array_push($benefDocs, $entityHasDocumentType->getDocumentTypeDocumentType());
        }
        return $benefDocs;
    }

    public function fillArray($result, $employeeHasBeneficiaries)
    {
        $filled = array();
        foreach ($employeeHasBeneficiaries as $employeeHasBeneficiary) {
            $docs = array();
            $beneficiaryId = $employeeHasBeneficiary->getBeneficiaryBeneficiary()->getIdBeneficiary();
            $benefDocs = $this->benefDocs($employeeHasBeneficiary);
            if (array_key_exists($beneficiaryId, $filled)) {
                foreach ($result as $base) {
                    if (in_array($base->getName(), $benefDocs)) {
                        array_push($filled[$employeeId], $base);
                    }
                }
            } else {
                $filled[$beneficiaryId] = array();
                foreach ($result as $base) {
                    if (in_array($base->getName(), $benefDocs)) {
                        array_push($filled[$beneficiaryId], $base);
                    }
                }
            }
        }
        return $this->fieldNotRequired($result, $this->removeDuplicated($filled));
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
        $employee = $this->loadClassByArray(array('personPerson' => $person), 'Employee');
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
                array_push($documentTypeByEmployee, $entityHasDocumentType->getDocumentTypeDocumentType());
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
                array_push($keys, array_search($document, $documentsPerEmployee));
            }
        }
        return $this->render('RocketSellerTwoPickBundle:Employee:documents.html.twig', array('documentTypeByEmployee' => $documentTypeByEmployee, 'employee' => $employee, 'documentsPerEmployee' => $documentsPerEmployee, 'keys' => $keys));
    }

    public function simpleRemoveDuplicated($array)
    {
        $docs = array();
        foreach ($array as $doc) {
            if (!in_array($doc, $docs)) {
                array_push($docs, $doc);
            }
        }
        return $docs;
    }

}
