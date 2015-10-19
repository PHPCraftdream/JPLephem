<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\DE\ElemNotFoundException;
use \Marando\JPLephem\Results\Libration;
use \Marando\JPLephem\SSObj;

/**
 * Represents the Earth's Moon
 */
class Moon extends SSObj {

  protected function getId() {
    return 10;
  }

  protected function getName() {
    return 'Moon';
  }

  /**
   * Finds the librations of the Moon's mantle
   * @return Libration
   */
  public function libration() {
    try {
      // Try calculating libration
      $libration = $this->interp(13, 3);
      return Libration::rad($libration[0], $libration[1], $libration[2]);
    }
    catch (ElemNotFoundException $e) {
      $message = "Unable to calculate lunar linration because the data appears"
              . "to not exist within the {$this->de} data.";

      throw new ElemNotFoundException($message, null, $e, 13);
    }
  }

  public function mantleVelocity() {
    try {
      // Try calculating velocity
      $velocity = $this->interp(14, 3);
    }
    catch (ElemNotFoundException $e) {
      $message = "Unable to calculate lunar mantle velocity because the data "
              . "appears to not exist within the {$this->de} data.";

      throw new ElemNotFoundException($message, null, $e, 14);
    }
  }

}
