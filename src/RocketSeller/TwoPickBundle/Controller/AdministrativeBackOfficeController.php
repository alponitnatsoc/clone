<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\SellLog;
use RocketSeller\TwoPickBundle\Entity\ToCall;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use DateTime;
use Doctrine\ORM\QueryBuilder;


class AdministrativeBackOfficeController extends Controller
{
    public function indexAction(Request $request)
    {
        if(!$this->isGranted('ROLE_MONEY_ADMIN')){
            throw $this->createAccessDeniedException();
        }
        $pending = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ToCall")->findBy(array('status'=>0));
        $failed = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ToCall")->findBy(array('status'=>3));
        $uRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $eRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        $fixedPending= array();
        /** @var ToCall $item */
        foreach ($pending as $item) {
            if($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putUserPayBack"){
                $params = $item->getParameters();
                /** @var User $tUser */
                $tUser = $uRepo->find($params['user']);
                $fixedPending[$item->getIdToCall()]=array();
                $fixedPending[$item->getIdToCall()]['type']="PB";
                $fixedPending[$item->getIdToCall()]['person']=$tUser->getPersonPerson();
                $fixedPending[$item->getIdToCall()]['item']=$item;
                $topays = $params['toPay'];
                $value = 0;
                foreach ( $topays as $key => $topay) {
                    /** @var PurchaseOrdersDescription $tPod */
                    $tPod = $podRepo->find($topay);
                    $value+=$tPod->getValue();
                }
                $fixedPending[$item->getIdToCall()]['value']=$value;


            }elseif ($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putCreateRefundPurchaseOrder"){
                $params = $item->getParameters();
                /** @var Employer $tEmployer */
                $tEmployer = $eRepo->findOneBy(array('idHighTech'=>$params['account_number']));
                $fixedPending[$item->getIdToCall()]=array();
                $fixedPending[$item->getIdToCall()]['type']="RM";
                $fixedPending[$item->getIdToCall()]['person']= $tEmployer->getPersonPerson();
                $fixedPending[$item->getIdToCall()]['value']=$params['value'];
                $fixedPending[$item->getIdToCall()]['item']=$item;
            }
        }
        $fixedFailed= array();
        /** @var ToCall $item */
        foreach ($failed as $item) {
            if($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putUserPayBack"){
                $params = $item->getParameters();
                /** @var User $tUser */
                $tUser = $uRepo->find($params['user']);
                $fixedFailed[$item->getIdToCall()]=array();
                $fixedFailed[$item->getIdToCall()]['type']="PB";
                $fixedFailed[$item->getIdToCall()]['person']=$tUser->getPersonPerson();
                $fixedFailed[$item->getIdToCall()]['value']="";
                $fixedFailed[$item->getIdToCall()]['item']=$item;

            }elseif ($item->getService()=="RocketSellerTwoPickBundle:UtilsRest:putCreateRefundPurchaseOrder"){
                $params = $item->getParameters();
                /** @var Employer $tEmployer */
                $tEmployer = $eRepo->findOneBy(array('idHighTech'=>$params['account_number']));
                $fixedFailed[$item->getIdToCall()]=array();
                $fixedFailed[$item->getIdToCall()]['type']="RM";
                $fixedFailed[$item->getIdToCall()]['person']= $tEmployer->getPersonPerson();
                $fixedFailed[$item->getIdToCall()]['value']=$params['value'];
                $fixedFailed[$item->getIdToCall()]['item']=$item;
            }
        }
        return $this->render('RocketSellerTwoPickBundle:Administrative:administrativeActions.html.twig', array(
            'toApprove' => $fixedPending,
            'failed' => $fixedFailed,
        ) );
    }
    public function doActAction($id, $action)
    {
        if(!$this->isGranted('ROLE_MONEY_ADMIN')){
            throw $this->createAccessDeniedException();
        }
        /** @var ToCall $pending */
        $pending = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ToCall")->find($id);
        if($pending!=null){
            $pending->setStatus($action);
            $em =$this->getDoctrine()->getManager();
            $em->persist($pending);
            $em->flush();
            if($action==1){
                /** @var User $admUser */
                $admUser = $this->getUser();
                $params=$pending->getParameters();
                $service=$pending->getService();
                $request = $this->container->get('request');
                $method=$params['methodTy'];
                unset($params['methodTy']);
                if($method=='GET'){
                    $request->query->add($params);
                    $request->query->add(array('token'=>$admUser->getSalt()));
                }else{
                    $request->request->add($params);
                    $request->request->add(array('token'=>$admUser->getSalt()));

                }
                $request->setMethod($method);
                $insertionAnswer = $this->forward($service, array('request'=>$request),  array('_format' => 'json'));
                if(intval($insertionAnswer->getStatusCode())>=300){
                    $pending->setStatus(3);
                    $em =$this->getDoctrine()->getManager();
                    $em->persist($pending);
                    $em->flush();
                }
            }
        }
        return $this->redirectToRoute("money_admin_dashboard");


    }
    public function createTestsAction(Request $request)
    {
        $item= new ToCall();
        $item->setReasonToAuthorize("this is a test reason");
        $item->setService("RocketSellerTwoPickBundle:UtilsRest:putUserPayBack");
        $params= array();
        $params['token']='182937675461788124124';
        $params['user']='12';
        $params['toPay']=array(0=>'2000',1=>'4344');
        $params['methodTy']='PUT';
        $item->setParameters($params);
        $item2= new ToCall();
        $item2->setReasonToAuthorize("this is a test reason2");
        $item2->setService("RocketSellerTwoPickBundle:UtilsRest:putCreateRefundPurchaseOrder");
        $params2= array();
        $params2['token']='182937675461788124124';
        $params2['source']='100';
        $params2['account_number']='14902055';
        $params2['account_id']='2134123';
        $params2['value']='200000';
        $params2['methodTy']='PUT';
        $item2->setParameters($params2);
        $em=$this->getDoctrine()->getManager();
        $em->persist($item);
        $em->persist($item2);
        $em->flush();
    }
}