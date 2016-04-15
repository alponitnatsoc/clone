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


        $banco = new Bank();
        $banco->setName("BANCO DE LA REPÚBLICA");
        $banco->setNovopaymentCode("00");
        $banco->setHightechCode("00");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO DE BOGOTÁ");
        $banco->setNovopaymentCode("01");
        $banco->setHightechCode("01");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO POPULAR");
        $banco->setNovopaymentCode("02");
        $banco->setHightechCode("02");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO CORPBANCA COLOMBIA S.A.");
        $banco->setNovopaymentCode("06");
        $banco->setHightechCode("06");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCOLOMBIA S.A.");
        $banco->setNovopaymentCode("07");
        $banco->setHightechCode("07");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("CITIBANK COLOMBIA");
        $banco->setNovopaymentCode("09");
        $banco->setHightechCode("09");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO GNB SUDAMERIS COLOMBIA");
        $banco->setNovopaymentCode("12");
        $banco->setHightechCode("12");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BBVA COLOMBIA");
        $banco->setNovopaymentCode("13");
        $banco->setHightechCode("13");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("HELM BANK");
        $banco->setNovopaymentCode("06");
        $banco->setHightechCode("14");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("RED MULTIBANCA COLPATRIA S.A.");
        $banco->setNovopaymentCode("42");
        $banco->setHightechCode("19");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO DE OCCIDENTE");
        $banco->setNovopaymentCode("23");
        $banco->setHightechCode("23");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCOLDEX");
        $banco->setNovopaymentCode("1");
        $banco->setHightechCode("31");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO CAJA SOCIAL - BCSC S.A.");
        $banco->setNovopaymentCode("30");
        $banco->setHightechCode("32");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO AGRARIO DE COLOMBIA S.A.");
        $banco->setNovopaymentCode("43");
        $banco->setHightechCode("40");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO DAVIVIENDA S.A.");
        $banco->setNovopaymentCode("39");
        $banco->setHightechCode("51");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO AV VILLAS");
        $banco->setNovopaymentCode("49");
        $banco->setHightechCode("52");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO WWB S.A.");
        $banco->setNovopaymentCode("53");
        $banco->setHightechCode("53");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO PROCREDIT");
        $banco->setNovopaymentCode("51");
        $banco->setHightechCode("58");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCAMIA");
        $banco->setNovopaymentCode("52");
        $banco->setHightechCode("59");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO PICHINCHA S.A.");
        $banco->setNovopaymentCode("57");
        $banco->setHightechCode("60");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCOOMEVA");
        $banco->setNovopaymentCode("54");
        $banco->setHightechCode("61");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO FALABELLA S.A.");
        $banco->setNovopaymentCode("56");
        $banco->setHightechCode("62");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO FINANDINA S.A.");
        $banco->setNovopaymentCode("55");
        $banco->setHightechCode("63");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO SANTANDER");
        $banco->setNovopaymentCode("59");
        $banco->setHightechCode("65");
        $manager->persist($banco);

        $banco = new Bank();
        $banco->setName("BANCO COOPERATIVO COOPCENTRAL");
        $banco->setNovopaymentCode("58");
        $banco->setHightechCode("66");
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
