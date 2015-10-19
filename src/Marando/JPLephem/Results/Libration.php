<?php

namespace Marando\JPLephem\Results;

use \Marando\Units\Angle;

/**
 * @property Angle $phi
 * @property Angle $theta
 * @property Angle $psi
 */
class Libration {

  public function __construct(Angle $phi, Angle $theta, Angle $psi) {
    $this->phi   = $phi;
    $this->theta = $theta;
    $this->psi   = $psi->norm();
  }

  public static function rad($phi, $theta, $psi) {
    return new static(
            Angle::rad($phi), Angle::rad($theta), Angle::rad($psi));
  }

  public function __toString() {
    return "ϕ = {$this->phi}, θ = {$this->theta}, ψ = {$this->psi}";
  }

  // // //

  protected $properties = [];

  public function __get($name) {
    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

}
