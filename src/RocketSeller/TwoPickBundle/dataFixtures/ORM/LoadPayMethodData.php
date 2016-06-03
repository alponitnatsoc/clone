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

class LoadPayMethodDataData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $PayTypeTransferencia=$this->getReference("payType-transferencia");
        $PayTypeEfectivo=$this->getReference("payType-efectivo");
        $PayTypeRetiro=$this->getReference("payType-retiro");
        $payMethodFields=new PayMethodFields();
        $payMethodFields->setColumnName("accountTypeAccountType");
        $payMethodFields->setDataType("AccountType");
        $payMethodFields->setLabel('Tipo de Cuenta');
        $payMethodFields->setPayTypePayType($PayTypeTransferencia);
        $manager->persist($payMethodFields);
        $payMethodFields=new PayMethodFields();
        $payMethodFields->setColumnName("accountNumber");
        $payMethodFields->setDataType("text");
        $payMethodFields->setLabel('Número de Cuenta');
        $payMethodFields->setPayTypePayType($PayTypeTransferencia);
        $manager->persist($payMethodFields);
        $payMethodFields=new PayMethodFields();
        $payMethodFields->setColumnName("bankBank");
        $payMethodFields->setDataType("Bank");
        $payMethodFields->setLabel('Banco');
        $payMethodFields->setPayTypePayType($PayTypeTransferencia);
        $manager->persist($payMethodFields);
        $payMethodFields=new PayMethodFields();
        $payMethodFields->setColumnName("cellphone");
        $payMethodFields->setDataType("number");
        $payMethodFields->setLabel('Celular del empleado');
        $payMethodFields->setPayTypePayType($PayTypeRetiro);
        $manager->persist($payMethodFields);
        $payMethodFields=new PayMethodFields();
        $payMethodFields->setColumnName("hasIt");
        $payMethodFields->setDataType("choice");
        $payMethodFields->setLabel('¿Tu empleado tiene cuenta Daviplata?');
        $payMethodFields->setPayTypePayType($PayTypeRetiro);
        $manager->persist($payMethodFields);

        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }
}
