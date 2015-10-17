<?php

namespace Marando\JPLephem\DE;

class Earth extends \Marando\JPLephem\DE\JPLBody {

  public function getIdDE() {
    return null;
  }

  public function getIdNAIF() {
    return 399;
  }

  public function getName() {
    return 'Earth';
  }

}
