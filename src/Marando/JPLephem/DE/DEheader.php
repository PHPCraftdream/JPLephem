<?php

namespace Marando\JPLephem\DE;

use \Marando\JPLephem\DE\FileReader;
use \Exception;

/**
 * Represents the header of a JPL DE ephemeris
 *
 * @property string  $description
 * @property float   $startEpoch
 * @property float   $finalEpoch
 * @property int     $blockSize
 * @property int     $kSize
 * @property int     $nCoeff
 * @property DEConst $const
 * @property array   $coeffStart
 * @property array   $coeffCount
 * @property array   $coeffSets
 *
 * @author Ashley Marando <a.marando@me.com>
 */
class DEheader {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new header instance from the path to a DE header file
   * @param string $filePath Full or relative path to the haeder file
   * @throws Exception Occurs if the file does not exist
   */
  public function __construct($filePath) {
    // Check for file existence
    if (!file_exists($filePath))
      throw new Exception("Invalid path: {$filePath}");

    // Create a new FileReader instance
    $file = new FileReader($filePath);

    // Parse each section
    $this->parseMeta($file);
    $this->parseGroup1010($file);
    $this->parseGroup1030($file);
    $this->parseGroup1040and1041($file);
    $this->parseGroup1050($file);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Holds the public properties for this instance
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

  /**
   * Parses the information before GROUP 1010
   * @param FileReader $file
   */
  protected function parseMeta(FileReader $file) {
    $line = $file->splitLine(0, ' ');

    $this->kSize  = (int)$line[1];
    $this->nCoeff = (int)$line[3];
  }

  /**
   * Parses GROUP 1010
   * @param FileReader $file
   */
  protected function parseGroup1010(FileReader $file) {
    $file->seek(4);
    $this->description = trim($file->current());
  }

  /**
   * Parses GROUP 1030
   * @param FileReader $file
   */
  protected function parseGroup1030(FileReader $file) {
    $line = $file->splitLine(10, ' ');

    $this->startEpoch = (float)$line[0];
    $this->finalEpoch = (float)$line[1];
    $this->blockSize  = (int)$line[2];
  }

  /**
   * Parses the constant values from GROUP 1040 and 1041
   * @param FileReader $file
   */
  protected function parseGroup1040and1041(FileReader $file) {
    $coeffNames  = [];
    $coeffValues = [];

    // Seek to start of GROUP 1040, and store the total number of constants
    $file->seek(14);
    $count = (int)trim($file->current());

    // Parse each constant header
    $i = 0;
    while ($i < $count) {
      $file->next();
      foreach ($file->splitCurrent(' ') as $coeff) {
        $coeffNames[] = $coeff;
        $i++;
      }
    }

    // Seek to the begining of GROUP 1041
    $file->seek(18 + ceil($count / 10));

    // Parse each constant value
    $i = 0;
    while ($i < $count) {
      $file->next();
      foreach ($file->splitCurrent(' ') as $value) {
        $coeffValues[] = static::evalNumber($value);
        $i++;
      }
    }

    // Create a new constant instance and store each of the coefficients
    $this->const                    = new DEConst();
    for ($i = 0; $i < count($coeffNames); $i++)
      $this->const->{$coeffNames[$i]} = $coeffValues[$i];
  }

  /**
   * Parses the coefficient properties of GROUP 1050
   * @param FileReader $file
   */
  protected function parseGroup1050(FileReader $file) {
    $coeffStart = [];
    $coeffCount = [];
    $coeffSets  = [];
    $nameLen    = ceil($this->const->count() / 10);
    $valueLen   = ceil($this->const->count() / 3);

    // Seek to GROUP 1050 line 1
    $file->seek(22 + $nameLen + $valueLen);
    foreach (explode(' ', $file->current()) as $i)
      if ($i != '')
        $coeffStart[] = (int)$i;

    // Seek to GROUP 1050 line 2
    $file->seek(23 + $nameLen + $valueLen);
    foreach (explode(' ', $file->current()) as $i)
      if ($i != '')
        $coeffCount[] = (int)$i;

    // Seek to GROUP 1050 line 3
    $file->seek(24 + $nameLen + $valueLen);
    foreach (explode(' ', $file->current()) as $i)
      if ($i != '')
        $coeffSets[] = (int)$i;

    // Save coefficient values to properties
    $this->coeffStart = $coeffStart;
    $this->coeffCount = $coeffCount;
    $this->coeffSets  = $coeffSets;
  }

  /**
   * Evaluates a number of the format +0.143951838384999992D-05
   * @param string $raw
   * @return float
   */
  protected static function evalNumber($raw) {
    $array = explode('D', $raw);
    return floatval($array[0]) * 10 ** intval($array[1]);
  }

}
