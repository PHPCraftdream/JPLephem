<?php

use Marando\JPLephem\DE\DEreader;
use \Marando\JPLephem\Mercury;
use \Marando\JPLephem\Venus;

class AngleTest extends \PHPUnit_Framework_TestCase {

  public function test() {


    echo $p = Venus::at(2451545.5)->position(new Mercury);

    echo 1;
  }

}
