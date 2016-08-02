<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\EmployeeContractType;

class LoadEmployeeContractTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $contractTypeDomestico = new EmployeeContractType();
        $contractTypeDomestico->setName('Domestico');        
        $manager->persist($contractTypeDomestico);

        $contractTypeConvencional = new EmployeeContractType();
        $contractTypeConvencional->setName('Convencional');        
        $manager->persist($contractTypeConvencional);

        $contractTypeServicios = new EmployeeContractType();
        $contractTypeServicios->setName('Por servicios');        
        $manager->persist($contractTypeServicios);
        
        $manager->flush();

        $this->addReference('EmployeeContractType-domestico', $contractTypeDomestico);
        $this->addReference('EmployeeContractType-convencional', $contractTypeConvencional);
        $this->addReference('EmployeeContractType-servicios', $contractTypeServicios);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}
    