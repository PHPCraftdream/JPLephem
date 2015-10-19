<?php

namespace Marando\JPLephem\Results;

use \Marando\Units\Angle;

/**
 * Represents the measures of Earth's nutation
 * 
 * @property Angle $longitude
 * @property Angle $obliquity
 */
class Nutation {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new Nutation instance
   *
   * @param Angle $longitude Nutation in longitude, Δψ
   * @param Angle $obliquity Nutation in obliquity, Δε
   */
  public function __construct(Angle $longitude, Angle $obliquity) {
    $this->longitude = $longitude;
    $this->obliquity = $obliquity;
  }

  /**
   * Creates a new Nutation instance from radians
   *
   * @param float $longitude
   * @param float $obliquity
   * @return static
   */
  public static function rad($longitude, $obliquity) {
    return new Nutation(Angle::rad($longitude), Angle::rad($obliquity));
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Holds the public properties of this instance
   * @var array
   */
  protected $properties = [];

  public function __get($name) {
    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------
  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    return "Δψ: {$this->longitude}, Δε: {$this->obliquity}";
  }

}
