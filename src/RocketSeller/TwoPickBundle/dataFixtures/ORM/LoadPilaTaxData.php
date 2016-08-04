<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PilaTax;

class LoadPilaTaxData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $pilaTaxConstraints= new ArrayCollection();
        $calcCons=new PilaTax();
        $calcCons->setMonth(1);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.2952);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(2);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.2952);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(3);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.2952);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(4);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3081);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(5);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3081);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(6);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3081);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(7);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3201);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(8);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3201);
        $pilaTaxConstraints->add($calcCons);

        $calcCons=new PilaTax();
        $calcCons->setMonth(9);
        $calcCons->setYear(2016);
        $calcCons->setTax(0.3201);
        $pilaTaxConstraints->add($calcCons);




        foreach ($pilaTaxConstraints as $CC) {
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
