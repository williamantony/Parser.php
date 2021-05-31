<?php

namespace WA\Parser\Templates\Location;

use WA\Utilities\Arrays;

class US_Postal
{
    private const DIRECTIONS = [
        "N" => "NORTH",
        "S" => "SOUTH",
        "E" => "EAST",
        "W" => "WEST",
        "NE" => "NORTH EAST",
        "NW" => "NORTH WEST",
        "SE" => "SOUTH EAST",
        "SW" => "SOUTH WEST",
    ];

    private const STREET_TYPES = [
        "AVE" => "AVENUE",
        "ST" => "STREET",
        "RD" => "ROAD",
        "BLVD" => "BOULEVARD",
        "HWY" => "HIGHWAY",
    ];

    private const LINE2_TYPES = [
        "APT" => "APARTMENT",
        "BLDG" => "BUILDING",
        "FL" => "FLOOR",
        "STE" => "SUITE",
        "RM" => "ROOM",
        "DEPT" => "DEPARTMENT",
        "#" => "UNIT",
    ];

    private const STATES = [
        "AL" => "ALABAMA",
        "AK" => "ALASKA",
        "AZ" => "ARIZONA",
        "AR" => "ARKANSAS",
        "CA" => "CALIFORNIA",
        "CO" => "COLORADO",
        "CT" => "CONNECTICUT",
        "DE" => "DELAWARE",
        "DC" => "DISTRICT OF COLUMBIA",
        "FL" => "FLORIDA",
        "GA" => "GEORGIA",
        "HI" => "HAWAII",
        "ID" => "IDAHO",
        "IL" => "ILLINOIS",
        "IN" => "INDIANA",
        "IA" => "IOWA",
        "KS" => "KANSAS",
        "KY" => "KENTUCKY",
        "LA" => "LOUISIANA",
        "ME" => "MAINE",
        "MD" => "MARYLAND",
        "MA" => "MASSACHUSETTS",
        "MI" => "MICHIGAN",
        "MN" => "MINNESOTA",
        "MS" => "MISSISSIPPI",
        "MO" => "MISSOURI",
        "MT" => "MONTANA",
        "NE" => "NEBRASKA",
        "NV" => "NEVADA",
        "NH" => "NEW HAMPSHIRE",
        "NJ" => "NEW JERSEY",
        "NM" => "NEW MEXICO",
        "NY" => "NEW YORK",
        "NC" => "NORTH CAROLINA",
        "ND" => "NORTH DAKOTA",
        "OH" => "OHIO",
        "OK" => "OKLAHOMA",
        "OR" => "OREGON",
        "PA" => "PENNSYLVANIA",
        "RI" => "RHODE ISLAND",
        "SC" => "SOUTH CAROLINA",
        "SD" => "SOUTH DAKOTA",
        "TN" => "TENNESSEE",
        "TX" => "TEXAS",
        "UT" => "UTAH",
        "VT" => "VERMONT",
        "VA" => "VIRGINIA",
        "WA" => "WASHINGTON",
        "WV" => "WEST VIRGINIA",
        "WI" => "WISCONSIN",
        "WY" => "WYOMING",
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
            "parsedStreet" => null,
        ];

        $string = explode(",", $string);

        while ($theLine = trim(array_shift($string))) {

            if (!isset($address["street"]) && self::isStreetAddress($theLine)) {
                $address["street"] = $theLine;

                if (!isset($address["parsedStreet"])) {
                    $address["parsedStreet"] = self::parseStreetAddress($theLine);
                }
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

            $nextPart = current($parts);

            if (!isset($address["number"]) && (int) $thePart > 0) {
                $address["number"] = $thePart;
                continue;
            }

            if (!isset($address["direction"]) && self::isDirection($thePart)) {
                $address["direction"] = $thePart;
                continue;
            }

            if (!self::isStreetType($nextPart) && self::isDirection($address["direction"] . " " . $thePart)) {
                $address["direction"] = $address["direction"] . " " . $thePart;
                continue;
            }

            if (!isset($address["type"]) && !self::isStreetType($thePart)) {
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
        $states_list = array_merge(
            array_keys(self::STATES),
            array_values(self::STATES),
        );

        return in_array(trim(strtoupper($state)), $states_list);
    }

    public static function isZipCode(string $zipcode)
    {
        return (int) $zipcode > 0 && strlen($zipcode) > 4;
    }

    public static function isDirection(string $direction)
    {
        $directions_list = array_merge(
            array_keys(self::DIRECTIONS),
            array_values(self::DIRECTIONS),
        );

        return in_array(strtoupper($direction), $directions_list);
    }

    public static function isStreetType(string $type)
    {
        $types_list = array_merge(
            array_keys(self::STREET_TYPES),
            array_values(self::STREET_TYPES),
        );

        return in_array(strtoupper($type), $types_list);
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

        $postPattern = '\s?#?[A-Z0-9]{1,}';

        $list = array_merge(
            array_keys(self::LINE2_TYPES),
            array_values(self::LINE2_TYPES)
        );

        $pattern = '/^(' . implode('|', $list) . ')?' . $postPattern . '$/i';
        preg_match($pattern, $line, $matches);

        return !empty($matches) && !empty($matches[0]);
    }

    /**
     * Format Address
     */
    public static function format(string $address)
    {
        $address = self::parseAddress($address);

        $string = "";

        $street = $address["parsedStreet"];

        print_r($address);

        if (!empty($street["number"])) {
            $string .= " " . $street["number"];
        }

        if (!empty($street["direction"])) {
            $direction = strtoupper($street["direction"]);

            if (isset(self::DIRECTIONS[$direction])) {
                $string .= " " . self::DIRECTIONS[$direction];
            } else {
                $string .= " " . $direction;
            }
        }

        if (!empty($street["street"])) {
            $string .= " " . $street["street"];
        }

        if (!empty($street["type"])) {
            $street_type = strtoupper($street["type"]);

            if (isset(self::STREET_TYPES[$street_type])) {
                $string .= " " . self::STREET_TYPES[$street_type];
            } else {
                $string .= " " . $street_type;
            }
        }

        if (!empty($address["line2"])) {
            $string .= ", " . $address["line2"];
        }

        if (!empty($address["city"])) {
            $string .= ", " . $address["city"];
        }

        if (!empty($address["state"])) {
            $state = strtoupper($address["state"]);

            if (isset(self::STATES[$state])) {
                $string .= ", " . self::STATES[$state];
            } else {
                $string .= ", " . $state;
            }
        }

        if (!empty($address["zipcode"])) {
            $string .= " " . $address["zipcode"];
        }

        if (!empty($address["country"])) {
            $string .= ", " . $address["country"];
        }

        return trim(strtoupper($string));
    }
}
