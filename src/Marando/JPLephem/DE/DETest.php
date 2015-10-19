<?php

namespace Marando\JPLephem\DE;

/**
 * Represents a row of data from a DE test file
 *
 * @property string $denum
 * @property string $date
 * @property float  $jde
 * @property int    $target
 * @property int    $center
 * @property int    $element
 * @property float  $value
 */
class DETest {

  protected $properties = [];

  public function __get($name) {
    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

}
