<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class Extensiones extends AbstractExtension {
    
    public function getFilters()
    {
        return array(
            new TwigFilter('priceEuros', array($this, 'priceFilter')),
            new TwigFilter('priceDolares', array($this, 'priceDolaresFilter')),
        );
    }

    public function priceFilter($number, $decimals = 2, $decPoint = ',', $thousandsSep = '.')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = $price.'€';

        return $price;
    }
    
    public function priceDolaresFilter($number, $decimals = 2, $decPoint = ',', $thousandsSep = '.')
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }
}
