<?php

namespace Marando\JPLephem\DE;

use \Marando\JPLephem\DE\DEHeader;
use \Marando\JPLephem\DE\FileReader;
use \Marando\JPLephem\Results\CartesianVector;
use \Marando\Units\Distance;
use \Marando\Units\Velocity;
use \Marando\JPLephem\DE\DEVer;

/**
 * Reads JPL DE files and interpolates the positions provided by it
 *
 * @property float    $jde      JDE (Julian Ephemeris Day) of this instance
 * @property DEVer       $de       JPL DE version
 * @property DEHeader $header JPL DE header
 */
class DEreader {
  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  /**
   * Flag to denote partial DE downloads
   */
  const PARTIAL_FLAG = '.partial';

  /**
   * JPL DE source url
   */
  const DE_SOURCE_DOMAIN = 'ssd.jpl.nasa.gov';

  /**
   * JPL DE source path
   */
  const DE_SOURCE_PATH = '//pub//eph/planets/ascii/';

  /**
   * Default DE to use, 421 is a good choice since it's small
   */
  const DE_DEFAULT = 'DE421';

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new DEreader instance for the specified jde and DE version
   *
   * @param float $jde
   * @param DEVer    $de  The DE version to use
   */
  public function __construct($jde, DEVer $de = null) {
    // Store the JDE and figure out which DE version to use
    $this->jde = $jde;
    $this->de  = $de == null ? DEVer::parse(static::DE_DEFAULT) : $de;

    // Find the DE storage path
    $this->path = $this->getStoragePath();

    // Download the ephemeris data if it does not exist
    if (!$this->exists())
      $this->download();

    // Get parsed DE header
    $this->header = new DEHeader($this->selectHeaderFile());

    // Check date requested
    $this->checkDate();

    // Reference relevant file and load relevant chunk
    $this->file  = $this->selectFile();
    $this->chunk = $this->loadChunk();
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * The path to the DE files
   * @var string
   */
  protected $path;

  /**
   * Holds a chunk of Chebyshev coefficients pertaining to the JD specified
   * during instantiation
   * @var array
   */
  protected $chunk;

  /**
   * The ephemeris file pertaining to the JD specified during instantiation
   * @var FileReader
   */
  protected $file;

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

  /**
   * Interpolates the position of a planet
   * @param int $planet The id of the planet to interpolate
   * @return CartesianVector
   */
  public function interpPlanet($planet) {
    $raw = $this->interp($planet, 3, true);

    $auDef = Distance::km($this->header->const->AU);

    $vector     = new CartesianVector();
    $vector->x  = Distance::au($raw[0], $auDef);
    $vector->y  = Distance::au($raw[1], $auDef);
    $vector->z  = Distance::au($raw[2], $auDef);
    $vector->vx = Velocity::aud($raw[3], $auDef);
    $vector->vy = Velocity::aud($raw[4], $auDef);
    $vector->vz = Velocity::aud($raw[5], $auDef);

    return $vector;
  }

  /**
   *
   *
   *
   * Earth Mean Equator and Equinox of Reference Epoch
   * @param type $element
   * @param type $components
   * @param type $velocity
   * @return array
   */
  public function interp($element, $components = 3, $velocity = false) {
    $p = $element - 1;  // Convert 1-base to 0-base

    $jd0 = $this->chunk[0];
    $jd1 = $this->chunk[1];

    // Coefficient properties
    $pointer  = $this->header->coeffStart[$p] - 1;
    $nCoeff   = $this->header->coeffCount[$p];
    $nSubIntv = $this->header->coeffSets[$p];


    $tint = ($this->jde - $jd0) / $this->header->blockSize;
    $ieph = floor($tint);

    $tint = ($tint - $ieph) * $nSubIntv;
    $nseg = floor($tint);

    $tseg = 2 * ($tint - $nseg) - 1;

    $pointer += $nseg * $nCoeff * $components;


    $coeff = [];
    $i     = $pointer;
    for ($j = 1; $j <= $components; $j++) {
      for ($k = 1; $k <= $nCoeff; $k++) {
        $coeff[$j][$k] = $this->chunk[$i];
        $i++;
      }
    }



    //$chebTime = 2 * $tseg;
    $chebTime = $tseg;

    $posPoly = [];

    $posPoly[1] = 1;
    $posPoly[2] = $chebTime;

    for ($j = 3; $j <= $nCoeff; $j++)
      $posPoly[$j] = 2 * $chebTime * $posPoly[$j - 1] - $posPoly[$j - 2];


    $ephem_r = [];
    for ($j = 1; $j <= $components; $j++) {
      $ephem_r[$j] = 0;
      for ($k = 1; $k < $nCoeff; $k++) {
        $ephem_r[$j] = $ephem_r[$j] + $coeff[$j][$k] * $posPoly[$k];
      }

      //convert to AU
      if ($element <= 11)
        $ephem_r[$j] = $ephem_r[$j] / $this->header->const->AU;
    }


    if ($velocity) {
      $ephem_v = [];
      $vPoly   = [];

      $vPoly[1] = 0;
      $vPoly[2] = 1;
      $vPoly[3] = 4 * $chebTime;

      for ($j = 4; $j <= $nCoeff; $j++)
        $vPoly[$j] = 2 * $chebTime * $vPoly[$j - 1] +
                2 * $posPoly[$j - 1] -
                $vPoly[$j - 2];

      for ($j = 1; $j <= 3; $j++) {
        $ephem_v[$j] = 0;

        for ($k = 1; $k <= $nCoeff; $k++) {
          $ephem_v[$j] = $ephem_v[$j] + $coeff[$j][$k] * $vPoly[$k];
          $v           = "$ephem_v[$j] + {$coeff[$j][$k]} * $vPoly[$k]";
        }

        $ephem_v[$j] = $ephem_v[$j] * (2.0 * $nSubIntv / $this->header->blockSize);

        // AU/day
        if ($element <= 11)
          $ephem_v[$j] = $ephem_v[$j] / $this->header->const->AU;
      }
    }

    $ephem   = [];
    foreach ($ephem_r as $r)
      $ephem[] = $r;

    if ($velocity)
      foreach ($ephem_v as $v)
        $ephem[] = $v;

    return $ephem;
  }

  /**
   *
   * @return \Marando\JPLephem\DE\DEtest[]
   * @throws Exception
   */
  public function testpo() {
    $path = "{$this->path}/testpo.{$this->de->version}";
    if (!file_exists($path))
      throw new Exception('testpo file not found');

    $file = new FileReader($path);

    $values = [];
    $line   = 8;
    while ($file->valid()) {
      $array = $file->splitLine($line, ' ');

      if (count($array) == 7) {
        $test          = new DEtest();
        $test->denum   = $array[0];
        $test->date    = $array[1];
        $test->jde     = $array[2];
        $test->target  = $array[3];
        $test->center  = $array[4];
        $test->element = $array[5];
        $test->value   = $array[6];

        $values[] = $test;
      }

      $line++;
    }

    return $values;
  }

  // // // Protected

  /**
   * Checks that the jde of this instance is provided by the desired DE version
   * @throws \OutOfBoundsException Occurs when the date is out of range
   */
  protected function checkDate() {
    $outOfRangeLower = $this->jde < $this->header->startEpoch;
    $outOfRangeUpper = $this->jde > $this->header->finalEpoch;

    if ($outOfRangeLower || $outOfRangeUpper) {
      $message = <<<MSG
The requested JDE '{$this->jde}' is out of range for the
{$this->header->description} which has a range of JDE
{$this->header->startEpoch} to JDE {$this->header->finalEpoch}.
MSG;

      throw new \OutOfBoundsException($message);
    }
  }

  /**
   * Downloads all DE files for the appropriate version if needed
   */
  protected function download() {
    $url         = "ssd.jpl.nasa.gov";
    $path        = $this->getStoragePath();
    $partialFlag = static::PARTIAL_FLAG;

    // Connect
    $ftp = ftp_connect($url);
    if (!$ftp)
      die('could not connect.');

    // Login
    $r = ftp_login($ftp, "anonymous", "");
    if (!$r)
      die('could not login.');

    // Enter passive mode
    $r = ftp_pasv($ftp, true);
    if (!$r)
      die('could not enable passive mode.');

    // Get file listing
    $files = ftp_nlist($ftp, "//pub//eph/planets/ascii/de{$this->de->version}");

    // Make the DE directory
    if (!file_exists($this->path))
      mkdir($this->path);

    // Flag directory as partial
    file_put_contents("{$this->path}/{$partialFlag}", '');

    // Doenload each file
    foreach ($files as $file) {
      $f = array_values(explode(DIRECTORY_SEPARATOR, $file));
      $f = $f[count($f) - 1];
      exec("curl ftp://{$url}{$file} > {$path}/{$f}");
    }

    // Once complete remove partale download flag
    unlink("{$path}/{$partialFlag}");
  }

  /**
   * Returns if the DE files requested by this instance exist in full
   * @return boolean
   */
  protected function exists() {
    // Flag denoting partial downloads of DE files
    $partialFlag = static::PARTIAL_FLAG;

    // Check if DE path exists, but download is partial
    if (file_exists($this->path))
      if (count(preg_grep("/{$partialFlag}/", scandir($this->path))) > 0)
        return false;

    // Check if DE path exists and files have been downloaded
    // TODO: In the future this should compare the actual files...
    if (file_exists($this->path))
      if (count(preg_grep("/\.{$this->de->version}/", scandir($this->path))) > 3)
        return true;

    return false;
  }

  /**
   * Finds the storage path depending on the DE version of this instance
   * @return string
   */
  protected function getStoragePath() {
    $base = __DIR__ . "/../../../../de";
    $full = "{$base}/{$this->de->version}";

    // Make the base DE storage directory if it doesnt exist
    if (!file_exists($base))
      mkdir($base);

    // Return the real path if the DE folder exists
    return file_exists($full) ? realpath($full) : $full;
  }

  protected function selectFile() {
    // Get year represented by the instance's jde
    $year  = static::jdToYear($this->jde);
    $files = array_values(preg_grep("/ascp[0-9]./", scandir($this->path)));

    preg_match('/ascp([0-9]{0,5})/', $files[0], $matches);

    $yearA = $matches[1];
    for ($i = 1; $i < count($files); $i++) {
      preg_match('/ascp([0-9]{0,5})/', $files[$i], $matches);

      $yearB = $matches[1];
      if ($year >= $yearA && $year < $yearB)
        break;
      else
        $yearA = $yearB;
    }

    $file = $files[$i - 1];
    return new FileReader("{$this->path}/{$file}");
  }

  protected function selectHeaderFile() {
    // Define header file options
    $file    = "{$this->path}/header.{$this->de->version}";
    $file572 = "{$this->path}/header.{$this->de->version}_572";
    $file229 = "{$this->path}/header.{$this->de->version}_229";

    // Try to find a header that exists...
    if (file_exists($file572))
      return $file572;
    if (file_exists($file))
      return $file;
    if (file_exists($file229))
      return $file229;

    // Occurs if none of the above header files were found.
    $message = <<<ERROR
No header file was found in the directory "{$path}" which usually happens if the
DE download is incomplete. Please check the directory and try again.
ERROR;
    throw new Exception('No header file.');
  }

  /**
   * Finds the year of a julian day number
   * @param float $jd
   * @return int
   */
  protected static function jdToYear($jd) {
    return floor(2000 + floor(($jd - 2451544.500000) / 36525));
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

  protected function loadChunk() {
    $blockSize = $this->header->blockSize;
    $nCoeff    = $this->header->nCoeff;
    $coeffs    = [];

    // Seek first line and evaluate start JDE of file
    $this->file->seek(1);
    $jde0 = static::evalNumber($this->file->splitCurrent(' ')[0]);

    // Calculate the chunk number and starting line
    $chunkNum   = floor(($this->jde - $jde0) / $blockSize);
    $chunkStart = 1 + ($chunkNum * (2 + floor($nCoeff / 3)) );

    // Get start byte offset of chunk
    $this->file->seek($chunkStart - 1);
    $byte1 = $this->file->ftell();

    // Get end byte offset of chunk
    $this->file->seek($chunkStart + floor($nCoeff / 3));
    $byteN = $this->file->ftell();

    // Load entire chunk using byte offsets (file_get_contents is fastest way)
    $fPath = $this->file->getRealPath();
    $chunk = file_get_contents($fPath, null, null, $byte1, $byteN - $byte1);

    // Explode chunk to array and parse coefficients
    $chunk = explode("\n", $chunk);
    for ($i = $chunkNum == 0 ? 1 : 0; $i <= floor($nCoeff / 3); $i++) {
      foreach (static::parseLine($chunk[$i]) as $coeff)
        $coeffs[] = static::evalNumber(trim($coeff));
    }

    // Return coefficients
    return $coeffs;
  }

  protected static function parseLine($line) {
    return array_values(array_filter(explode(' ', trim($line))));
  }

}
