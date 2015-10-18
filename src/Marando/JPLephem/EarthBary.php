<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;

class EarthBary extends SSObj {

  protected function getId() {
    return 3;
  }

  protected function getName() {
    return 'Earth-Moon barycenter';
  }

}
