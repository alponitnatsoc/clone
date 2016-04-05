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
        $EntityCoomeva->setPilaCode('EPS016');
 		    $EntityCoomeva->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCoomeva);

        $EntityCaprecom = new Entity();
        $EntityCaprecom->setName('Caprecom');
        $EntityCaprecom->setPayrollCode('255');
        $EntityCaprecom->setPilaCode('EPSC20');
 		    $EntityCaprecom->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCaprecom);

        $EntitySura = new Entity();
        $EntitySura->setName('Sura');
        $EntitySura->setPayrollCode('170');
        $EntitySura->setPilaCode('EPS010');
 		    $EntitySura->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntitySura);

        $EntityCompensarEPS = new Entity();
        $EntityCompensarEPS->setName('Compensar');
        $EntityCompensarEPS->setPayrollCode('50');
        $EntityCompensarEPS->setPilaCode('EPS008');
 		    $EntityCompensarEPS->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityCompensarEPS);

        $EntityFamisanarEPS = new Entity();
        $EntityFamisanarEPS->setName('Famisanar');
        $EntityFamisanarEPS->setPayrollCode('90');
        $EntityFamisanarEPS->setPilaCode('EPS017');
        $EntityFamisanarEPS->setEntityTypeEntityType($this->getReference('entityType-eps'));

        $manager->persist($EntityFamisanarEPS);


        // ARL.
        $EntityAlfaARL = new Entity();
        $EntityAlfaARL->setName('SEGUROS DE VIDA ALFA S.A .- ARP ALFA');
        $EntityAlfaARL->setPayrollCode('600');
        $EntityAlfaARL->setPilaCode('14-17');
 		    $EntityAlfaARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntityAlfaARL);

        $EntityColmenaARL = new Entity();
        $EntityColmenaARL->setName('ARP Colmena');
        $EntityColmenaARL->setPayrollCode('630');
        $EntityColmenaARL->setPilaCode('14-25');
        $EntityColmenaARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntityColmenaARL);

        $EntityColpatriaARL = new Entity();
        $EntityColpatriaARL->setName('ARP Colpatria');
        $EntityColpatriaARL->setPayrollCode('635');
        $EntityColpatriaARL->setPilaCode('14-4');
        $EntityColpatriaARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntityColpatriaARL);

        $EntitySuratepARL = new Entity();
        $EntitySuratepARL->setName('ARP Suratep');
        $EntitySuratepARL->setPayrollCode('670');
        $EntitySuratepARL->setPilaCode('14-28');
        $EntitySuratepARL->setEntityTypeEntityType($this->getReference('entityType-arl'));

        $manager->persist($EntitySuratepARL);

        // Pensiones.
        $EntityPorvenir = new Entity();
        $EntityPorvenir->setName('Porvenir');
        $EntityPorvenir->setPayrollCode('300');
        $EntityPorvenir->setPilaCode('230301');
 		    $EntityPorvenir->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityPorvenir);

        $EntityProteccion = new Entity();
        $EntityProteccion->setName('Protección');
        $EntityProteccion->setPayrollCode('330');
        $EntityPorvenir->setPilaCode('230201');
 		    $EntityProteccion->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        $EntityColfondos = new Entity();
        $EntityColfondos->setName('Colfondos');
        $EntityColfondos->setPayrollCode('330');
        $EntityPorvenir->setPilaCode('231001');
 		    $EntityColfondos->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        $EntityNoAporta = new Entity();
        $EntityNoAporta->setName('No aporta');
        $EntityNoAporta->setPayrollCode('0');
        $EntityNoAporta->setPilaCode('0');
 		    $EntityNoAporta->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityNoAporta);

        $EntityPensionado = new Entity();
        $EntityPensionado->setName('Pensionado');
        $EntityPensionado->setPayrollCode('0');
        $EntityNoAporta->setPilaCode('0');
 		    $EntityPensionado->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityPensionado);

        // Caja compensasion.
        $EntityComfacundi = new Entity();
        $EntityComfacundi->setName('Comfacundi');
        $EntityComfacundi->setPayrollCode('548');
        $EntityNoAporta->setPilaCode('CCF26');
 		    $EntityComfacundi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityComfacundi);

        $EntityCafam = new Entity();
        $EntityCafam->setName('Cafam');
        $EntityCafam->setPayrollCode('507');
        $EntityNoAporta->setPilaCode('CCF21');
        $EntityCafam->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityCafam);

        $EntityColsubsidio = new Entity();
        $EntityColsubsidio->setName('COLSUBSIDIO');
        $EntityColsubsidio->setPayrollCode('500');
        $EntityNoAporta->setPilaCode('CCF22');
        $EntityColsubsidio->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityColsubsidio);

        $EntityAfidro = new Entity();
        $EntityAfidro->setName('AFIDRO - BOGOTA');
        $EntityAfidro->setPayrollCode('502');
        $EntityNoAporta->setPilaCode('CCF24');
        $EntityAfidro->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityAfidro);

        $EntityAseguradores= new Entity();
        $EntityAseguradores->setName('ASEGURADORES');
        $EntityAseguradores->setPayrollCode('504');
        $EntityNoAporta->setPilaCode('CCF24');
        $EntityAseguradores->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityAseguradores);

        $manager->flush();

        // EPS.
        $this->addReference('entity-coomeva', $EntityCoomeva);
        $this->addReference('entity-caprecom', $EntityCaprecom);
        $this->addReference('entity-sura', $EntitySura);
        $this->addReference('entity-compensar-eps', $EntityCompensarEPS);
        $this->addReference('entity-famisanar-eps', $EntityFamisanarEPS);

        // ARL.
        $this->addReference('entity-alfa-arl', $EntityAlfaARL);
        $this->addReference('entity-colmena-arl', $EntityColmenaARL);
        $this->addReference('entity-colpatria-arl', $EntityColpatriaARL);
        $this->addReference('entity-suratep-arl', $EntitySuratepARL);

        // Pensiones.
        $this->addReference('entity-proteccion', $EntityProteccion);
        $this->addReference('entity-colfondos', $EntityColfondos);
        $this->addReference('entity-porvenir', $EntityPorvenir);
        $this->addReference('entity-no-aporta', $EntityNoAporta);
        $this->addReference('entity-pensionado', $EntityPensionado);

        //Caja compensasion.
        $this->addReference('entity-comfacundi', $EntityComfacundi);
        $this->addReference('entity-cafam', $EntityCafam);
        $this->addReference('entity-colsubsidio', $EntityColsubsidio);
        $this->addReference('entity-afidro', $EntityAfidro);
        $this->addReference('entity-aseguradores', $EntityAseguradores);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
