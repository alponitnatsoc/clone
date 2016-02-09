<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;

class PurchaseOrdersStatusData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PurchaseOrdersStatusAprobado = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado->setName('Aprobado');
        $PurchaseOrdersStatusAprobado->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado->setIdNovoPay('00');
        $manager->persist($PurchaseOrdersStatusAprobado);

        $PurchaseOrdersStatusAprobado08 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado08->setName('Aprobado');
        $PurchaseOrdersStatusAprobado08->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado08->setIdNovoPay('08');
        $manager->persist($PurchaseOrdersStatusAprobado08);

        $PurchaseOrdersStatusRechazado12 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado12->setName('Rechazado');
        $PurchaseOrdersStatusRechazado12->setDescription('Transaccion Invalida');
        $PurchaseOrdersStatusRechazado12->setIdNovoPay('12');
        $manager->persist($PurchaseOrdersStatusRechazado12);

        $PurchaseOrdersStatusRechazado13 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado13->setName('Rechazado');
        $PurchaseOrdersStatusRechazado13->setDescription('Monto Invalido');
        $PurchaseOrdersStatusRechazado13->setIdNovoPay('13');
        $manager->persist($PurchaseOrdersStatusRechazado13);

        $PurchaseOrdersStatusRechazado14 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado14->setName('Rechazado');
        $PurchaseOrdersStatusRechazado14->setDescription('Tarjeta Invalida');
        $PurchaseOrdersStatusRechazado14->setIdNovoPay('14');
        $manager->persist($PurchaseOrdersStatusRechazado14);

        $PurchaseOrdersStatusRechazado31 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado31->setName('Rechazado');
        $PurchaseOrdersStatusRechazado31->setDescription('Tarjeta No Soportada');
        $PurchaseOrdersStatusRechazado31->setIdNovoPay('31');
        $manager->persist($PurchaseOrdersStatusRechazado31);

        $PurchaseOrdersStatusRechazado51 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado51->setName('Rechazado');
        $PurchaseOrdersStatusRechazado51->setDescription('Fondos Insuficientes');
        $PurchaseOrdersStatusRechazado51->setIdNovoPay('51');
        $manager->persist($PurchaseOrdersStatusRechazado51);

        $PurchaseOrdersStatusRechazado54 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado54->setName('Rechazado');
        $PurchaseOrdersStatusRechazado54->setDescription('Tarjeta Vencida');
        $PurchaseOrdersStatusRechazado54->setIdNovoPay('54');
        $manager->persist($PurchaseOrdersStatusRechazado54);

        $PurchaseOrdersStatusRechazado57 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado57->setName('Rechazado');
        $PurchaseOrdersStatusRechazado57->setDescription('Transacción No Permitida');
        $PurchaseOrdersStatusRechazado57->setIdNovoPay('57');
        $manager->persist($PurchaseOrdersStatusRechazado57);

        $PurchaseOrdersStatusRechazado61 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado61->setName('Rechazado');
        $PurchaseOrdersStatusRechazado61->setDescription('Excede Monto Limite');
        $PurchaseOrdersStatusRechazado61->setIdNovoPay('61');
        $manager->persist($PurchaseOrdersStatusRechazado61);

        $PurchaseOrdersStatusRechazado65 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado65->setName('Rechazado');
        $PurchaseOrdersStatusRechazado65->setDescription('Excede Uso Dia');
        $PurchaseOrdersStatusRechazado65->setIdNovoPay('65');
        $manager->persist($PurchaseOrdersStatusRechazado65);

        $PurchaseOrdersStatusRechazado86 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado86->setName('Rechazado');
        $PurchaseOrdersStatusRechazado86->setDescription('Cuenta Invalida');
        $PurchaseOrdersStatusRechazado86->setIdNovoPay('86');
        $manager->persist($PurchaseOrdersStatusRechazado86);

        $PurchaseOrdersStatusRechazado87 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado87->setName('Rechazado');
        $PurchaseOrdersStatusRechazado87->setDescription('Excede Monto Diario');
        $PurchaseOrdersStatusRechazado87->setIdNovoPay('87');
        $manager->persist($PurchaseOrdersStatusRechazado87);

        $PurchaseOrdersStatusRechazado94 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado94->setName('Rechazado');
        $PurchaseOrdersStatusRechazado94->setDescription('Transacción duplicada');
        $PurchaseOrdersStatusRechazado94->setIdNovoPay('94');
        $manager->persist($PurchaseOrdersStatusRechazado94);

        $PurchaseOrdersStatusRechazado96 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado96->setName('Rechazado');
        $PurchaseOrdersStatusRechazado96->setDescription('Transacción no puede ser procesada');
        $PurchaseOrdersStatusRechazado96->setIdNovoPay('96');
        $manager->persist($PurchaseOrdersStatusRechazado96);

        $PurchaseOrdersStatusRechazadoB6 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB6->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB6->setDescription('Supera el numero maximo de transacciones diarias');
        $PurchaseOrdersStatusRechazadoB6->setIdNovoPay('B6');
        $manager->persist($PurchaseOrdersStatusRechazadoB6);

        $PurchaseOrdersStatusRechazadoB7 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB7->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB7->setDescription('Supera el valor maximo de la transaccion');
        $PurchaseOrdersStatusRechazadoB7->setIdNovoPay('B7');
        $manager->persist($PurchaseOrdersStatusRechazadoB7);

        $manager->flush();

        $this->addReference('purchaseOrdersStatus-aprobado', $PurchaseOrdersStatusAprobado);
        $this->addReference('purchaseOrdersStatus-aprobado08', $PurchaseOrdersStatusAprobado08);
        $this->addReference('purchaseOrdersStatus-rechazado12', $PurchaseOrdersStatusRechazado12);
        $this->addReference('purchaseOrdersStatus-rechazado13', $PurchaseOrdersStatusRechazado13);
        $this->addReference('purchaseOrdersStatus-rechazado14', $PurchaseOrdersStatusRechazado14);
        $this->addReference('purchaseOrdersStatus-rechazado31', $PurchaseOrdersStatusRechazado31);
        $this->addReference('purchaseOrdersStatus-rechazado51', $PurchaseOrdersStatusRechazado51);
        $this->addReference('purchaseOrdersStatus-rechazado54', $PurchaseOrdersStatusRechazado54);
        $this->addReference('purchaseOrdersStatus-rechazado57', $PurchaseOrdersStatusRechazado57);
        $this->addReference('purchaseOrdersStatus-rechazado61', $PurchaseOrdersStatusRechazado61);
        $this->addReference('purchaseOrdersStatus-rechazado65', $PurchaseOrdersStatusRechazado65);
        $this->addReference('purchaseOrdersStatus-rechazado86', $PurchaseOrdersStatusRechazado86);
        $this->addReference('purchaseOrdersStatus-rechazado87', $PurchaseOrdersStatusRechazado87);
        $this->addReference('purchaseOrdersStatus-rechazado94', $PurchaseOrdersStatusRechazado94);
        $this->addReference('purchaseOrdersStatus-rechazado96', $PurchaseOrdersStatusRechazado96);
        $this->addReference('purchaseOrdersStatus-rechazadoB6', $PurchaseOrdersStatusRechazadoB6);
        $this->addReference('purchaseOrdersStatus-rechazadoB7', $PurchaseOrdersStatusRechazadoB7);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
