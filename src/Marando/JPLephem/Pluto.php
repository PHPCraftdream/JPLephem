<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the planet Pluto
 */
class Pluto extends SSObj {

  protected function getId() {
    return 9;
  }

  protected function getName() {
    return 'Pluto';
  }

}
