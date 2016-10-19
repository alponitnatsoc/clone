<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\TransactionType;

class LoadTransactionTypeData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
    	  $transactionType1 = new TransactionType();
	      $transactionType1->setName('Agregar empleador a pila');
	      $transactionType1->setDescription('Inscripción del empleador al operador de pila');
	      $transactionType1->setCode('IPil');
	      $manager->persist($transactionType1);
	
		    $transactionType2 = new TransactionType();
		    $transactionType2->setName('Cargar planilla del empleador');
		    $transactionType2->setDescription('Cargar archivo de pila del empleador al operador de pila');
		    $transactionType2->setCode('CPla');
		    $manager->persist($transactionType2);
	    
	      $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }

}
