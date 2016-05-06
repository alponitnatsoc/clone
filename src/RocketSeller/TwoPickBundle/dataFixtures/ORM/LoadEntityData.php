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
        $EntityProteccion->setPilaCode('230201');
 		    $EntityProteccion->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityProteccion);

        $EntityColfondos = new Entity();
        $EntityColfondos->setName('Colfondos');
        $EntityColfondos->setPayrollCode('330');
        $EntityColfondos->setPilaCode('231001');
 		    $EntityColfondos->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityColfondos);

        $EntityNoAporta = new Entity();
        $EntityNoAporta->setName('No aporta');
        $EntityNoAporta->setPayrollCode('0');
        $EntityNoAporta->setPilaCode('0');
 		    $EntityNoAporta->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityNoAporta);

        $EntityPensionado = new Entity();
        $EntityPensionado->setName('Pensionado');
        $EntityPensionado->setPayrollCode('0');
        $EntityPensionado->setPilaCode('0');
 		    $EntityPensionado->setEntityTypeEntityType($this->getReference('entityType-pensiones'));

        $manager->persist($EntityPensionado);

        // Caja compensasion.
        $EntityComfacundi = new Entity();
        $EntityComfacundi->setName('Comfacundi');
        $EntityComfacundi->setPayrollCode('548');
        $EntityComfacundi->setPilaCode('CCF26');
 		    $EntityComfacundi->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityComfacundi);

        $EntityCafam = new Entity();
        $EntityCafam->setName('Cafam');
        $EntityCafam->setPayrollCode('507');
        $EntityCafam->setPilaCode('CCF21');
        $EntityCafam->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityCafam);

        $EntityColsubsidio = new Entity();
        $EntityColsubsidio->setName('COLSUBSIDIO');
        $EntityColsubsidio->setPayrollCode('500');
        $EntityColsubsidio->setPilaCode('CCF22');
        $EntityColsubsidio->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityColsubsidio);

        $EntityAfidro = new Entity();
        $EntityAfidro->setName('AFIDRO - BOGOTA');
        $EntityAfidro->setPayrollCode('502');
        $EntityAfidro->setPilaCode('CCF24');
        $EntityAfidro->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityAfidro);

        $EntityAseguradores= new Entity();
        $EntityAseguradores->setName('ASEGURADORES');
        $EntityAseguradores->setPayrollCode('504');
        $EntityAseguradores->setPilaCode('CCF24');
        $EntityAseguradores->setEntityTypeEntityType($this->getReference('entityType-cajacomp'));

        $manager->persist($EntityAseguradores);

        //FCES
        $EntityPorvenirCesantias = new Entity();
        $EntityPorvenirCesantias->setName('Porvenir Cesantias');
        $EntityPorvenirCesantias->setPayrollCode('400');
        $EntityPorvenirCesantias->setPilaCode('230301');
        $EntityPorvenirCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntityPorvenirCesantias);

        $EntityColfondosCesantias = new Entity();
        $EntityColfondosCesantias->setName('ColFondos Cesantias');
        $EntityColfondosCesantias->setPayrollCode('410');
        $EntityColfondosCesantias->setPilaCode('231001');
        $EntityColfondosCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntityColfondosCesantias);

        $EntityProteccionCesantias = new Entity();
        $EntityProteccionCesantias->setName('Protección Cesantias');
        $EntityProteccionCesantias->setPayrollCode('430');
        $EntityProteccionCesantias->setPilaCode('230201');
        $EntityProteccionCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntityProteccionCesantias);

        $EntitySkandiaCesantias = new Entity();
        $EntitySkandiaCesantias->setName('Skandia Cesantias');
        $EntitySkandiaCesantias->setPayrollCode('450');
        $EntitySkandiaCesantias->setPilaCode('230901');
        $EntitySkandiaCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntitySkandiaCesantias);

        $EntityFondoNacionalAhorroCesantias = new Entity();
        $EntityFondoNacionalAhorroCesantias->setName('Fondo Nacional del Ahorro');
        $EntityFondoNacionalAhorroCesantias->setPayrollCode('460');
        //TODO Find PilaCode for Fondo Nacional del Ahorro
        $EntityFondoNacionalAhorroCesantias->setPilaCode('0');
        $EntityFondoNacionalAhorroCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntityFondoNacionalAhorroCesantias);

        $EntityNoSeCesantias = new Entity();
        $EntityNoSeCesantias->setName('No se');
        $EntityNoSeCesantias->setPayrollCode('0');
        $EntityNoSeCesantias->setPilaCode('0');
        $EntityNoSeCesantias->setEntityTypeEntityType($this->getReference('entityType-fces'));

        $manager->persist($EntityNoSeCesantias);

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

        //FCES
        $this->addReference('entity-porvenirCes', $EntityPorvenirCesantias);
        $this->addReference('entity-colfondosCes', $EntityColfondosCesantias);
        $this->addReference('entity-proteccionCes', $EntityProteccionCesantias);
        $this->addReference('entity-skandiaCes', $EntitySkandiaCesantias);
        $this->addReference('entity-fondoNacionalAhorroCes', $EntityFondoNacionalAhorroCesantias);
        $this->addReference('entity-noSeCes', $EntityNoSeCesantias);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
