<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\CalculatorConstraints;

class LoadCalculatorData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {


        $domestic=$this->getReference("position-domestico");
        $calcConstraints=new ArrayCollection();
        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("smmlv");
        $calcCons->setValue("689455");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("auxilio transporte");
        $calcCons->setValue("77700");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("eps empleador");
        $calcCons->setValue("0.085");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("eps empleado");
        $calcCons->setValue("0.04");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("pension empleador");
        $calcCons->setValue("0.12");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("pension empleado");
        $calcCons->setValue("0.04");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("arl");
        $calcCons->setValue("0.00522");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("caja");
        $calcCons->setValue("0.04");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("sena");
        $calcCons->setValue("0");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("icbf");
        $calcCons->setValue("0");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("vacaciones");
        $calcCons->setValue("1.25");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("intereses cesantias");
        $calcCons->setValue("0.01");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("cesantias");
        $calcCons->setValue("0.0833333333");
        $calcConstraints->add($calcCons);

        $calcCons=new CalculatorConstraints();
        $calcCons->setPositionPosition($domestic);
        $calcCons->setName("dotacion");
        $calcCons->setValue("50000");
        $calcConstraints->add($calcCons);
	
		    $calcCons=new CalculatorConstraints();
		    $calcCons->setPositionPosition($domestic);
		    $calcCons->setName("prima");
		    $calcCons->setValue("0.0833333333");
		    $calcConstraints->add($calcCons);


        foreach ($calcConstraints as $CC) {
            $manager->persist($CC);
        }
        $manager->flush();

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
