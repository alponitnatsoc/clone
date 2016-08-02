<?php
namespace RocketSeller\TwoPickBundle\Traits;

use RocketSeller\TwoPickBundle\Controller\UtilsController;

trait BasicPersonDataMethodsTrait
{
    protected function fullName($id)
    {
        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Person');
        /** @var \RocketSeller\TwoPickBundle\Entity\Person $person */
        $person = $repository->find($id);

        $names = $person->getNames();
        $lastName = $person->getLastName1();
        $lastName2 = $person->getLastName2();

        $fullName = $names . " " . $lastName;

        if($lastName2 != ""){
          $fullName = $fullName . " " . $lastName2;
        }
        /** @var UtilsController $utils */
        $utils = $this->get('app.symplifica_utils');


        return $utils->mb_capitalize($fullName);
    }
}
