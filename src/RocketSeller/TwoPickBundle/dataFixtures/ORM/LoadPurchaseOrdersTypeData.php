<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType;

class PurchaseOrdersTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $PurchaseOrdersTypeSymplifica = new PurchaseOrdersType();
        $PurchaseOrdersTypeSymplifica->setName('Servicio symplifica');        
        
        $manager->persist($PurchaseOrdersTypeSymplifica);
        
        $PurchaseOrdersTypeNomina = new PurchaseOrdersType();
        $PurchaseOrdersTypeNomina->setName('Pago nomina');               

        $manager->persist($PurchaseOrdersTypeNomina);

        
        $manager->flush();

        $this->addReference('purchaseOrdersType-symplifica', $PurchaseOrdersTypeSymplifica);
        $this->addReference('purchaseOrdersType-nomina', $PurchaseOrdersTypeNomina);
        
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
    