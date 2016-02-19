<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\AccountType;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\CalculatorConstraints;
use RocketSeller\TwoPickBundle\Entity\Frequency;
use RocketSeller\TwoPickBundle\Entity\PayMethodFields;

class LoadBankData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {


        $banco= new Bank();
        $banco->setName("Bancolombia");
        $manager->persist($banco);


        $banco= new Bank();
        $banco->setName("Davivienda");
        $manager->persist($banco);


        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }
}
    