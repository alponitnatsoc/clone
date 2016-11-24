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
                    'Pasar Datacrédito' => 'DC',
                    'Codigo SMS' => 'SMS',
                    'Cambiar Contraseña' => "PWS",
                ),
                'choices_as_values' => true,
                'label'=>"Acción a realizar"))
            ->add('save', "submit", array('label' => 'Realizar'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $answer=$request->request->all()["form"];
            $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
            $targetUser = $userRepo->findOneBy(array('emailCanonical'=>$answer["target"]));
            if ($targetUser==null){
                return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
                    'form' => $form->createView(),
                    'msn'=>"El correo ".$answer['target']." no se encuentra"
                ) );
            }
            //Agregar la lógica de cada coso
            $log = new SellLog();
            $log->setActionType($answer['actionType']);
            $log->setDate(new DateTime());
            $log->setTargetUser($targetUser);
            $log->setUserUser($this->getUser());
            $em=$this->getDoctrine()->getManager();
            $em->persist($log);
            $em->flush();
            return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
                'form' => $form->createView(),
                'msn'=>"Se realizó la acción al correo ".$answer['target']." exitosamente"
            ) );
        }
        return $this->render('RocketSellerTwoPickBundle:Sells:administrativeActions.html.twig', array(
            'form' => $form->createView(),
        ) );
    }
}