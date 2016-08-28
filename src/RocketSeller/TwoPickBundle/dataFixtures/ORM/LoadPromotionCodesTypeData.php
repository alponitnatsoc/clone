<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PromotionCodeType;

class LoadPromotionCodesTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $clientBeta= new PromotionCodeType();
        $clientBeta->setDescription("Clientes beta 3 meses gratis");
        $clientBeta->setShortName("CB");
        $clientBeta->setDuration(3);
        $manager->persist($clientBeta);

        $clientBackdoor= new PromotionCodeType();
        $clientBackdoor->setDescription("Inscripción normal");
        $clientBackdoor->setShortName("AC");
        $clientBackdoor->setDuration(1);
        $manager->persist($clientBackdoor);


        $clientFeriaHogar= new PromotionCodeType();
        $clientFeriaHogar->setDescription("Feria hogar 2016");
        $clientFeriaHogar->setShortName("AC");
        $clientFeriaHogar->setDuration(2);
        $manager->persist($clientFeriaHogar);


        $manager->flush();

        $this->addReference('promotion-code-type-beta', $clientBeta);
        $this->addReference('promotion-code-type-backdoor', $clientBackdoor);
        $this->addReference('promotion-code-type-feria-hogar', $clientFeriaHogar);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 14;
    }
}
    