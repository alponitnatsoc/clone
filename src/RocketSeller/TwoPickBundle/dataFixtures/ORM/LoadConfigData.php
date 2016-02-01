<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Config;

class LoadCityData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $configData = $this->getConfigData($manager);
        if (!isset($configData['EPS'])) {
            $config = new Config();
            $config->setName('EPS');
            $config->setValue('EPS');
            $config->setDescripcion('Key para distinguir entidades de EPS');
            $manager->persist($config);
            $manager->flush();
        }

        if (!isset($configData['ARL'])) {
            $config = new Config();
            $config->setName('ARL');
            $config->setValue('ARL');
            $config->setDescripcion('Key para distinguir entidades de ARL');
            $manager->persist($config);
            $manager->flush();
            unset($config);
        }

        if (!isset($configData['Pension'])) {
            $config = new Config();
            $config->setName('Pension');
            $config->setValue('Pension');
            $config->setDescripcion('Key para distinguir entidades de Pension');
            $manager->persist($config);
            $manager->flush();
            unset($config);
        }

        if (!isset($configData['CC Familiar'])) {
            $config = new Config();
            $config->setName('CC Familiar');
            $config->setValue('CC Familiar');
            $config->setDescripcion('Key para distinguir entidades de Caja de Compensacion Familiar');
            $manager->persist($config);
            $manager->flush();
            unset($config);
        }

        if (!isset($configData['RUT Actividad Economica'])) {
            $config = new Config();
            $config->setName('RUT Actividad Economica');
            $config->setValue('2435');
            $config->setDescripcion('Key para indicar el codigo RUT de la actividad economica');
            $manager->persist($config);
            $manager->flush();
            unset($config);
        }

        //$this->addReference('config', $config);
    }

    private function getConfigData(ObjectManager $manager)
    {
        #$configRepo = new Config();
        $configRepo = $manager->getRepository("RocketSellerTwoPickBundle:Config");
        $configDataTmp = $configRepo->findAll();
        $configData = array();
        if ($configDataTmp) {
            foreach ($configDataTmp as $key => $value) {
                $configData[$value->getName()] = $value->getValue();
            }
        }
        return $configData;
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 1;
    }

}
