<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Mars
 */
class Mars extends SSObj {

  protected function getId() {
    return 4;
  }

  protected function getName() {
    return 'Mars';
  }

}
