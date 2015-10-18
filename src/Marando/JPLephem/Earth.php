<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\Results\Nutation;
use \Marando\JPLephem\SSObj;

class Earth extends SSObj {

  protected function getId() {
    return 399;
  }

  protected function getName() {
    return 'Earth';
  }

  public function nutations() {
    $nutations = $this->interp(12, 2);
    return Nutation::rad($nutations[0], $nutations[1]);
  }

}
