<?php

namespace WA\Parser;

use WA\Utilities\Text;

class PersonName
{
    public $fullName;
    public $firstName;
    public $middleInitial;
    public $lastName;
    public $title;
    public $suffix;
    public $postNominalSuffix;

    private static $NAME_TITLES = [
        "Mr", "Mrs", "Ms", "Rev", "Dr",
        "Pr", "Br", "Sis", "Hon", "Prof",
    ];

    public function __construct(string $name = null)
    {
        if (isset($name)) {
            $this->setName($name);
        }
    }

    public function setName(string $name)
    {
        $this->fullName = $name;
        $this->parseName($name);
    }

    private function parseName(string $name)
    {
        $name = trim(str_replace(".", "", $name));
        $name = explode(" ", $name);

        while ($theWord = array_shift($name)) {

            if (!isset($this->firstName) && self::isTitle($theWord)) {
                $this->title = trim($this->title . " " . $theWord . ".");
                continue;
            }

            if (!isset($this->firstName)) {
                $this->firstName = $theWord;
                continue;
            }

            if (!isset($this->middleInitial) && strlen($theWord) == 1) {
                $this->middleInitial = $theWord;
                continue;
            }

            if (!isset($this->suffix) && self::isSuffix($theWord)) {
                $this->suffix = $theWord;
                continue;
            }

            if (!isset($this->suffix)) {
                $this->lastName = trim($this->lastName . " " . $theWord);
            }

            if (isset($this->suffix)) {
                $this->postNominalSuffix = trim($this->postNominalSuffix . " " . $theWord);
            }

        }

    }

    public function getName(bool $long = false)
    {
        $name = "";

        if ($long && !empty($this->title)) {
            $name .= $this->title;
        }

        if (!empty($this->firstName)) {
            $name .= " " . $this->firstName;
        }

        if ($long && !empty($this->middleInitial)) {
            $name .= " " . $this->middleInitial . ".";
        }

        if (!empty($this->lastName)) {
            $name .= " " . $this->lastName;
        }

        if ($long && !empty($this->suffix)) {
            $name .= " " . $this->suffix . ".";
        }

        if ($long && !empty($this->postNominalSuffix)) {
            $name .= " " . $this->postNominalSuffix;
        }

        return trim($name);
    }

    private static function isTitle(string $title)
    {
        return in_array($title, self::$NAME_TITLES);
    }

    private static function isSuffix(string $suffix)
    {
        if (in_array($suffix, ["Jr", "Sr"])) {
            return true;
        }

        if (strlen($suffix) <= 3 && Text::isRomanNumeral($suffix)) {
            return true;
        }

        return false;
    }
}
