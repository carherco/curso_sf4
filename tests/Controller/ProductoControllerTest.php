<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductoControllerTest extends WebTestCase
{
    public function xtestIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/producto/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}