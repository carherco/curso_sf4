<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/** @ODM\EmbeddedDocument */
class Address
{
    /** @ODM\Field(type="string") */
    private $address;

    /** @ODM\Field(type="string") */
    private $city;

    /** @ODM\Field(type="string") */
    private $state;

    /** @ODM\Field(type="string") */
    private $zipcode;

    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    public function getCity() { return $this->city; }
    public function setCity($city) { $this->city = $city; }

    public function getState() { return $this->state; }
    public function setState($state) { $this->state = $state; }

    public function getZipcode() { return $this->zipcode; }
    public function setZipcode($zipcode) { $this->zipcode = $zipcode; }
}