<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\ContractType;

class LoadContractTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $contractTypeFijo = new ContractType();
        $contractTypeFijo->setName('Término fijo');
        $contractTypeFijo->setPayrollCode('2');
        $manager->persist($contractTypeFijo);

        $contractTypeIndefinido = new ContractType();
        $contractTypeIndefinido->setName('Término indefinido');
        $contractTypeIndefinido->setPayrollCode('1');
        $manager->persist($contractTypeIndefinido);

        $manager->flush();

        $this->addReference('contractType-fijo', $contractTypeFijo);
        $this->addReference('contractType-indefinido', $contractTypeIndefinido);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 7;
    }
}
