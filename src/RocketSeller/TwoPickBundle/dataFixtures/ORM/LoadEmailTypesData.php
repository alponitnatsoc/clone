<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\EmailType;
use RocketSeller\TwoPickBundle\Entity\EntityType;

class LoadEmailTypesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $emailType2MWRE = new EmailType();
        $emailType2MWRE->setName("Mas de dos meses sin registro");
        $emailType2MWRE->setEmailType("twoMonthsRegistration");
        $emailType2MWRE->setCode("2MWRE");
        $manager->persist($emailType2MWRE);

        $emailTypeNRL = new EmailType();
        $emailTypeNRL->setName("Sin Registro Landing");
        $emailTypeNRL->setEmailType("noRegisterLanding");
        $emailTypeNRL->setCode("NRL");
        $manager->persist($emailTypeNRL);

        $emailTypeNRF = new EmailType();
        $emailTypeNRF->setName("Sin Registro Facebook");
        $emailTypeNRF->setEmailType("noRegisterFacebook");
        $emailTypeNRF->setCode("NRF");
        $manager->persist($emailTypeNRF);

        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 2;
    }
}
