<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
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
        $diaPago = 22;
        $date = new \DateTime();
        $response = $response."-Dia del mes: ".$date->format('d').", Dia del recordatorio quincenal: 10, Dia del recordatorio mensual: ".$diaPago.'<br>';
        if($date->format('d') == $diaPago){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA MENSUAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    $enviado = false;
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts()and!$enviado){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll()){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send = $smailer->sendEmailByTypeMessage(array('emailType'=>'reminderPay','toEmail'=>$user->getEmail(),'userName'=>$user->getPersonPerson()->getFullName(),'days'=>3));
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $enviado=true;
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
        }elseif($date->format('d') == 10){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA QUINCENAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    $enviado = false;
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts() and !$enviado){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll() and $contract->getActivePayroll()->getPeriod() == 2 and !$enviado){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send=$smailer->sendEmailByTypeMessage(array('emailType'=>'reminderPay','toEmail'=>$user->getEmail(),'userName'=>$user->getPersonPerson()->getFullName(),'days'=>2));
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $enviado=true;
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
        $diaPago = 25;
        $date = new \DateTime();

        $response = $response."-Dia del mes: ".$date->format('d').", Dia del recordatorio quincenal: 12, Dia del recordatorio mensual: ".$diaPago.'<br>';
        if($date->format('d') == $diaPago){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA MENSUAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    $enviado=false;
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts() and !$enviado){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll()){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send=$smailer->sendEmailByTypeMessage(array('emailType'=>'lastReminderPay','toEmail'=>$user->getEmail(),'userName'=>$user->getPersonPerson()->getFullName(),'days'=>3));
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $enviado=true;
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
        }elseif($date->format('d') == 12){
            $users = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
            $response = $response."- SE EJECUTA LA TAREA QUINCENAL".'<br><br>';
            /** @var User $user */
            foreach ($users as $user){
                $response = $response.'- -Usuario: '.$user->getPersonPerson()->getFullName().'<br>';
                if($user->getStatus() == 2){
                    $enviado = false;
                    /** @var EmployerHasEmployee $eHE */
                    foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                        $response = $response."- - Empleado: ".$eHE->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br>";
                        if($eHE->getContracts() and !$enviado){
                            /** @var Contract $contract */
                            foreach ($eHE->getContracts() as $contract){
                                if($contract->getActivePayroll() and $contract->getActivePayroll()->getPeriod() == 2){
                                    if($contract->getActivePayroll()->getMonth()==$date->format('m')){
                                        $smailer = $this->get('symplifica.mailer.twig_swift');
                                        $send=$smailer->sendEmailByTypeMessage(array('emailType'=>'lastReminderPay','toEmail'=>$user->getEmail(),'userName'=>$user->getPersonPerson()->getFullName(),'days'=>2));
                                        $response = $response."- - -Periodo activo: ".$contract->getActivePayroll()->getMonth().' '.$contract->getActivePayroll()->getYear()."<br>";
                                        if($send){
                                            $enviado=true;
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
     * Reminder daviplata.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send the reminder to create daviplata if not created.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error"
     *   }
     * )
     *
     * @return View
     */
    public function postReminderDaviplataAction()
    {
        $response = 'Comienza: <br>';
        $diaQ = 13;
        $diaM = 25;
        $date = new \DateTime();
        $response = $response."-Dia del mes: ".$date->format('d').", Dia del recordatorio davivienda mensual: ".$diaM.", Dia del recordatorio quincenal: ".$diaQ.'<br>';
        $flag=false;
        if($date->format('d') == $diaM or $date->format('d')==$diaQ){
            $notifications = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository("RocketSellerTwoPickBundle:Notification")
                ->findBy(
                    array(
                        "accion" => 'Crear Daviplata',
                        "status" => 1,
                    )
                );
            if($notifications){
                $flag=true;
                $response = $response."-HAY NOTIFICACIONES".'<br><br>';
            }
        }
        if(($date->format('d') == $diaM or $date->format('d')==$diaQ) and !$flag){
            $response = $response."- NO HAY NOTIFICACIONES DE DAVIPLATA".'<br><br>';
        }elseif($date->format('d') == $diaM){
            $response = $response."- SE EJECUTA LA TAREA MENSUAL".'<br><br>';
            $response = $response."- -RECORRIENDO NOTIFICACIONES DAVIPLATA".'<br><br>';
            /** @var Notification $notification */
            foreach ($notifications as $notification){
                $person = $notification->getPersonPerson();
                /** @var User $user */
                $user = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson'=>$person));
                if($user){
                    $pices = explode('/',$notification->getRelatedLink());
                    if($pices){
                        $pMethod = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:PayMethod")->find($pices[2]);
                        if($pMethod){
                            /** @var Contract $contract */
                            $contract=$this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('payMethodPayMethod'=>$pMethod));
                            if($contract){
                                if($contract->getActivePayroll()->getMonth() == $date->format('m') and $contract->getActivePayroll()->getPeriod()==4){
                                    $smailer = $this->get('symplifica.mailer.twig_swift');
                                    $context = array(
                                        'emailType'=>'daviplataReminder',
                                        'toEmail'=>$user->getEmail(),
                                        'userName'=>$user->getPersonPerson()->getFullName(),
                                        'employeeName'=>$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()
                                    );
                                    $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                                    $response = $response . "- - - Usuario= ".$user->getPersonPerson()->getFullName()." Empleado: ".$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br><br>";
                                    if($send){
                                        $enviado=true;
                                        $response = $response."- - -ENVIO EL CORREO<br><br>";
                                    }else{
                                        $response = $response."- - -NO ENVIO EL CORREO<br><br>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }elseif($date->format('d') == $diaQ){
            $response = $response."- SE EJECUTA LA TAREA QUINCENAL".'<br><br>';
            $response = $response."- -RECORRIENDO NOTIFICACIONES DAVIPLATA".'<br><br>';
            /** @var Notification $notification */
            foreach ($notifications as $notification) {
                $person = $notification->getPersonPerson();
                /** @var User $user */
                $user = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson' => $person));
                if ($user) {
                    $pices = explode('/', $notification->getRelatedLink());
                    if ($pices) {
                        $pMethod = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:PayMethod")->find($pices[2]);
                        if ($pMethod) {
                            /** @var Contract $contract */
                            $contract = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('payMethodPayMethod' => $pMethod));
                            if ($contract) {
                                if ($contract->getActivePayroll()->getMonth() == $date->format('m') and $contract->getActivePayroll()->getPeriod() == 2) {
                                    $response = $response . "- - - Usuario= ".$user->getPersonPerson()->getFullName()." Empleado: ".$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()."<br><br>";
                                    $context = array(
                                        'emailType'=>'daviplataReminder',
                                        'toEmail'=>$user->getEmail(),
                                        'userName'=>$user->getPersonPerson()->getFullName(),
                                        'employeeName'=>$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()
                                    );
                                    $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
                                    if ($send) {
                                        $response = $response . "- - -ENVIO EL CORREO<br><br>";
                                    } else {
                                        $response = $response . "- - -NO ENVIO EL CORREO<br><br>";
                                    }
                                }
                            }
                        }
                    }
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