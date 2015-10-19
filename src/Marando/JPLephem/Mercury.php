<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Mercury
 */
class Mercury extends SSObj {

  protected function getId() {
    return 1;
  }

  protected function getName() {
    return 'Mercury';
  }

}
