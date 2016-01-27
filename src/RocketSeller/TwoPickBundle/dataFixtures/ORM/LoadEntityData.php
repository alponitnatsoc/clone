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
        /* EPS This is temporary, while SQL sends the web service to get this.*/

        $EntityCoomeva = new Entity();
        $EntityCoomeva->setName('Coomeva');
        $EntityCoomeva->setPayrollCode('70');
 		    $EntityCoomeva->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCoomeva);

        $EntityCaprecom = new Entity();
        $EntityCaprecom->setName('Caprecom');
        $EntityCaprecom->setPayrollCode('255');
 		    $EntityCaprecom->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCaprecom);

        $EntitySura = new Entity();
        $EntitySura->setName('Sura');
        $EntitySura->setPayrollCode('170');
 		    $EntitySura->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntitySura);

        $EntityCompensarEPS = new Entity();
        $EntityCompensarEPS->setName('Compensar');
        $EntityCompensarEPS->setPayrollCode('50');
 		    $EntityCompensarEPS->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCompensarEPS);

        $EntityFamisanarEPS = new Entity();
        $EntityFamisanarEPS->setName('Famisanar');
        $EntityFamisanarEPS->setPayrollCode('90');
        $EntityFamisanarEPS->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityFamisanarEPS);


        // ARL.
        $EntitySuraARL = new Entity();
        $EntitySuraARL->setName('Sura');
        $EntitySuraARL->setPayrollCode('670');
 		    $EntitySuraARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntitySuraARL);

        $EntityColpatriaARL = new Entity();
        $EntityColpatriaARL->setName('Sura');
        $EntityColpatriaARL->setPayrollCode('635');
 		    $EntityColpatriaARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntityColpatriaARL);

        // Pensiones.
        $EntityPorvenir = new Entity();
        $EntityPorvenir->setName('Porvenir');
        $EntityPorvenir->setPayrollCode('300');
 		    $EntityPorvenir->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityPorvenir);

        $EntityProteccion = new Entity();
        $EntityProteccion->setName('Protección');
        $EntityProteccion->setPayrollCode('330');
 		    $EntityProteccion->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        $EntityColfondos = new Entity();
        $EntityColfondos->setName('Colfondos');
        $EntityColfondos->setPayrollCode('330');
 		    $EntityColfondos->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        // Caja compensasion.
        $EntityComfacundi = new Entity();
        $EntityComfacundi->setName('Comfacundi');
        $EntityComfacundi->setPayrollCode('548');
 		    $EntityComfacundi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityComfacundi);

        $EntityCafam = new Entity();
        $EntityCafam->setName('Cafam');
        $EntityCafam->setPayrollCode('507');
        $EntityCafam->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityCafam);




        $manager->flush();

        // EPS.
        $this->addReference('entity-coomeva', $EntityCoomeva);
        $this->addReference('entity-caprecom', $EntityCaprecom);
        $this->addReference('entity-sura', $EntitySura);
        $this->addReference('entity-compensar-eps', $EntityCompensarEPS);
        $this->addReference('entity-famisanar-eps', $EntityFamisanarEPS);

        // ARL.
        $this->addReference('entity-sura-arl', $EntitySuraARL);
        $this->addReference('entity-colpatria-arl', $EntityColpatriaARL);

        // Pensiones.
        $this->addReference('entity-proteccion', $EntityProteccion);
        $this->addReference('entity-colfondos', $EntityColfondos);
        $this->addReference('entity-porvenir', $EntityPorvenir);

        //Caja compensasion.
        $this->addReference('entity-comfacundi', $EntityComfacundi);
        $this->addReference('entity-cafam', $EntityCafam);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
