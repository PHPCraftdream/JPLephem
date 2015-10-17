<?php

namespace Marando\JPLephem\DE;

class DE {

  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  const VERSIONS = [102, 200, 202, 403, 405, 406, 410, 413, 414, 418, 421, 422,
      423, 424, 430, '430t', 431, 432, '432t'];

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  protected function __construct($version = 421) {
    $this->version = $version;
  }

  // // // Static

  public static function parse($version) {
    $denum  = str_replace('de', '', strtolower($version));
    $result = array_values(preg_grep("/^{$denum}$/", static::VERSIONS));

    if (count($result) > 0)
      return new static($result[0]);
    else
      throw new \Exception("DE{$denum} was not found");
  }

  public static function DE102() {
    return new static(102);
  }

  public static function DE200() {
    return new static(200);
  }

  public static function DE202() {
    return new static(202);
  }

  public static function DE403() {
    return new static(403);
  }

  public static function DE405() {
    return new static(405);
  }

  public static function DE406() {
    return new static(406);
  }

  public static function DE410() {
    return new static(410);
  }

  public static function DE413() {
    return new static(413);
  }

  public static function DE414() {
    return new static(414);
  }

  public static function DE418() {
    return new static(418);
  }

  public static function DE421() {
    return new static(421);
  }

  public static function DE422() {
    return new static(422);
  }

  public static function DE423() {
    return new static(423);
  }

  public static function DE424() {
    return new static(424);
  }

  public static function DE430() {
    return new static(430);
  }

  public static function DE430t() {
    return new static('430t');
  }

  public static function DE431() {
    return new static(431);
  }

  public static function DE432() {
    return new static(432);
  }

  public static function DE432t() {
    return new static('432t');
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   *
   * @var type
   */
  public $version;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------
  // // // Overrides

  public function __toString() {
    return "DE {$this->version}";
  }

}
