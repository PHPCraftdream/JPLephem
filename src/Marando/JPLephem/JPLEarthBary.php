<?php

namespace Marando\JPLephem\DE;

use \Marando\JPLephem\DE\JPLBody;

class JPLEarthBary extends JPLBody {

  public function getIdDE() {
    return 3;
  }

  public function getIdNAIF() {
    return 3;
  }

  public function getName() {
    return 'Earth-Moon barycenter';
  }

}
