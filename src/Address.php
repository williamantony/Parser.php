<?php

namespace WA\Parser;

use WA\Parser\Templates\Location\US_Postal;
use WA\Utilities\Arrays;

class Address
{
    public $street;
    public $line2;
    public $city;
    public $state;
    public $zipcode;
    public $country;

    public $isValid = false;
    public $rawAddress;
    public $parsedAddress;

    public function __construct(string $address)
    {
        $this->rawAddress = $address;
        $this->parsedAddress = self::parse($address);

        $this->extract($this->parsedAddress);
    }

    public function __toString()
    {
        $address = Arrays::clean(array_values($this->parsedAddress));
        return implode(", ", $address);
    }

    public function extract(array $address)
    {
        $this->street = $address["street"];
        $this->line2 = $address["line2"];
        $this->city = $address["city"];
        $this->state = $address["state"];
        $this->zipcode = $address["zipcode"];
        $this->country = $address["country"];

        if (count(Arrays::clean(array_values($address))) >= 5) {
            $this->isValid = true;
        }
    }

    public function compare($address)
    {
        if ($address instanceof Address) {
            $address = $address->parsedAddress;
        }

        if (is_string($address)) {
            $address = (new Address($address))->parsedAddress;
        }

        if (!is_array($address)) {
            return false;
        }

        $diff = array_diff($address, $this->parsedAddress);

        return count($diff) === 0;
    }

    public static function parse(string $address)
    {
        $addressSplit = explode(",", $address);
        $country = trim(end($addressSplit));

        switch (strtoupper($country)) {
            case "US":
            case "USA":
            case "UNITED STATES":
            case "UNITED STATES OF AMERICA":
                return US_Postal::parseAddress($address);

            default:
                break;
        }
    }

    public function get(string $type)
    {
        return $this->address[$type];
    }

}
