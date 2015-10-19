<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\Results\Nutation;
use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Earth
 */
class Earth extends SSObj {

  protected function getId() {
    return 399;
  }

  protected function getName() {
    return 'Earth';
  }

  /**
   * Finds the Earth's nutations in longitude and obliquity according to the
   * IAU 1980 model
   * @return Nutation
   */
  public function nutations() {
    try {
      // Try calculating nutation
      $nutations = $this->interp(12, 2);
      return Nutation::rad($nutations[0], $nutations[1]);
    }
    catch (ElemNotFoundException $e) {
      $message = "Unable to calculate the Earth's nutations because the data "
              . "appears to not exist within the {$this->de} data.";

      throw new ElemNotFoundException($message, null, $e, 12);
    }
  }

}
