<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\PromotionCodeTypeHasProduct;

class LoadAccountDataData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $pcthpBeta= new PromotionCodeTypeHasProduct();
        $pcthpBeta->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta->setProductProduct($this->getReference('product-type-ps1'));

        $manager->persist($pcthpBeta);

        $pcthpBeta2= new PromotionCodeTypeHasProduct();
        $pcthpBeta2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta2->setProductProduct($this->getReference('product-type-ps2'));

        $manager->persist($pcthpBeta2);

        $pcthpBeta3= new PromotionCodeTypeHasProduct();
        $pcthpBeta3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-beta'));
        $pcthpBeta3->setProductProduct($this->getReference('product-type-ps3'));

        $manager->persist($pcthpBeta3);

        $pcthpBack= new PromotionCodeTypeHasProduct();
        $pcthpBack->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack->setProductProduct($this->getReference('product-type-ps1'));

        $manager->persist($pcthpBack);

        $pcthpBack2= new PromotionCodeTypeHasProduct();
        $pcthpBack2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack2->setProductProduct($this->getReference('product-type-ps2'));

        $manager->persist($pcthpBack2);

        $pcthpBack3= new PromotionCodeTypeHasProduct();
        $pcthpBack3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $pcthpBack3->setProductProduct($this->getReference('product-type-ps3'));

        $manager->persist($pcthpBack3);


        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 15;
    }
}
    