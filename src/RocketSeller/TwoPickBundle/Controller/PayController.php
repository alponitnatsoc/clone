<?php
namespace RocketSeller\TwoPickBundle\Controller;

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

use RocketSeller\TwoPickBundle\Traits\EmployerHasEmployeeMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\PayMethodsTrait;

class PayController extends Controller
{

    use EmployerHasEmployeeMethodsTrait;
    use PayMethodsTrait;

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