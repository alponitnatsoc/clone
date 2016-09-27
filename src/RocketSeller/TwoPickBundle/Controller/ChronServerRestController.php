<?php
namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class ChronServerRestController extends FOSRestController
{

    public function __construct( $container)
    {
        $this->setContainer($container);
    }
    /**
     *  Charge Symplifica membership<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "retry pay pod",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putAutoChargeMembershipAction()
    {

        $users = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        $dateNow= new DateTime();
        $response= array();
        $productRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
        /** @var Product $PS1 */
        $PS1 = $productRepo->findOneBy(array("simpleName" => "PS1"));
        /** @var Product $PS2 */
        $PS2 = $productRepo->findOneBy(array("simpleName" => "PS2"));
        /** @var Product $PS3 */
        $PS3 = $productRepo->findOneBy(array("simpleName" => "PS3"));
        /** @var User $user */
        foreach ($users as $user) {
            $isFreeMonths = $user->getIsFree();
            if($user->getLastPayDate()==null)
                continue;
            if ($isFreeMonths > 0) {
                $isFreeMonths -= 1;
            }
            $isFreeMonths += 1;
            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($user->getLastPayDate()->format("Y-m-1")))));
            if($effectiveDate<=$dateNow){
                $ps1Count=$ps2Count=$ps3Count=0;
                $atLeastOne=false;
                $employees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                /** @var EmployerHasEmployee $employee */
                foreach ($employees as $employee) {
                    if($employee->getState()>=3){
                        $atLeastOne=true;
                        $contracts = $employee->getContracts();
                        $actualContract = null;
                        /** @var Contract $contract */
                        foreach ($contracts as $contract) {
                            if ($contract->getState() == 1) {
                                $actualContract = $contract;
                                break;
                            }
                        }
                        if ($actualContract == null) {
                            continue;
                        }
                        $actualDays = $actualContract->getWorkableDaysMonth();
                        if ($actualDays < 10) {
                            $ps1Count++;
                        } elseif ($actualDays <= 19) {
                            $ps2Count++;
                        } else {
                            $ps3Count++;
                        }
                    }
                }
                if($atLeastOne)
                    $response[]=$user->getPersonPerson()->getFullName();

            }

        }
        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData(array('response' => $response));

    }
}
