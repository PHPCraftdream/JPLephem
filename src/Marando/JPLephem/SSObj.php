<?php

namespace Marando\JPLephem;

use \Marando\JPLephem\DE\DE;
use \Marando\JPLephem\DE\DEreader;
use \Marando\JPLephem\Results\CartesianVector;

/**
 * Represents a solar system object that is part of a JPL ephemeris
 *
 * @property float  $id   Ephemeris id of the object
 * @property string $name Name of the object
 */
abstract class SSObj {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new instace at the specified jde
   * @param float $jde
   */
  public function __construct($jde = null) {
    $this->jde = $jde;
    $this->de  = DE::DE421();  // Default DE
  }

  // // // Static

  /**
   * Creates a new instace at the specified jde
   * @param float $jde
   * @return static
   */
  public static function at($jde) {
    return new static($jde);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * DE version used in this instance
   * @var DE
   */
  private $de = null;

  /**
   * DE reader used in this instance
   * @var DEreader
   */
  private $reader = null;

  /**
   * JDE of this instance
   * @var float
   */
  private $jde = null;

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

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Specifies the DE version to use for this instance
   * @param DE|string $de
   * @return static
   */
  public function with($de = '421') {
    if ($de instanceof DE)
      $this->de = $de;
    else
      $this->de = DE::parse($de);

    // Since the DE version has been specified, load the reader
    $this->loadReader();
    return $this;
  }

  /**
   * Finds the position of a solar system body relative to this instance
   * @param SSObj $body
   * @return CartesianVector
   */
  public function position(SSObj $body) {
    $center = $this->interpPlanet($this);
    $target = $this->interpPlanet($body);

    // Position = Target - Center
    return $target->subtract($center);
  }

  //public function observe(SSObj $body) {
  //}
  // // // Protected

  /**
   * Gets the JPL DE id of the body represented by this instance
   */
  abstract protected function getId();

  /**
   * Gets the name of the body represented by this instance
   */
  abstract protected function getName();

  /**
   * Interpolates part of a DE ephemeris
   *
   * @param int $elem       The element number to interpolate
   * @param int $components The number of components, ex. x/y/z = 3, ψ/ε = 2
   * @param bool $velocity  True returns XYZ velocity components
   *
   * @return array
   */
  protected function interp($elem, $components = 3, $velocity = false) {
    // If no reader has been set, load it
    if ($this->reader == null)
      $this->loadReader();

    // Run the interpolation on the reader
    return $this->reader->interp($elem, $components, $velocity);
  }

  /**
   * Calculates the solar system *barycentric* position of a planet, the sun or
   * the moon
   *
   * @param SSObj $planet
   * @return CartesianVector
   */
  protected function interpPlanet(SSObj $planet) {
    // Load the reader if it has not been loaded yet
    if ($this->reader == null)
      $this->loadReader();

    // If the object is the solar system bary center, return all zero
    if ($planet instanceof SolarBary)
      return CartesianVector::fromAU_AUd(0, 0, 0, 0, 0, 0);

    // Barycentric Earth geocenter
    if ($planet instanceof Earth) {
      // Earth-Moon mass ratio, barycenter position and geocentric moon position
      $emrat = $this->reader->header->const->EMRAT;
      $emb   = $this->reader->interpPlanet((new EarthBary)->id, 3, true);
      $moon  = $this->reader->interpPlanet((new Moon)->id, 3, true);

      // Position & velocity of Earth with respect to Earth-Moon Barycenter
      $x  = $emb->x->au - 1 / (1 + $emrat) * $moon->x->au;
      $y  = $emb->y->au - 1 / (1 + $emrat) * $moon->y->au;
      $z  = $emb->z->au - 1 / (1 + $emrat) * $moon->z->au;
      $vx = $emb->vx->aud - 1 / (1 + $emrat) * $moon->vx->aud;
      $vy = $emb->vy->aud - 1 / (1 + $emrat) * $moon->vy->aud;
      $vz = $emb->vz->aud - 1 / (1 + $emrat) * $moon->vz->aud;

      return CartesianVector::fromAU_AUd($x, $y, $z, $vx, $vy, $vz);
    }

    // Barycentric Moon
    if ($planet instanceof Moon) {
      // Earth-Moon mass ratio & barycenter; geocentric moon position
      $emrat = $this->reader->header->const->EMRAT;
      $emb   = $this->reader->interpPlanet((new EarthBary)->id, 3, true);
      $moon  = $this->reader->interpPlanet((new Moon())->id, 3, true);

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

    // Find the barycentric position of the planet
    return $this->reader->interpPlanet($planet->id, 3, true);
  }

  // Loads the reader relevant to this instance
  protected function loadReader() {
    $this->reader = new DEreader($this->jde, $this->de);
  }

}
