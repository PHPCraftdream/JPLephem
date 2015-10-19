<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;
use \Marando\JPLephem\DE\ElemNotFoundException;
use \Marando\Units\Time;

/**
 * Represents the Solar System barycenter
 */
class SolarBary extends SSObj {

  protected function getId() {
    return 0;
  }

  protected function getName() {
    return 'Solar System barycenter';
  }

  public function ttTDB() {
    try {
      // Try calculating velocity
      $ttTDB = $this->interp(15, 1);
      return Time::sec($ttTDB[0]);
    }
    catch (ElemNotFoundException $e) {
      $message = "Unable to calculate TT-TDB because the data appears to not"
              . "within the {$this->de} data.";

      throw new ElemNotFoundException($message, null, $e, 15);
    }
  }

}
