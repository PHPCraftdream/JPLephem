<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the Sun
 */
class Sun extends SSObj {

  protected function getId() {
    return 11;
  }

  protected function getName() {
    return 'Sun';
  }

}
