<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PilaConstraints;

class LoadCalculatorData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {


        $pilaConstraints=new ArrayCollection();
        $multiplier=7;
        for($i=0;$i<9;$i++){
            if($i==0){
                $add1=0;
            }else{
                $add1=1;
            }
            $calcCons=new PilaConstraints();
            $calcCons->setType(3);
            $calcCons->setLastTwoDigitsFrom($i*$multiplier+$add1);
            $calcCons->setLastTwoDigitsTo(($i+1)*$multiplier);
            $calcCons->setLastDay($i+1);
            $pilaConstraints->add($calcCons);
        }

        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(64);
        $calcCons->setLastTwoDigitsTo(69);
        $calcCons->setLastDay(10);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(70);
        $calcCons->setLastTwoDigitsTo(75);
        $calcCons->setLastDay(11);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(76);
        $calcCons->setLastTwoDigitsTo(81);
        $calcCons->setLastDay(12);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(82);
        $calcCons->setLastTwoDigitsTo(87);
        $calcCons->setLastDay(13);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(88);
        $calcCons->setLastTwoDigitsTo(93);
        $calcCons->setLastDay(14);
        $pilaConstraints->add($calcCons);


        $calcCons=new PilaConstraints();
        $calcCons->setType(3);
        $calcCons->setLastTwoDigitsFrom(94);
        $calcCons->setLastTwoDigitsTo(99);
        $calcCons->setLastDay(15);
        $pilaConstraints->add($calcCons);

        //big enterprise

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(0);
        $calcCons->setLastTwoDigitsTo(10);
        $calcCons->setLastDay(1);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(11);
        $calcCons->setLastTwoDigitsTo(23);
        $calcCons->setLastDay(2);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(24);
        $calcCons->setLastTwoDigitsTo(36);
        $calcCons->setLastDay(3);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(37);
        $calcCons->setLastTwoDigitsTo(49);
        $calcCons->setLastDay(4);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(50);
        $calcCons->setLastTwoDigitsTo(62);
        $calcCons->setLastDay(5);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(63);
        $calcCons->setLastTwoDigitsTo(75);
        $calcCons->setLastDay(6);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(76);
        $calcCons->setLastTwoDigitsTo(88);
        $calcCons->setLastDay(7);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(1);
        $calcCons->setLastTwoDigitsFrom(89);
        $calcCons->setLastTwoDigitsTo(99);
        $calcCons->setLastDay(8);
        $pilaConstraints->add($calcCons);

        //small enterprise

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(0);
        $calcCons->setLastTwoDigitsTo(8);
        $calcCons->setLastDay(1);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(9);
        $calcCons->setLastTwoDigitsTo(16);
        $calcCons->setLastDay(2);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(17);
        $calcCons->setLastTwoDigitsTo(24);
        $calcCons->setLastDay(3);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(25);
        $calcCons->setLastTwoDigitsTo(32);
        $calcCons->setLastDay(4);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(33);
        $calcCons->setLastTwoDigitsTo(40);
        $calcCons->setLastDay(5);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(41);
        $calcCons->setLastTwoDigitsTo(48);
        $calcCons->setLastDay(6);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(49);
        $calcCons->setLastTwoDigitsTo(56);
        $calcCons->setLastDay(7);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(57);
        $calcCons->setLastTwoDigitsTo(64);
        $calcCons->setLastDay(8);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(65);
        $calcCons->setLastTwoDigitsTo(72);
        $calcCons->setLastDay(9);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(73);
        $calcCons->setLastTwoDigitsTo(79);
        $calcCons->setLastDay(10);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(80);
        $calcCons->setLastTwoDigitsTo(86);
        $calcCons->setLastDay(11);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(87);
        $calcCons->setLastTwoDigitsTo(93);
        $calcCons->setLastDay(12);
        $pilaConstraints->add($calcCons);

        $calcCons=new PilaConstraints();
        $calcCons->setType(2);
        $calcCons->setLastTwoDigitsFrom(94);
        $calcCons->setLastTwoDigitsTo(99);
        $calcCons->setLastDay(13);
        $pilaConstraints->add($calcCons);


        foreach ($pilaConstraints as $CC) {
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
