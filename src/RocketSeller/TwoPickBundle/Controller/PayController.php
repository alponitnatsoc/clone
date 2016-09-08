<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\PayType;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Form\PayMethod;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\Pay;
use JMS\Serializer\SerializationContext;

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\PayMethodsTrait;

class PayController extends Controller
{
    use SubscriptionMethodsTrait;
    use EmployerHasEmployeeMethodsTrait;
    use PayMethodsTrait;

    public function showPODDescriptionAction($idPOD,$notifRef){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        /** @var PurchaseOrdersDescription $realPod */
        $realPod = $podRepo->find($idPOD);
        if($this->getUser()->getId() == $realPod->getPurchaseOrders()->getIdUser()->getId()){
            if($notifRef!=-1){
                $notRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Notification");
                /** @var Notification $realNot */
                $realNot=$notRepo->find($notifRef);
                if($realNot!=null){
                    $em=$this->getDoctrine()->getManager();
                    $realNot->setStatus(0);
                    $em->persist($realNot);
                    $em->flush();
                }
            }
            return $this->render('RocketSellerTwoPickBundle:Pay:detailPOD.html.twig', array(
                "pod" => $realPod
            ));
        }
        return $this->redirectToRoute("show_dashboard");
    }
    public function showListPODDescriptionAction(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $answer = $this->forward('RocketSellerTwoPickBundle:PayRestSecured:getListPods', array("_format" => 'json'));
        if($answer->getStatusCode() != 200) {
            return redirectToRoute("dashboard");
        }
        $decodedAnswer = json_decode($answer->getContent(), true);
        $decodedAnswer = json_decode($decodedAnswer, true);

        return $this->render('RocketSellerTwoPickBundle:Pay:listPODS.html.twig', $decodedAnswer);
    }

    public function editPODDescriptionAction(Request $request,$idPOD){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        /** @var PurchaseOrdersDescription $realPod */
        $realPod = $podRepo->find($idPOD);
        if($this->getUser()->getId() == $realPod->getPurchaseOrders()->getIdUser()->getId()&&$realPod->getPurchaseOrdersStatus()->getIdNovoPay()=="-2"){
            /** @var User $user */
            $user = $this->getUser();
            $payMethod = $realPod->getPayMethod();
            /** @var Contract $contract */
            $contract = $realPod->getPayrollPayroll()->getContractContract();
            $fields = $payMethod->getPayTypePayType()->getPayMethodFields();
            $options = array();
            foreach ($fields as $field) {
                $options[] = $field;
            }
            if ($contract == null || $contract->getPayMethodPayMethod() == null) {
                $form = $this->createForm(new PayMethod($fields));
            }else {
                $form = $this->createForm(new PayMethod($fields), $contract->getPayMethodPayMethod());
            }
            $form
                ->add('verification', 'number', array(
                    'mapped' => false,
                    'required' => true,
                    'label' => "Codigo Mensaje de texto"))
                ->add('save','submit', array(
                    'label'=> 'Guardar'
                ));
            if($payMethod->getPayTypePayType()->getSimpleName()=="DAV")
                $form->remove("hasIt");

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $verifCode = intval($form->get("verification")->getData());
                if($user->getSmsCode()==$verifCode){
                    //then is secure to change the account
                    $em=$this->getDoctrine()->getManager();
                    $em->persist($payMethod);
                    $em->flush();
                    //$remove=$this->removeEmployeeToHighTech($contract->getEmployerHasEmployeeEmployerHasEmployee());
                    $adding=$this->addEmployeeToHighTech($contract->getEmployerHasEmployeeEmployerHasEmployee());
                    if($adding){
                        return $this->redirectToRoute("show_pod_description", array('idPOD'=>$realPod->getIdPurchaseOrdersDescription()));
                    }else{
                        return $this->redirectToRoute("edit_pod_description", array('idPOD'=>$realPod->getIdPurchaseOrdersDescription()));
                    }

                }
            }
            //send the code to the cellphone

            $this->sendVerificationCode();



            return $this->render('RocketSellerTwoPickBundle:Pay:editPOD.html.twig', array(
                'form' => $form->createView(),
                "pod" => $realPod
            ));
        }
        return $this->redirectToRoute("show_dashboard");
    }

    public function sendVerificationCode() {
        $this->forward('RocketSellerTwoPickBundle:PayRestSecured:getSendVerificationCode', array("_format" => 'json'));
    }

    public function addEmployeeToHighTechAction($idEmployerHasEmployee) {
      $employerHasEmployeeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");
      $employerHasEmployee = $employerHasEmployeeRepo->find($idEmployerHasEmployee);

      $adding = $this->addEmployeeToHighTech($employerHasEmployee);
      return $adding;
    }

    /**
     * @param Request $request
     * @param integer $id - Id el id de la relación entre empleado y empleador EmployerHasEmployee
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPaymentsAction(Request $request, $id)
    {
        $pagosRecibidos = $this->showPayments($id);
//         $em = $this->getDoctrine()->getManager();
//         $employeeRepository = $em->getRepository("RocketSellerTwoPickBundle:Employee");
//         /** @var Employee $employee */
//         $employee = $employeeRepository->findOneBy(
//             array(
//                 "idEmployee" => $id
//             )
//         );

