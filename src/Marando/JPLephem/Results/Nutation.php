<?php

namespace Marando\JPLephem\Results;

use \Marando\Units\Angle;

/**
 * @property Angle $longitude
 * @property Angle $obliquity
 */
class Nutation {

  public function __construct(Angle $longitude, Angle $obliquity) {
    $this->longitude = $longitude;
    $this->obliquity = $obliquity;
  }

  public static function rad($longitude, $obliquity) {
    return new Nutation(Angle::rad($longitude), Angle::rad($obliquity));
  }

  public function __toString() {
    return "Δψ: {$this->longitude}, Δε: {$this->obliquity}";
  }

  // // //

  protected $properties = [];

  public function __get($name) {
    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

}
