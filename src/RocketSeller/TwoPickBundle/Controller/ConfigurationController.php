<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Configuration;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ConfigurationController extends Controller
{
    public function storeConfigAction($legalOptions, $redirectTo, $changeFlag, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $personRepo = $em->getRepository("RocketSellerTwoPickBundle:Person");
        $person = $personRepo->find($this->getUser()->getPersonPerson()->getIdPerson());

        $configurationRepo= $em->getRepository("RocketSellerTwoPickBundle:Configuration");
        $configurations=$configurationRepo->findAll();

        $personConfiguration= new ArrayCollection();

        foreach($configurations as $cr){
          if( $cr->getValue() == "PreLegal-NaturalPerson" && $legalOptions[0] == 1) {
            $personConfiguration->add($cr);
          }
          elseif ($cr->getValue() == "PreLegal-SocialSecurity" && $legalOptions[1] == 1) {
            $personConfiguration->add($cr);
          }
          elseif( $cr->getValue() == "PreLegal-DaysMinimalWage" && $legalOptions[2] == 1) {
            $personConfiguration->add($cr);
          }
          elseif ($cr->getValue() == "PreLegal-SocialSecurityEmployer" && $legalOptions[3] == 1) {
            $personConfiguration->add($cr);
          }
          elseif( $cr->getValue() == "PreLegal-SocialSecurityPayment" && $legalOptions[4] == 1) {
            $personConfiguration->add($cr);
          }
          elseif ($cr->getValue() == "PreLegal-SignedContract" && $legalOptions[5] == 1) {
            $personConfiguration->add($cr);
          }
          elseif ($cr->getValue() == "PreLegal-PartialNoSisbén" && $legalOptions[6] == 1){
            $personConfiguration->add($cr);
          }
        }

        $person->setConfiguration($personConfiguration);
        $em->flush();

        //Should call changeFlag
        if( $changeFlag == 0 ){
          return $this->redirectToRoute("change_flag",array('flag'=>0));
        }
        else if ( $changeFlag == 1){
          return $this->redirectToRoute("change_flag",array('flag'=>1));
        }
        else if ( $changeFlag == 90) {
          return $this->redirectToRoute("contact",array('subject'=>'asistencia'));
        }

        //otherwise just redirect to the next page after storing the data
        return $this->redirectToRoute($redirectTo);

    }

}
