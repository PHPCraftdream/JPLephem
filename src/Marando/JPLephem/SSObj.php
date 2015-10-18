<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\DE\DE;
use \Marando\JPLephem\DE\DEreader;

/**
 * @property float  $id
 * @property string $name
 */
abstract class SSObj {

  //
  // Constructors
  //

  public function __construct($jde = null) {
    $this->jde = $jde;
    $this->de  = DE::DE421();
  }

  // // // Static

  public static function at($jde) {
    return new static($jde);
  }

  //
  // Properties
  //

  private $de     = null;
  private $reader = null;
  private $jde    = null;

  /**
   * Holds the public properties of this instance
   * @var array
   */
  protected $properties = [];

  public function __get($name) {
    if ($name == 'id')
      return $this->getId();

    if ($name == 'name')
      return $this->getName();

    return $this->properties[$name];
  }

  public function __set($name, $value) {
    $this->properties[$name] = $value;
  }

  //
  // Functions
  //

  public function with(DE $de) {
    $this->de = DE::parse($de);
    $this->loadReader();
  }

  public function position(SSObj $body) {
    $center = $this->interpPlanet($this->id);
    $target = $this->interpPlanet($body->id);

    // target (as viewed from center) = XYZ[target] - XYZ[center]
    return $target->subtract($center);
  }

  //public function observe(SSObj $body) {
  //}
  // // // Protected

  abstract protected function getId();

  abstract protected function getName();

  protected function interp($elem, $componets = 3, $velocity = false) {
    if ($this->reader == null)
      $this->loadReader();

    return $this->reader->interp($elem, $components, $velocity);
  }

  /**
   * The intent of this function is to interpolate the solar system barycentric
   * position and velocity of a planet. The solar system barycentric position of
   * the Earth is calculated with respect to the solar system barycentric
   * Earth-Moon Barycenter position. Similarly, the solar system barycentric
   * position of the moon is calculateed with respect to the solar system
   * barycentric position of the Earth.
   *
   * @param float $planet
   * @return CartesianVector
   */
  protected function interpPlanet($planet = Jupiter) {
    if ($this->reader == null)
      $this->loadReader();


    if ($planet instanceof SolarBary)
      return CartesianVector::fromAU_AUd(0, 0, 0, 0, 0, 0);


    if ($planet instanceof Earth) {
      // Earth-Moon mass ratio & barycenter; geocentric moon position
      $emrat = $this->reader->header->const->EMRAT;
      $emb   = $this->reader->interpPlanet(EarthBary, 3, true);
      $moon  = $this->reader->interpPlanet(Moon, 3, true);

      // Position & velocity of Earth with respect to Earth-Moon Barycenter
      $x  = $emb->x->au - 1 / (1 + $emrat) * $moon->x->au;
      $y  = $emb->y->au - 1 / (1 + $emrat) * $moon->y->au;
      $z  = $emb->z->au - 1 / (1 + $emrat) * $moon->z->au;
      $vx = $emb->vx->aud - 1 / (1 + $emrat) * $moon->vx->aud;
      $vy = $emb->vy->aud - 1 / (1 + $emrat) * $moon->vy->aud;
      $vz = $emb->vz->aud - 1 / (1 + $emrat) * $moon->vz->aud;

      return CartesianVector::fromAU_AUd($x, $y, $z, $vx, $vy, $vz);
    }

    if ($planet instanceof Moon) {
      // Earth-Moon mass ratio & barycenter; geocentric moon position
      $emrat = $this->reader->header->const->EMRAT;
      $emb   = $this->reader->interpPlanet(EarthBary, 3, true);
      $moon  = $this->reader->interpPlanet(Moon, 3, true);

      // Position & velocity of Earth with respect to Earth-Moon Barycenter
      $x     = $emb->x->au - 1 / (1 + $emrat) * $moon->x->au;
      $y     = $emb->y->au - 1 / (1 + $emrat) * $moon->y->au;
      $z     = $emb->z->au - 1 / (1 + $emrat) * $moon->z->au;
      $vx    = $emb->vx->aud - 1 / (1 + $emrat) * $moon->vx->aud;
      $vy    = $emb->vy->aud - 1 / (1 + $emrat) * $moon->vy->aud;
      $vz    = $emb->vz->aud - 1 / (1 + $emrat) * $moon->vz->aud;
      $earth = CartesianVector::fromAU_AUd($x, $y, $z, $vx, $vy, $vz);

      // Find SSB centered moon position by adding earth to it's vectors
      return $moon->add($earth);
    }

    return $this->reader->interpPlanet($planet, 3, true);
  }

  protected function loadReader() {
    $this->reader = new DEreader($this->jde, $this->de);
  }

}
