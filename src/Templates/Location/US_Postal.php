<?php

namespace WA\Parser\Templates\Location;

use WA\Utilities\Arrays;

class US_Postal
{
    private const DIRECTIONS = [
        "N", "S", "E", "W",
        "NE", "NW", "SE", "SW",
        "NORTH", "SOUTH", "EAST", "WEST",
        "NORTH EAST", "NORTH WEST",
        "SOUTH EAST", "SOUTH WEST",
    ];

    private const STREET_TYPES = [
        "AVE", "AVENUE",
        "ST", "STREET",
        "RD", "ROAD",
        "BLVD", "BOULEVARD",
        "HWY", "HIGHWAY",
    ];

    private const LINE2_TYPES = [
        "APT", "APARTMENT",
        "BLDG", "BUILDING",
        "FL", "FLOOR",
        "STE", "SUITE",
        "RM", "ROOM",
        "DEPT", "DEPARTMENT",
        "UNIT",
    ];

    private const STATES = [
        "IL", "ILLINOIS",
    ];

    private const COUNTRIES = [
        "US", "USA", "UNITED STATES",
    ];

    public static function parseAddress(string $string)
    {
        $address = [
            "street" => null,
            "line2" => null,
            "city" => null,
            "state" => null,
            "zipcode" => null,
            "country" => null,
        ];

        $string = explode(",", $string);

        while ($theLine = trim(array_shift($string))) {

            if (!isset($address["street"]) && self::isStreetAddress($theLine)) {
                $address["street"] = $theLine;
                continue;
            }

            if (!isset($address["line2"]) && self::hasLine2Components($theLine)) {
                $address["line2"] = $theLine;
                continue;
            }

            if (self::isCountry($theLine)) {
                $address["country"] = trim($theLine);
                continue;
            }

            $parts = explode(" ", $theLine);

            while ($thePart = trim(array_shift($parts))) {

                if (!isset($address["state"]) && self::isState($thePart)) {
                    $address["state"] = $thePart;
                    continue;
                }

                if (!isset($address["zipcode"]) && self::isZipCode($thePart)) {
                    $address["zipcode"] = $thePart;
                    continue;
                }

                if (!isset($address["zipcode"])) {
                    $address["city"] = trim($address["city"] . " " . $thePart);
                    continue;
                }

            }

        }

        return $address;
    }

    public static function isStreetAddress(string $string)
    {
        return (bool) self::parseStreetAddress($string);
    }

    public static function parseStreetAddress(string $string)
    {
        $address = [
            "number" => null,
            "direction" => null,
            "street" => null,
            "type" => null,
        ];

        $parts = explode(" ", $string);

        while ($thePart = trim(array_shift($parts))) {

            if (!isset($address["number"]) && (int) $thePart > 0) {
                $address["number"] = $thePart;
                continue;
            }

            if (!isset($address["direction"]) && in_array(strtoupper($thePart), self::DIRECTIONS)) {
                $address["direction"] = $thePart;
                continue;
            }

            if (!isset($address["type"]) && !in_array(strtoupper($thePart), self::STREET_TYPES)) {
                $address["street"] = trim($address["street"] . " " . $thePart);
                continue;
            }

            if (!isset($address["type"])) {
                $address["type"] = $thePart;
                continue;
            }

        }

        if (count(Arrays::clean($address)) < 3) {
            return false;
        }

        return $address;
    }

    public static function isCountry(string $country)
    {
        return in_array(trim(strtoupper($country)), self::COUNTRIES);
    }

    public static function isState(string $state)
    {
        return in_array(trim(strtoupper($state)), self::STATES);
    }

    public static function isZipCode(string $zipcode)
    {
        return (int) $zipcode > 0 && strlen($zipcode) > 4;
    }

    private static function hasLine2Components(string $line)
    {
        /**
         * Matches following patterns
         * --------------------------
         * Unit25
         * Unit#25
         * Unit #25
         * Unit 25
         */

        $postPattern = '((((\s?#)|(\s#?))[A-Z0-5]{1,})|[0-9]{1,})';
        $pattern = '/(' . implode('|', self::LINE2_TYPES) . ')' . $postPattern . '/i';

        preg_match($pattern, $line, $matches);

        return !empty($matches) && !empty($matches[0]);
    }
}
