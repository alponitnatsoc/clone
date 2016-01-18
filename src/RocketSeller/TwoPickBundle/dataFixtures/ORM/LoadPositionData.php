<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Position;

class LoadPositionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PositionDomestica = new Position();
        $PositionDomestica->setName('Empleada(o) domestico');        
        $manager->persist($PositionDomestica);
        
        $PositionJardinero = new Position();
        $PositionJardinero->setName('Jardinero');        
        $manager->persist($PositionJardinero);

        $PositionVarios = new Position();
        $PositionVarios->setName('Varios');        
        $manager->persist($PositionVarios);

        $PositionOtros = new Position();
        $PositionOtros->setName('Otros');        
        $manager->persist($PositionOtros);

        $manager->flush();

        $this->addReference('position-domestico', $PositionDomestica);
        $this->addReference('position-jardinero', $PositionJardinero);
        $this->addReference('position-varios', $PositionVarios);
        $this->addReference('position-otros', $PositionOtros);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
    