<?php
namespace RocketSeller\TwoPickBundle\Traits;

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

        return $fullName;
    }
}
