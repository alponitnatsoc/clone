<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BeneficiaryController extends Controller
{
    public function addBeneficiaryAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Beneficiary:addBeneficiary.html.twig', array(
                // ...
            ));    }

}
