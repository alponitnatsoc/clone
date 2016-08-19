<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PayType;

class LoadPayTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PayTypeRetiro = new PayType();
        $PayTypeRetiro->setName('Daviplata');
        $PayTypeRetiro->setDescripcion('Retiros sin ningún costo desde los cajeros de Davivienda y sin cuotas de manejo. ');
        $PayTypeRetiro->setImage('/img/icon_daviplata.png');
        $PayTypeRetiro->setPayrollCode('CON');
        $PayTypeRetiro->setSimpleName('DAV');

        $manager->persist($PayTypeRetiro);

        $PayTypeTransferencia = new PayType();
        $PayTypeTransferencia->setName('Transferencia bancaria');
        $PayTypeTransferencia->setDescripcion(' Registre la cuenta bancaria donde el empleado recibirá su pago de nómina.');
        $PayTypeTransferencia->setImage('/img/icon_transfer.png');
        $PayTypeTransferencia->setPayrollCode('CON');
        $PayTypeTransferencia->setSimpleName('TRA');


        $manager->persist($PayTypeTransferencia);

        $PayTypeEfectivo = new PayType();
        $PayTypeEfectivo->setName('En efectivo');
        $PayTypeEfectivo->setDescripcion('El pago se realizará de forma directa al empleado.');
        $PayTypeEfectivo->setImage('/img/icon_cash.png');
        $PayTypeEfectivo->setPayrollCode('EFE');
        $PayTypeEfectivo->setSimpleName('EFE');

        $manager->persist($PayTypeEfectivo);

        $manager->flush();

        $this->addReference('payType-transferencia', $PayTypeTransferencia);
        $this->addReference('payType-efectivo', $PayTypeEfectivo);
        $this->addReference('payType-retiro', $PayTypeRetiro);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }
}
