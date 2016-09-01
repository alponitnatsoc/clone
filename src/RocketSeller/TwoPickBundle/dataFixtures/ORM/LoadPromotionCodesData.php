<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;

class LoadPromotionCodesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $backdoorCode= new PromotionCode();
        $backdoorCode->setCode("BACKDOOR");
        $backdoorCode->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-backdoor'));
        $manager->persist($backdoorCode);


        $feriaCode= new PromotionCode();
        $feriaCode->setCode("Feria2016");
        $feriaCode->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode);



        $feriaCode2= new PromotionCode();
        $feriaCode2->setCode("feria2016");
        $feriaCode2->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode2);



        $feriaCode3= new PromotionCode();
        $feriaCode3->setCode("feria 2016");
        $feriaCode3->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode3);



        $feriaCode4= new PromotionCode();
        $feriaCode4->setCode("Feria 2016");
        $feriaCode4->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode4);



        $feriaCode5= new PromotionCode();
        $feriaCode5->setCode("FERIA 2016");
        $feriaCode5->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode5);



        $feriaCode6= new PromotionCode();
        $feriaCode6->setCode("FERIA2016");
        $feriaCode6->setPromotionCodeTypePromotionCodeType($this->getReference('promotion-code-type-feria-hogar'));
        $manager->persist($feriaCode6);




        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 15;
    }
}
    