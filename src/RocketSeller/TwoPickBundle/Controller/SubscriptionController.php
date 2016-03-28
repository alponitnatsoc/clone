<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{

    private function findPriceByNumDays($productos, $days)
    {
        if ($days > 0 && $days <= 10) {
            $key = 'PS1';
        } elseif ($days >= 11 && $days <= 19) {
            $key = 'PS2';
        } elseif ($days >= 20) {
            $key = 'PS3';
        } else {
            $key = 'PS3';
        }
        foreach ($productos as $value) {
            if ($value->getSimpleName() == $key) {
                return $value;
            }
        }
    }

    /**
     * 
     * @param Employer $employer Empleador del cual se buscaran los empleados
     * @param boolean $activeEmployee buscar empleados solo activos = true, default=false para buscarlos todos
     * @return boolean
     */
    public function getData(Employer $employer, $activeEmployee = false)
    {
        $config = $this->getConfigData();

        /* @var $employerHasEmployee EmployerHasEmployee */
        $employerHasEmployee = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                ->findByEmployerEmployer($employer);

        $productos = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Product')
                ->findBy(array('simpleName' => array('PS1', 'PS2', 'PS3')));

        $employees = array();
        $total_sin_descuentos = $total_con_descuentos = $valor_descuento_3er = $valor_descuento_isRefered = $valor_descuento_haveRefered = 0;
        $descuento_3er = isset($config['D3E']) ? $config['D3E'] : 0.1;
        $descuento_isRefered = isset($config['DIR']) ? $config['DIR'] : 0.2;
        $descuento_haveRefered = isset($config['DHR']) ? $config['DHR'] : 0.2;
        foreach ($employerHasEmployee as $keyEmployee => $employee) {
            if ($activeEmployee) {
                if ($employee->getState() == 0) {
                    continue;
                }
            }

            $contracts = $employee->getContractByState(true);

            foreach ($contracts as $keyContract => $contract) {
                $employees[$keyEmployee]['contrato'] = $contract;
                $employees[$keyEmployee]['employee'] = $employee;
                $employees[$keyEmployee]['product']['object'] = $this->findPriceByNumDays($productos, ($contract->getWorkableDaysMonth()));
                //if ($employee->getIsFree() > 0) {
                $employees[$keyEmployee]['product']['price'] = 0;
                // } else {
                $employees[$keyEmployee]['product']['price'] = $employees[$keyEmployee]['product']['object']->getPrice();
                // }
                $total_sin_descuentos += $employees[$keyEmployee]['product']['price'];
                break;
            }
        }
        if (count($employees) >= 3) {
            $valor_descuento_3er = round($total_sin_descuentos * $descuento_3er);
            $employees = $this->updateProductPrice($employees, $descuento_3er);
        }
        $userIsRefered = $this->userIsRefered();
        if ($userIsRefered) {
            $valor_descuento_isRefered = round($total_sin_descuentos * $descuento_isRefered);
            $employees = $this->updateProductPrice($employees, $descuento_isRefered);
        }
        $userHaveValidRefered = $this->userHaveValidRefered();
        if ($userHaveValidRefered) {
            $valor_descuento_haveRefered = round($total_sin_descuentos * (count($userHaveValidRefered) * $descuento_haveRefered));
            $employees = $this->updateProductPrice($employees, $descuento_haveRefered);
        }

        if (($valor_descuento_3er + $valor_descuento_isRefered + $valor_descuento_haveRefered) > $total_sin_descuentos) {
            $total_con_descuentos = 0;
        } else {
            $total_con_descuentos = $total_sin_descuentos - ($valor_descuento_3er + $valor_descuento_isRefered + $valor_descuento_haveRefered); //descuentos antes de iva            
        }
        return array(
            'employees' => $employees,
            'productos' => $productos,
            'total_sin_descuentos' => $total_sin_descuentos,
            'total_con_descuentos' => $total_con_descuentos,
            'descuento_3er' => array('percent' => $descuento_3er, 'value' => $valor_descuento_3er),
            'descuento_isRefered' => array('percent' => $descuento_isRefered, 'value' => $valor_descuento_isRefered, 'object' => $userIsRefered),
            'descuento_haveRefered' => array('percent' => $descuento_haveRefered, 'value' => $valor_descuento_haveRefered, 'object' => $userHaveValidRefered)
        );
    }

    private function updateProductPrice($employees, $descuentoPercent)
    {
        foreach ($employees as $key => $employe) {
            $employees[$key]['product']['price'] = $employe['product']['price'] - ($employe['product']['price'] * $descuentoPercent);
        }
        return $employees;
    }

    public function addToNovo()
    {
        /* @var $user User */
        $user = $this->getUser();
        /* @var $person Person */
        $person = $user->getPersonPerson();
        /* @var $employer Employer */
        $employer = $person->getEmployer();

        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "name" => $person->getNames(),
            "lastName" => $person->getLastName1() . " " . $person->getLastName2(),
            "year" => $person->getBirthDate()->format("Y"),
            "month" => $person->getBirthDate()->format("m"),
            "day" => $person->getBirthDate()->format("d"),
            "phone" => $person->getPhones()->get(0)->getPhoneNumber(),
            "email" => $user->getEmail()
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClient', array('_format' => 'json'));
        //dump($insertionAnswer);
        //echo "Status Code Employer: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode();

        if ($insertionAnswer->getStatusCode() == 406 || $insertionAnswer->getStatusCode() == 201) {
            $eHEes = $employer->getEmployerHasEmployees();
            //dump($eHEes);
            /** @var EmployerHasEmployee $employeeC */
            foreach ($eHEes as $employeeC) {
                //dump($employeeC);
                if ($employeeC->getState() > 0) {
                    //check if it exist

                    $contracts = $employeeC->getContracts();
                    /** @var Contract $cont */
                    $contract = null;
                    foreach ($contracts as $cont) {
                        if ($cont->getState() == 1) {
                            $contract = $cont;
                        }
                    }

                    /* @var $payMC PayMethod */
                    $payMC = $contract->getPayMethodPayMethod();

                    /* @var $payType PayType */
                    $payType = $payMC->getPayTypePayType();

                    if ($payType->getPayrollCode() != 'EFE') {
                        $paymentMethodId = $payMC->getAccountTypeAccountType();
                        if ($paymentMethodId) {
                            $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? 4 :
                                    $payMC->getAccountTypeAccountType()->getName() == "Corriente" ? 5 : 6;
                        }
                        $paymentMethodAN = $payMC->getAccountNumber() == null ? $payMC->getCellPhone() : $payMC->getAccountNumber();
                        $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
                        $request->setMethod("POST");
                        $request->request->add(array(
                            "documentType" => $employeePerson->getDocumentType(),
                            "beneficiaryId" => $employeePerson->getDocument(),
                            "documentNumber" => $person->getDocument(),
                            "name" => $employeePerson->getNames(),
                            "lastName" => $employeePerson->getLastName1() . " " . $employeePerson->getLastName2(),
                            "yearBirth" => $employeePerson->getBirthDate()->format("Y"),
                            "monthBirth" => $employeePerson->getBirthDate()->format("m"),
                            "dayBirth" => $employeePerson->getBirthDate()->format("d"),
                            "phone" => $employeePerson->getPhones()->get(0)->getPhoneNumber(),
                            "email" => $employeePerson->getEmail() == null ? $employeePerson->getDocumentType() . $person->getDocument() .
                                    "@" . $employeePerson->getNames() . ".com" : $employeePerson->getEmail(),
                            "companyId" => $person->getDocument(), //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                            "companyBranch" => "0", //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                            "paymentMethodId" => $paymentMethodId,
                            "paymentAccountNumber" => $paymentMethodAN,
                            "paymentBankNumber" => 0, //THIS SHOULD HAVE THE NOVO ID BANK TABLE
                            "paymentType" => $payMC->getAccountTypeAccountType() ? $payMC->getAccountTypeAccountType()->getName() : null,
                        ));
                        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postBeneficiary', array('_format' => 'json'));
                        if (!($insertionAnswer->getStatusCode() == 201 || $insertionAnswer->getStatusCode() == 406)) {
                            $this->addFlash('error', $insertionAnswer->getContent());
                            return false;
                        }
                        //dump($insertionAnswer);
                        //echo "Status Code Employee: " . $employeePerson->getNames() . " -> " . $insertionAnswer->getStatusCode() . " content" . $insertionAnswer->getContent();
                    }
                }
            }
        } else {
            $this->addFlash('error', $insertionAnswer->getContent());
            return false;
        }
        return true;
    }

    public function subscriptionChoicesAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        /* @var $user User */
        $user = $this->getUser();

        $responce = $this->forward('RocketSellerTwoPickBundle:EmployerRest:setEmployeesFree', array(
            'idEmployer' => $this->getUser()->getPersonPerson()->getEmployer()->getIdEmployer(),
            'freeTime' => 1,
            'all' => true
                ), array('_format' => 'json')
        );

        $data = $this->getData($user->getPersonPerson()->getEmployer(), false);
        $date = new \DateTime();
        $date->add(new \DateInterval('P1M'));
        $startDate = $date->format('Y-m-d');

        //dump($data);
        //die;
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionChoices.html.twig', array(
                    'employees' => $data['employees'],
                    'productos' => $data['productos'], //$this->orderProducts($employees['productos']),
                    'total' => $data['total_sin_descuentos'],
                    'total_sin_descuentos' => $data['total_sin_descuentos'],
                    'descuento_3er' => $data['descuento_3er'],
                    'descuento_isRefered' => $data['descuento_isRefered'],
                    'total_con_descuentos' => $data['total_con_descuentos'],
                    'user' => $user,
                    'descuento_haveRefered' => $data['descuento_haveRefered'],
                    'startDate' => $startDate
        ));
    }

    public function suscripcionConfirmAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {


            $user = $this->getUser();
            $person = $user->getPersonPerson();
            $billingAdress = $person->getBillingAddress();
            $data = $this->getData($user->getPersonPerson()->getEmployer(), true);

            //dump($data);
            $date = new \DateTime();
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');
            $form = $this->createForm(new PagoMembresiaForm(), new BillingAddress(), array(
                'action' => $this->generateUrl('subscription_pay'),
                'method' => 'POST',
            ));

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionConfirm.html.twig', array(
                        'form' => $form->createView(),
                        'employer' => $person,
                        'employees' => $data['employees'],
                        'billingAdress' => $billingAdress,
                        'total_sin_descuentos' => $data['total_sin_descuentos'],
                        'total_con_descuentos' => $data['total_con_descuentos'],
                        'descuento_3er' => $data['descuento_3er'],
                        'descuento_isRefered' => $data['descuento_isRefered'],
                        'descuento_haveRefered' => $data['descuento_haveRefered'],
                        'startDate' => $startDate
            ));
        } else {
            return $this->redirectToRoute("subscription_choices");
        }
    }

    public function suscripcionPayAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            if ($this->addToNovo()) {
                $request = new Request();
                $request->setMethod('POST');
                $request->request->set('credit_card', $request->get('credit_card'));
                $request->request->set('expiry_date_year', $request->get('expiry_date_year'));
                $request->request->set('expiry_date_month', $request->get('expiry_date_month'));
                $request->request->set('cvv', $request->get('cvv'));
                $request->request->set('name_on_card', $request->get('name_on_card'));
                $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('request' => $request), array('_format' => 'json'));
                if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                    $this->addFlash('error', $postAddCreditCard->getContent());
                    return $this->redirectToRoute("subscription_error");
                    //throw $this->createNotFoundException($data->getContent());
                } else {
                    $data = $this->getData($this->getUser()->getPersonPerson()->getEmployer(), true);
                    //dump($data);
                    //die;
                    $methodId = json_decode($postAddCreditCard->getContent(), true);

                    $purchaseOrdersStatusRepo = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:PurchaseOrdersStatus');
                    /** @var $purchaseOrdersStatus PurchaseOrdersStatus */
                    $purchaseOrdersStatus = $purchaseOrdersStatusRepo->findOneBy(array('name' => 'Pendiente'));

                    $purchaseOrder = new PurchaseOrders();
                    $purchaseOrder->setIdUser($this->getUser());
                    $purchaseOrder->setName('Pago Membresia');
                    $purchaseOrder->setValue($data['total_con_descuentos']);
                    $purchaseOrder->setPurchaseOrdersStatus($purchaseOrdersStatus);
                    $purchaseOrder->setPayMethodId(isset($methodId['response']['method-id']) ? $methodId['response']['method-id'] : null);

                    foreach ($data['employees'] as $key => $employee) {
                        $purchaseOrderDescription = new PurchaseOrdersDescription();
                        $purchaseOrderDescription->setDescription("Pago Membresia");
                        $purchaseOrderDescription->setPurchaseOrders($purchaseOrder);
                        $purchaseOrderDescription->setPurchaseOrdersStatus($purchaseOrdersStatus);
                        $purchaseOrderDescription->setValue($employee['product']['price']);
                        $purchaseOrderDescription->setProductProduct($employee['product']['object']);
                        $purchaseOrder->addPurchaseOrderDescription($purchaseOrderDescription);
                    }

                    $em->persist($purchaseOrderDescription);
                    $em->persist($purchaseOrder);
                    $em->flush(); //para obtener el id que se debe enviar a novopay

                    $responce = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array("idPurchaseOrder" => $purchaseOrder->getIdPurchaseOrders()), array('_format' => 'json'));
                    //dump($responce);
                    //die;
                    $data = json_decode($responce->getContent(), true);
                    if ($responce->getStatusCode() == Response::HTTP_OK) {
                        $this->addFlash('success', $data);
                        //dump($data);                        
                        //die;
                        $this->sendEmailPaySuccessAction();

                        /* @var $user User */
                        $user = $this->getUser();
                        $user->setPaymentState(1);
                        $user->setDayToPay(\date('d'));
                        $em->persist($user);
                        $em->flush();

                        return $this->redirectToRoute("subscription_success");
                    }
                    $this->addFlash('error', $responce->getContent());
                    return $this->redirectToRoute("subscription_error");
                }
            } else {
                $this->addFlash('error', 'Error al insertar en novopayment');
                return $this->redirectToRoute("subscription_error");
            }
        } else {
            return $this->redirectToRoute("subscription_choices");
        }
    }

    public function suscripcionSuccessAction(Request $request)
    {
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionSuccess.html.twig', array(
                    'user' => $this->getUser(),
                    'date' => \date('Y-m-d')
        ));
    }

    public function suscripcionErrorAction(Request $request)
    {
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionError.html.twig', array(
                    'user' => $this->getUser()
        ));
    }

    /**
     * Buscar si el usuario fue referido por alguien
     * @param User $user
     * @return Referred
     */
    private function userIsRefered(User $user = null)
    {
        /* @var $isRefered Referred */
        $isRefered = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Referred')
                ->findOneBy(array('referredUserId' => $user != null ? $user : $this->getUser(), 'status' => 0));

        if ($isRefered && $isRefered->getUserId()->getPaymentState() > 0) {
            return $isRefered;
        }
        return null;
    }

    /**
     * Buscar referidos que tiene el usuario y que ya tengan subscripcion 
     * @param User $user
     * @return array
     */
    private function userHaveValidRefered(User $user = null)
    {
        /* @var $haveRefered Referred */
        $haveRefered = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Referred')
                ->findBy(array('userId' => $user != null ? $user : $this->getUser(), 'status' => 0));
        $responce = array();
        foreach ($haveRefered as $key => $referedUser) {
            if ($referedUser->getReferredUserId()->getPaymentState() > 0) {
                array_push($responce, $referedUser);
            }
        }
        return $responce;
    }

    public function suscripcionInactivaAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

    private function getConfigData()
    {
        #$configRepo = new Config();
        $configRepo = $this->getdoctrine()->getRepository("RocketSellerTwoPickBundle:Config");
        $configDataTmp = $configRepo->findAll();
        $configData = array();
        if ($configDataTmp) {
            foreach ($configDataTmp as $key => $value) {
                $configData[$value->getName()] = $value->getValue();
            }
        }
        return $configData;
    }

    private function sendEmailPaySuccessAction()
    {
        $toEmail = $this->getUser()->getEmail();
        $fromEmail = "servicioalcliente@symplifica.com";
        $tsm = $this->get('symplifica.mailer.twig_swift');
        $response = $tsm->sendEmail($this->getUser(), "RocketSellerTwoPickBundle:Subscription:paySuccess.txt.twig", $fromEmail, $toEmail);
        return $response;
    }

}
