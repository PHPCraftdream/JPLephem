<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Venus
 */
class Venus extends SSObj {

  protected function getId() {
    return 2;
  }

  protected function getName() {
    return 'Venus';
  }

}
