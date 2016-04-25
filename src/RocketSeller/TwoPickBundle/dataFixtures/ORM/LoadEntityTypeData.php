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
        $EntityTypeEPS->setPayrollCode('EPS');

        $manager->persist($EntityTypeEPS);

        $EntityTypeARL = new EntityType();
        $EntityTypeARL->setName('ARL');
        $EntityTypeARL->setPayrollCode('ARP');

        $manager->persist($EntityTypeARL);

        $EntityTypePensiones = new EntityType();
        $EntityTypePensiones->setName('Pension');
        $EntityTypePensiones->setPayrollCode('AFP');

        $manager->persist($EntityTypePensiones);

        $EntityTypeCajaComp = new EntityType();
        $EntityTypeCajaComp->setName('CC Familiar');
        $EntityTypeCajaComp->setPayrollCode('PARAFISCAL');

        $manager->persist($EntityTypeCajaComp);

        $EntityTypeARS = new EntityType();
        $EntityTypeARS->setName('ARS');
        $EntityTypeARS->setPayrollCode('ARS');

        $manager->persist($EntityTypeARS);

        $EntityTypeCesantias = new EntityType();
        $EntityTypeCesantias->setName('Severances');
        $EntityTypeCesantias->setPayrollCode('FCES');

        $manager->persist($EntityTypeARS);

        $manager->flush();

        $this->addReference('entityType-eps', $EntityTypeEPS);
        $this->addReference('entityType-arl', $EntityTypeARL);
        $this->addReference('entityType-pensiones', $EntityTypePensiones);
        $this->addReference('entityType-cajacomp', $EntityTypeCajaComp);
        $this->addReference('entityType-fces', $EntityTypeCesantias);
        $this->addReference('entityType-ars', $EntityTypeARS);


    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }
}
