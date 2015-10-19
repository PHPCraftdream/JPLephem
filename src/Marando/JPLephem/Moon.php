<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\SSObj;
use \Marando\JPLephem\Results\Libration;

class Moon extends SSObj {

  protected function getId() {
    return 10;
  }

  protected function getName() {
    return 'Moon';
  }

  public function libration() {
    $libration = $this->interp(13, 3);
    return Libration::rad($libration[0], $libration[1], $libration[2]);
  }

  public function mantleVelocity() {
    return;
    $velocity = $this->interp(14, 2);
  }

}
