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
        $productS1 = new Product();
        $productS1->setName('Pago Symplifica 0 a 10');
        $productS1->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 0 a 10 días');
        $productS1->setPrice(floatval('14224')); //16500/1,16=14224.13
        $productS1->setSimpleName('PS1');
        $productS1->setTaxTax($this->getReference('tax-iva'));
        $productS1->setValidity(null);
        $manager->persist($productS1);

        $productS2 = new Product();
        $productS2->setName('Pago Symplifica 11 a 19');
        $productS2->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 11 a 19 días');
        $productS2->setPrice(floatval('19397')); //21000/1,16=18103.44
        $productS2->setSimpleName('PS2');
        $productS2->setTaxTax($this->getReference('tax-iva'));
        $productS2->setValidity(null);
        $manager->persist($productS2);

        $productS3 = new Product();
        $productS3->setName('Pago Symplifica 20 o +');
        $productS3->setDescription('El pago de symplifica que hace el usuario par adquirir los servicios básicos de 20 o más días');
        $productS3->setPrice(floatval('25431')); //27500/1,16=23706.89
        $productS3->setSimpleName('PS3');
        $productS3->setTaxTax($this->getReference('tax-iva'));
        $productS3->setValidity(null);
        $manager->persist($productS3);

        $productRE = new Product();
        $productRE->setName('Pago Registro express symplifica');
        $productRE->setDescription('El pago de symplifica que hace el usuario para adquirir los servicios de registro express');
        $productRE->setPrice(floatval('129310.35'));
        $productRE->setSimpleName('PRE');
        $productRE->setTaxTax($this->getReference('tax-iva'));
        $productRE->setValidity(null);
        $manager->persist($productRE);

        $productAL = new Product();
        $productAL->setName('Pago asistencia legal');
        $productAL->setDescription('El pago que hace un usuario para una consulta legal');
        $productAL->setPrice(floatval('129310.35'));
        $productAL->setSimpleName('PAL');
        $productAL->setTaxTax($this->getReference('tax-iva'));
        $productAL->setValidity(null);
        $manager->persist($productAL);

        $productPN = new Product();
        $productPN->setName('Pago Nomina');
        $productPN->setDescription('Pago Nomina');
        $productPN->setPrice(null);
        $productPN->setSimpleName('PN');
        $productPN->setTaxTax(null);
        $productPN->setValidity(null);
        $manager->persist($productPN);

        $productPP = new Product();
        $productPP->setName('Pago PILA');
        $productPP->setDescription('Pago PILA');
        $productPP->setPrice(null);
        $productPP->setSimpleName('PP');
        $productPP->setTaxTax(null);
        $productPP->setValidity(null);
        $manager->persist($productPP);

        $productCT = new Product();
        $productCT->setName('Costo transaccion');
        $productCT->setDescription('Costo transaccion');
        $productCT->setPrice("4741.37");
        $productCT->setSimpleName('CT');
        $productCT->setTaxTax($this->getReference('tax-iva'));
        $productCT->setValidity(null);
        $manager->persist($productCT);

        $productMora = new Product();
        $productMora->setName('Mora Pila');
        $productMora->setDescription('Costo que paga el usuario por mora de Pila');
        $productMora->setPrice("0");
        $productMora->setSimpleName('CM');
        $productMora->setValidity(null);
        $manager->persist($productMora);

        $product4x1000 = new Product();
        $product4x1000->setName('4*1000(Cuatro por mill)');
        $product4x1000->setDescription('El Impuesto que se cobrará adicional a el costo de la transaccion');
        $product4x1000->setPrice("0.004");
        $product4x1000->setSimpleName('CPM');
        $product4x1000->setValidity(null);
        $manager->persist($product4x1000);

        $manager->flush();

        $this->addReference('product-type-ps1', $productS1);
        $this->addReference('product-type-ps2', $productS2);
        $this->addReference('product-type-ps3', $productS3);
        $this->addReference('product-type-al', $productAL);
        $this->addReference('product-type-ct', $productCT);
        $this->addReference('product-type-pp', $productPP);
        $this->addReference('product-type-pn', $productPN);
        $this->addReference('product-type-re', $productRE);
        $this->addReference('product-type-cm', $productMora);
        $this->addReference('product-type-4x1000', $product4x1000);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 8;
    }

}
