<?php

namespace RocketSeller\TwoPickBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SettingsControllerTest extends WebTestCase
{
    public function testManagesettings()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings');
    }

}
