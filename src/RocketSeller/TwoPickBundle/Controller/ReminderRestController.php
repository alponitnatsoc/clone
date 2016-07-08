<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\DateTime;

class ReminderRestController extends FOSRestController
{
    /**
     * Reminder user emails.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Sends the reminder to add novelties.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error"
     *   }
     * )
     *
     * @return View
     */
    public function postReminderAction()
    {
        $response = 'Comienza: <br>';
//        $diaPago=intval(date("t"))-24;
        $diaPago = 8;
        $date = new \DateTime();

        $response = $response."-Dia del mes: ".$date->format('d').", Dia del recordatorio quincenal: 10, Dia del recordatorio mensual: ".$diaPago.'<br>';
        if($date->format('d') == $diaPago){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA MENSUAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts()){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll()){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send = $smailer->sendReminderPayEmailMessage($user,3);
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $response = $response."- - -ENVIO EL CORREO<br><br>";
                                        }else{
                                            $response = $response."- - -NO ENVIO EL CORREO<br><br>";
                                        }

                                    }else{
                                        $response = $response."- - NO ES EL PERIODO ACTIVO <br><br>";
                                    }
                                }else{
                                    $response = $response."- - NO TIENE UN PERIODO ACTIVO <br><br>";
                                }
                            }
                        }else{
                            $response = $response."- - NO TIENE CONTRATOS. <br><br>";
                        }
                    }
                }else{
                    $response = $response."- -NO ESTA APROBADO. <br><br>";
                }
            }
        }elseif($date->format('d') == 8){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA QUINCENAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts()){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll() and $contract->getActivePayroll()->getPeriod() == 2){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send= $smailer->sendReminderPayEmailMessage($user,2);
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $response = $response."- - -ENVIO EL CORREO<br><br>";
                                        }else{
                                            $response = $response."- - -NO ENVIO EL CORREO<br><br>";
                                        }
                                    }else{
                                        $response = $response."- - NO ES EL PERIODO ACTIVO <br><br>";
                                    }
                                }else{
                                    $response = $response."- - NO TIENE UN PERIODO ACTIVO O ES CONTRATO MENSUAL<br>";
                                }
                            }
                        }else{
                            $response = $response."- - NO TIENE CONTRATOS. <br><br>";
                        }
                    }
                }else{
                    $response = $response."- -NO ESTA APROBADO. <br><br>";
                }
            }
        }else{
            $response = $response."- NO DEBE EJECUTARSE LA TAREA AUN ".'<br><br>';
        }
        $response = $response."Termina".'<br>';

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;
    }

    /**
     * ReminderLastDay user emails.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send the reminder last day to add novelties.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error"
     *   }
     * )
     * 
     * @return View
     */
    public function postLastReminderAction()
    {
        $response = 'Comienza: <br>';
//        $diaPago=intval(date("t"))-24;
        $diaPago = 8;
        $date = new \DateTime();

        $response = $response."-Dia del mes: ".$date->format('d').", Dia del recordatorio quincenal: 12, Dia del recordatorio mensual: ".$diaPago.'<br>';
        if($date->format('d') == $diaPago){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA MENSUAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts()){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll()){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send = $smailer->sendLastReminderPayEmailMessage($user,3);
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $response = $response."- - -ENVIO EL CORREO<br><br>";
                                        }else{
                                            $response = $response."- - -NO ENVIO EL CORREO<br><br>";
                                        }

                                    }else{
                                        $response = $response."- - NO ES EL PERIODO ACTIVO <br><br>";
                                    }
                                }else{
                                    $response = $response."- - NO TIENE UN PERIODO ACTIVO <br><br>";
                                }
                            }
                        }else{
                            $response = $response."- - NO TIENE CONTRATOS. <br><br>";
                        }
                    }
                }else{
                    $response = $response."- -NO ESTA APROBADO. <br><br>";
                }
            }
        }elseif($date->format('d') == 8){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA QUINCENAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts()){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll() and $contract->getActivePayroll()->getPeriod() == 2){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send= $smailer->sendLastReminderPayEmailMessage($user,2);
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $response = $response."- - -ENVIO EL CORREO<br><br>";
                                        }else{
                                            $response = $response."- - -NO ENVIO EL CORREO<br><br>";
                                        }
                                    }else{
                                        $response = $response."- - NO ES EL PERIODO ACTIVO <br><br>";
                                    }
                                }else{
                                    $response = $response."- - NO TIENE UN PERIODO ACTIVO O ES CONTRATO MENSUAL<br>";
                                }
                            }
                        }else{
                            $response = $response."- - NO TIENE CONTRATOS. <br><br>";
                        }
                    }
                }else{
                    $response = $response."- -NO ESTA APROBADO. <br><br>";
                }
            }
        }else{
            $response = $response."- NO DEBE EJECUTARSE LA TAREA AUN ".'<br><br>';
        }
        $response = $response."Termina".'<br>';

        $view = View::create();
        $view->setData($response)->setStatusCode(200);
        return $view;


    }
}
 ?>