<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Product;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $product = new Product();
        $product->setName('Pago Symplifica 0 a 10');
        $product->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 0 a 10 días');
        $product->setPrice(floatval('16500'));
        $product->setSimpleName('PS1');
        $product->setTaxTax($this->getReference('tax-iva'));
        $product->setValidity(null);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Pago Symplifica 11 a 19');
        $product->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 11 a 19 días');
        $product->setPrice(floatval('21000'));
        $product->setSimpleName('PS2');
        $product->setTaxTax($this->getReference('tax-iva'));
        $product->setValidity(null);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Pago Symplifica 20 o +');
        $product->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 20 o más días');
        $product->setPrice(floatval('27500'));
        $product->setSimpleName('PS3');
        $product->setTaxTax($this->getReference('tax-iva'));
        $product->setValidity(null);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Pago Nomina');
        $product->setDescription('Pago Nomina');
        $product->setPrice(null);
        $product->setSimpleName('PN');
        $product->setTaxTax(null);
        $product->setValidity(null);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Pago PILA');
        $product->setDescription('Pago PILA');
        $product->setPrice(null);
        $product->setSimpleName('PP');
        $product->setTaxTax(null);
        $product->setValidity(null);
        $manager->persist($product);

        $product = new Product();
        $product->setName('Costo transaccion');
        $product->setDescription('Costo transaccion');
        $product->setPrice(null);
        $product->setSimpleName('CT');
        $product->setTaxTax(null);
        $product->setValidity(null);
        $manager->persist($product);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }

}
