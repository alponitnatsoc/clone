<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct;

class LoadPromotionCodesTypeHasProductData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $pcthpBeta= new PromotionCodeTypeHasProduct();
        $pcthpBeta->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta->setProductProduct($this->getReference('product-type-ps1'));
        $pcthpBeta->setPercent(100);

        $manager->persist($pcthpBeta);

        $pcthpBeta2= new PromotionCodeTypeHasProduct();
        $pcthpBeta2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta2->setProductProduct($this->getReference('product-type-ps2'));
        $pcthpBeta2->setPercent(100);


        $manager->persist($pcthpBeta2);

        $pcthpBeta3= new PromotionCodeTypeHasProduct();
        $pcthpBeta3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta3->setProductProduct($this->getReference('product-type-ps3'));
        $pcthpBeta3->setPercent(100);


        $manager->persist($pcthpBeta3);

        $pcthpBack= new PromotionCodeTypeHasProduct();
        $pcthpBack->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack->setProductProduct($this->getReference('product-type-ps1'));
        $pcthpBack->setPercent(100);


        $manager->persist($pcthpBack);

        $pcthpBack2= new PromotionCodeTypeHasProduct();
        $pcthpBack2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack2->setProductProduct($this->getReference('product-type-ps2'));
        $pcthpBack2->setPercent(100);


        $manager->persist($pcthpBack2);

        $pcthpBack3= new PromotionCodeTypeHasProduct();
        $pcthpBack3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack3->setProductProduct($this->getReference('product-type-ps3'));
        $pcthpBack3->setPercent(100);


        $manager->persist($pcthpBack3);

        $pcthpFeria= new PromotionCodeTypeHasProduct();
        $pcthpFeria->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $pcthpFeria->setProductProduct($this->getReference('product-type-ps1'));
        $pcthpFeria->setPercent(100);


        $manager->persist($pcthpFeria);

        $pcthpFeria2= new PromotionCodeTypeHasProduct();
        $pcthpFeria2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $pcthpFeria2->setProductProduct($this->getReference('product-type-ps2'));
        $pcthpFeria2->setPercent(100);


        $manager->persist($pcthpFeria2);

        $pcthpFeria3= new PromotionCodeTypeHasProduct();
        $pcthpFeria3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $pcthpFeria3->setProductProduct($this->getReference('product-type-ps3'));
        $pcthpFeria3->setPercent(100);


        $manager->persist($pcthpFeria3);


        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 15;
    }
}
    