//         /** @var User $user */
//         $user=$this->getUser();
//         $idUser = $user->getPersonPerson()->getEmployer()->getIdEmployer();

//         $employerHasEmployeeRepository = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee");

//         /** @var EmployerHasEmployee $relEmployerEmployee */
//         $relEmployerEmployee = $employerHasEmployeeRepository->findBy(
//             array(
//                 "employerEmployer" => $idUser,
//                 "employeeEmployee" => $id
//             )
//         );

//         $employeeHasEmployers = $employee->getEmployeeHasEmployers()->getValues();
//         /** @var EmployerHasEmployee $employeeHasEmployer */
//         /** @var EmployerHasEmployee $thisEmployeeHasEmployer */
//         foreach($employeeHasEmployers as $employeeHasEmployer) {
//             if ($employeeHasEmployer->getEmployerEmployer()->getIdEmployer() == $idUser) {
//                 $thisEmployeeHasEmployer = $employeeHasEmployer;
//                 break;
//             }
//         }

//         $contracts = $thisEmployeeHasEmployer->getContracts()->getValues();
//         /** @var Contract $contract */
//         foreach($contracts as $contract) {
//             if ($contract->getState() == "Active") {
//                 $payrolls = $contract->getPayrolls()->getValues();
//                 break;
//             }
//         }

//         $pagosRecibidos = array();
//         /** @var Payroll $payroll */
//         foreach($payrolls as $payroll) {
//             $purchaseOrders = $payroll->getPurchaseOrders()->getValues();
//             /** @var PurchaseOrders $po */
//             foreach($purchaseOrders as $key => $po) {
//                 if ($po->getPurchaseOrdersStatusPurchaseOrdersStatus()->getIdPurchaseOrdersStatus() == 1) {
//                     $pagosRecibidos[$key]["dateCreated"] = $po->getDateCreated();
//                     $pagosRecibidos[$key]["dateModified"] = $po->getDateModified();
//                     $pagosRecibidos[$key]["valor"] = $po->getValue();
//                     $idPO = $po->getIdPurchaseOrders();
//                     $payRepository = $em->getRepository("RocketSellerTwoPickBundle:Pay");
//                     /** @var Pay $pay */
//                     $pay = $payRepository->findOneBy(
//                         array(
//                             "purchaseOrdersPurchaseOrders" => $idPO
//                         )
//                     );
//                     $pagosRecibidos[$key]["idPay"] = $pay->getIdPay();
//                 }
//             }
//         }

        return $this->render('RocketSellerTwoPickBundle:Pay:show-payments.html.twig', array(
            "pagos" => $pagosRecibidos
        ));
    }

    /**
     * @param integer $id - Id del pago para mostrar su detalle
     */
    public function viewPayDetailAction($id)
    {
        $detail = $this->payDetail($id);
        return $this->render('RocketSellerTwoPickBundle:Pay:detail-pay.html.twig', array(
            "pay" => $detail
        ));
    }
}
