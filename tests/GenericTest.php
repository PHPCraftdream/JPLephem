<?php

use \Marando\JPLephem\Earth;
use \Marando\JPLephem\EarthBary;
use \Marando\JPLephem\Jupiter;
use \Marando\JPLephem\Mars;
use \Marando\JPLephem\Mercury;
use \Marando\JPLephem\Moon;
use \Marando\JPLephem\Neptune;
use \Marando\JPLephem\Pluto;
use \Marando\JPLephem\Saturn;
use \Marando\JPLephem\SolarBary;
use \Marando\JPLephem\Sun;
use \Marando\JPLephem\Uranus;
use \Marando\JPLephem\Venus;

class AngleTest extends PHPUnit_Framework_TestCase {

  public function test() {


    $jde = 2457309.5;

    $planets = [
        SolarBary::at($jde),
        Sun::at($jde),
        Mercury::at($jde),
        Venus::at($jde),
        EarthBary::at($jde),
        Earth::at($jde),
        Moon::at($jde),
        Mars::at($jde),
        Jupiter::at($jde),
        Saturn::at($jde),
        Uranus::at($jde),
        Neptune::at($jde),
        Pluto::at($jde),
    ];

    foreach ($planets as $center) {
      foreach ($planets as $target) {
        if ($center == $target)
          continue;


        echo "\n{$center->name} -> {$target->name}";
        echo "\n------------------------------\n";
        echo $center->position($target)->setUnit('AU, AU/d');
      }
    }
  }

}
