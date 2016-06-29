<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Configuration;

class LoadConfigurationData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $configuration = new Configuration();
        $configuration->setName('Estado Legal Persona Natural');
        $configuration->setValue('PreLegal-NaturalPerson');
        $configuration->setDescripcion('El empleador es una persona natural.');
        $manager->persist($configuration);

        $configuration = new Configuration();
        $configuration->setName('Estado Legal Afiliado Seguridad Social');
        $configuration->setValue('PreLegal-SocialSecurity');
        $configuration->setDescripcion('El empleado está afiliado a las entidades de seguridad social.');
        $manager->persist($configuration);

        $configuration = new Configuration();
        $configuration->setName('Estado Legal Dias Afiliado Salario Minimo');
        $configuration->setValue('PreLegal-DaysMinimalWage');
        $configuration->setDescripcion('Mi empleado trabaja por días y lo tenemos afiliado a seguridad social con el salario mínimo entre varios empleadores.');
        $manager->persist($configuration);

        $configuration = new Configuration();
        $configuration->setName('Estado Legal Afiliado Empleador Seguridad Social');
        $configuration->setValue('PreLegal-SocialSecurityEmployer');
        $configuration->setDescripcion('Yo estoy registrado como su empleador en las entidades de seguridad social.');
        $manager->persist($configuration);

        $configuration = new Configuration();
        $configuration->setName('Estado Legal Pago Seguridad Social');
        $configuration->setValue('PreLegal-SocialSecurityPayment');
        $configuration->setDescripcion('Estoy al día en el pago de la seguridad social de mi empleado.');
        $manager->persist($configuration);

        $configuration = new Configuration();
        $configuration->setName('Estado Legal Contrato');
        $configuration->setValue('PreLegal-SignedContract');
        $configuration->setDescripcion('Tengo un contrato firmado con mi empleado.');
        $manager->persist($configuration);

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }

}
