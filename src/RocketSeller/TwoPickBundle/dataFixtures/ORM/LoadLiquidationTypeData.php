<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\LiquidationType;

class LiquidationTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $LiquidationTypeDefinitiva = new LiquidationType();
        $LiquidationTypeDefinitiva->setName('Definitiva');        
        
        $manager->persist($LiquidationTypeDefinitiva);
        
        $LiquidationTypeNomina = new LiquidationType();
        $LiquidationTypeNomina->setName('Nomina');               

        $manager->persist($LiquidationTypeNomina);

        
        $manager->flush();

        $this->addReference('liquidationType-definitiva', $LiquidationTypeDefinitiva);
        $this->addReference('liquidationType-Nomina', $LiquidationTypeNomina);
        
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
    