<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Jupiter
 */
class Jupiter extends SSObj {

  protected function getId() {
    return 5;
  }

  protected function getName() {
    return 'Jupiter';
  }

}
