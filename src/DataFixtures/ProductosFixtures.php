<?php

namespace App\DataFixtures;

use App\Entity\Producto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ProductosFixtures extends Fixture {
    
    public function load(ObjectManager $manager)
    {
        // Creamos y persistimos 20 productos
        for ($i = 0; $i < 20; $i++) {
            $product = new Producto();
            $product->setNombre('product '.$i);
            $product->setPrecio(mt_rand(10, 100));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
