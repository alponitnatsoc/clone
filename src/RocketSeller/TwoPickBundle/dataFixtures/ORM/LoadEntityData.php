<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Entity;

class LoadEntityData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $EntityCoomeva = new Entity();
        $EntityCoomeva->setName('Coomeva');
 		$EntityCoomeva->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCoomeva);

        $EntityCaprecom = new Entity();
        $EntityCaprecom->setName('Caprecom');
 		$EntityCaprecom->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCaprecom);

        $EntitySura = new Entity();
        $EntitySura->setName('Sura');
 		$EntitySura->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntitySura);

        $EntityCompensarEPS = new Entity();
        $EntityCompensarEPS->setName('Compensar');
 		$EntityCompensarEPS->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCompensarEPS);

        $EntitySuraARL = new Entity();
        $EntitySuraARL->setName('Sura');
 		$EntitySuraARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntitySuraARL);

        $EntityCompensar = new Entity();
        $EntityCompensar->setName('Compensar');
 		$EntityCompensar->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntityCompensar);

        $EntityPorvenir = new Entity();
        $EntityPorvenir->setName('Porvenir');
 		$EntityPorvenir->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityPorvenir);

        $EntityProteccion = new Entity();
        $EntityProteccion->setName('Protección');
 		$EntityProteccion->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        $EntityProteccionCesantias = new Entity();
        $EntityProteccionCesantias->setName('Protección');
        $EntityProteccionCesantias->setEntityTypeEntityType($this->getReference('entityType-cesantias'));

        $manager->persist($EntityProteccionCesantias);



        $manager->flush();

        $this->addReference('entity-coomeva', $EntityCoomeva);
        $this->addReference('entity-caprecom', $EntityCaprecom);
        $this->addReference('entity-sura', $EntitySura);
        $this->addReference('entity-sura-arl', $EntitySuraARL);
        $this->addReference('entity-proteccion', $EntityProteccion);
        $this->addReference('entity-compensar', $EntityCompensar);
        $this->addReference('entity-porvenir', $EntityPorvenir);
        $this->addReference('entity-compensar-eps', $EntityCompensarEPS);
        $this->addReference('entity-proteccion-cesantias', $EntityProteccionCesantias);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
