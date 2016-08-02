<?php

namespace RocketSeller\TwoPickBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PayrollControllerTest extends WebTestCase
{
    public function testPay()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/pay');
    }

}
