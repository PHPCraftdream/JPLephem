<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

/**
 * Represents the Earth-Moon barycenter
 */
class EarthBary extends SSObj {

  protected function getId() {
    return 3;
  }

  protected function getName() {
    return 'Earth-Moon barycenter';
  }

}
