<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Campaign;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PromotionCodeType;

class LoadCampaignData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $clientBeta= new Campaign();
        $clientBeta->setDescription("150k");
        $clientBeta->setEnabled(0);
        $clientBeta->setStock(200);
        $clientBeta->setDateStart(new \DateTime('2016-11-15 00:00:00'));
        $clientBeta->setDateEnd(new \DateTime('2016-11-30 00:00:00'));
        $manager->persist($clientBeta);

        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 14;
    }
}
    