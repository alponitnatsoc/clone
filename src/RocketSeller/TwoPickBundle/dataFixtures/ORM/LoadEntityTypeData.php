<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\EntityType;

class LoadEntityTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $EntityTypeEPS = new EntityType();
        $EntityTypeEPS->setName('EPS');
        
        $manager->persist($EntityTypeEPS);
        
        $EntityTypeARL = new EntityType();
        $EntityTypeARL->setName('ARL');        

        $manager->persist($EntityTypeARL);

        $EntityTypePensiones = new EntityType();
        $EntityTypePensiones->setName('Pension');                

        $manager->persist($EntityTypePensiones);

        $EntityTypeCajaComp = new EntityType();
        $EntityTypeCajaComp->setName('CC Familiar');                

        $manager->persist($EntityTypeCajaComp);

        $manager->flush();

        $this->addReference('entityType-eps', $EntityTypeEPS);
        $this->addReference('entityType-arl', $EntityTypeARL);
        $this->addReference('entityType-pensiones', $EntityTypePensiones);
        $this->addReference('entityType-cesantias', $EntityTypeCajaComp);

        
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }
}
    