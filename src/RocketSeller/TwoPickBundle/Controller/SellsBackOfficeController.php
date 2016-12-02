<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\SellLog;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use DateTime;
use Doctrine\ORM\QueryBuilder;


class SellsBackOfficeController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('target', 'email', array('label'=>"Correo del cliente"))
            ->add('newPWS', 'text', array('label'=>"Nueva Contraseña", 'required'=>false))
            ->add('actionType', 'choice', array(
                'choices'  => array(
                    'Codigo SMS' => 'SMS',
                    'Cambiar Contraseña' => "PWS",
                    'Pasar Datacrédito' => 'DC',
                ),
                'choices_as_values' => true,
                'label'=>"Acción a realizar"))
            ->add('save', "submit", array('label' => 'Realizar'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $answer=$request->request->all()["form"];
            $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
            /** @var User $targetUser */
            $targetUser = $userRepo->findOneBy(array('emailCanonical'=>$answer["target"]));
            if ($targetUser==null){
                return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
                    'form' => $form->createView(),
                    'msn'=>"El correo ".$answer['target']." no se encuentra"
                ) );
            }
            //Agregar la lógica de cada coso
            $userManager=$this->get('fos_user.user_manager');
            $msn="";
            if($answer['actionType']=="PWS"){
                $newpws= $answer['newPWS'];
                $targetUser->setPlainPassword($newpws);
                $userManager->updatePassword($targetUser);

            }elseif ($answer['actionType']=="SMS"){
                $msn=" Código SMS: ".$targetUser->getSmsCode();
            }elseif ($answer['actionType']=="DC"&&$this->isGranted('ROLE_SUPER_SELLS')){
                $targetUser->setDataCreditStatus(2);
                $userManager->updateUser($targetUser);
            }else{
                return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
                    'form' => $form->createView(),
                    'msn'=>"No se pudo realizar la acción por falta de permisos"
                ) );
            }
            $log = new SellLog();
            $log->setActionType($answer['actionType']);
            $log->setDate(new DateTime());
            $log->setTargetUser($targetUser);
            $log->setUserUser($this->getUser());
            $em=$this->getDoctrine()->getManager();
            $em->persist($log);
            $em->flush();
            $form = $this->createFormBuilder()
                ->add('target', 'email', array('label'=>"Correo del cliente"))
                ->add('newPWS', 'text', array('label'=>"Nueva Contraseña", 'required'=>false))
                ->add('actionType', 'choice', array(
                    'choices'  => array(
                        'Codigo SMS' => 'SMS',
                        'Cambiar Contraseña' => "PWS",
                        'Pasar Datacrédito' => 'DC',
                    ),
                    'choices_as_values' => true,
                    'label'=>"Acción a realizar"))
                ->add('save', "submit", array('label' => 'Realizar'))
                ->getForm();
            return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
                'form' => $form->createView(),
                'msn'=>"Se realizó la acción al correo ".$answer['target']." exitosamente".$msn
            ) );
        }
        return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
            'form' => $form->createView(),
        ) );
    }
}