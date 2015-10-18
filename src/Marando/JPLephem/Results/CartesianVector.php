<?php

namespace Marando\JPLephem\Results;

use \Marando\Units\Distance;
use \Marando\Units\Velocity;

/**
 * Represents a cartesian position and velocity vector
 *
 * @property Distance $x
 * @property Distance $y
 * @property Distance $z
 * @property Velocity $vx
 * @property Velocity $vy
 * @property Velocity $vz
 */
class CartesianVector {

  use \Marando\Units\Traits\SetUnitTrait,
      \Marando\Units\Traits\RoundingTrait;

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct(Distance $x = null, Distance $y = null,
          Distance $z = null, Velocity $vx = null, Velocity $vy = null,
          Velocity $vz = null) {

    $this->x  = $x;
    $this->y  = $y;
    $this->z  = $z;
    $this->vx = $vx;
    $this->vy = $vy;
    $this->vz = $vz;
  }

  public static function fromAU_AUd($x, $y, $z, $vx, $vy, $vz) {
    $x  = Distance::au($x);
    $y  = Distance::au($y);
    $z  = Distance::au($z);
    $vx = Velocity::aud($vx);
    $vy = Velocity::aud($vy);
    $vz = Velocity::aud($vz);

    return new static($x, $y, $z, $vx, $vy, $vz);
  }

  public static function fromKm_Kmd($x, $y, $z, $vx, $vy, $vz) {
    $x  = Distance::km($x);
    $y  = Distance::km($y);
    $z  = Distance::km($z);
    $vx = Velocity::kmd($vx);
    $vy = Velocity::kmd($vy);
    $vz = Velocity::kmd($vz);

    return new static($x, $y, $z, $vx, $vy, $vz);
  }

  public static function fromKm_Kms($x, $y, $z, $vx, $vy, $vz) {
    $x  = Distance::km($x);
    $y  = Distance::km($y);
    $z  = Distance::km($z);
    $vx = Velocity::kms($vx);
    $vy = Velocity::kms($vy);
    $vz = Velocity::kms($vz);

    return new static($x, $y, $z, $vx, $vy, $vz);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Holds the public properties of this instance
   * @var array
   */
  protected $properties = [];

  public function __get($name) {
    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  public function add(CartesianVector $b) {
    $x  = Distance::m($this->x->m + $b->x->m);
    $y  = Distance::m($this->y->m + $b->y->m);
    $z  = Distance::m($this->z->m + $b->z->m);
    $vx = Velocity::ms($this->vx->ms + $b->vx->ms);
    $vy = Velocity::ms($this->vy->ms + $b->vy->ms);
    $vz = Velocity::ms($this->vz->ms + $b->vz->ms);

    return new static($x, $y, $z, $vx, $vy, $vz);
  }

  public function subtract(CartesianVector $b) {
    $x  = Distance::m($this->x->m - $b->x->m);
    $y  = Distance::m($this->y->m - $b->y->m);
    $z  = Distance::m($this->z->m - $b->z->m);
    $vx = Velocity::ms($this->vx->ms - $b->vx->ms);
    $vy = Velocity::ms($this->vy->ms - $b->vy->ms);
    $vz = Velocity::ms($this->vz->ms - $b->vz->ms);

    return new static($x, $y, $z, $vx, $vy, $vz);
  }

  public function toArray() {
    $array = [];
  }

  // // // Overrides

  public function __toString() {
    $format = '%+0.15E';

    $x    = sprintf($format, $this->x->au);
    $y    = sprintf($format, $this->y->au);
    $z    = sprintf($format, $this->z->au);
    $vx   = sprintf($format, $this->vx->aud);
    $vy   = sprintf($format, $this->vy->aud);
    $vz   = sprintf($format, $this->vz->aud);
    $unit = ['AU', 'AU/d'];

    if (strtolower($this->unit) == 'km, km/s') {
      $x    = sprintf($format, $this->x->km);
      $y    = sprintf($format, $this->y->km);
      $z    = sprintf($format, $this->z->km);
      $vx   = sprintf($format, $this->vx->kms);
      $vy   = sprintf($format, $this->vy->kms);
      $vz   = sprintf($format, $this->vz->kms);
      $unit = ['km', 'km/s'];
    }

    return ""
            . " X: {$x} {$unit[0]}\n"
            . " Y: {$y} {$unit[0]}\n"
            . " Z: {$z} {$unit[0]}\n"
            . "VX: {$vx} {$unit[1]}\n"
            . "VY: {$vy} {$unit[1]}\n"
            . "VZ: {$vz} {$unit[1]}\n";
  }

}
