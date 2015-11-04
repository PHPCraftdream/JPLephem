<?php

/*
 * Copyright (C) 2015 ashley
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Marando\JPLephem\DE;

class Reader {
  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  /**
   * Flag of the filename denoting partial DE downloads
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
   * Default DE to use, 421 is a good choice because it's small
   */
  const DE_DEFAULT = 'DE421';

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new JPL DE ephemeris reader
   *
   * @param DE    $de  JPL DE version to load
   * @param float $jde Optional initial JDE in TDB
   */
  public function __construct(DE $de = null, $jde = 2451545.5) {
    // Set DE version if provided otherwise load default
    $this->de = $de ? $de : DE::parse(static::DE_DEFAULT);

    // Get the DE storage path
    $this->path = $this->getStoragePath();

    // Download the ephemeris data if it does not exist
    if (!$this->exists())
      $this->download();

    // Parse DE header file
    $this->header = new Header($this->selectHeaderFile());

    // Set initial JDE of instance
    $this->jde($jde);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * JPL DE version
   * @var DE
   */
  protected $de;

  /**
   * JDE of this instance in TDB
   * @var float
   */
  protected $jde;

  /**
   * Path to DE ephemeris files
   * @var type
   */
  protected $path;

  /**
   * Holds the Chebyshev coefficients of the selected JDE
   * @var array
   */
  protected $chunk;

  /**
   * The DE file the selected JDE falls within
   * @var type
   */
  protected $file;

  /**
   * Year interval between DE ephemeris file segments
   * @var type
   */
  protected $yearIntvl;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Sets the JDE of the reader, and is assumed to be in Barycentric Dynamical
   * Time (TDB)
   *
   * @param float $jde
   */
  public function jde($jde) {
    // Set the JDE
    $this->jde = (float)$jde;

    // Check if JDE is within range for the entire DE span and loaded
    $this->checkValidJDE();
    $this->checkLoadedJDE();

    return $this;
  }

  public function position(SSObj $target, SSObj $center = null) {
    // True position

    if ($target && $center) {
      $center = $this->interpObject($center);
      $target = $this->interpObject($target);

      return [
          $target[0] - $center[0],
          $target[1] - $center[1],
          $target[2] - $center[2],
          $target[3] - $center[3],
          $target[4] - $center[4],
          $target[5] - $center[5],
      ];
    }
    else {
      return $this->interpObject($target);
    }
  }

  public function observe($target, $center) {
    // Astrometric position w/ light travel time
  }

  public function interp($elem, $components) {
    $this->checkLoadedJDE();

    /// Earth Mean Equator and Equinox of Reference Epoch
    // Get the 0-based element pointer
    $p = $elem - 1;

    // Find the start and end JDE of the loaded chunk
    $jd0 = $this->chunk[0];
    $jd1 = $this->chunk[1];

    // Load chunk if JDE has changed
    if ($this->jde < $jd0 || $this->jde >= $jd1)
      $this->loadChunk();

    if (count($this->header->coeffStart) < $elem)
      throw new ElemNotFoundException("The element {$elem} was not found "
      . "within the ephemeris data", null, null, $elem);

    // Coefficient properties
    $pointer  = $this->header->coeffStart[$p] - 1;
    $nCoeff   = $this->header->coeffCount[$p];
    $nSubIntv = $this->header->coeffSets[$p];

    // Get the interval number
    $tint = ($this->jde - $jd0) / $this->header->blockSize;
    $ieph = floor($tint);

    // Get the subinterval number
    $tint = ($tint - $ieph) * $nSubIntv;
    $nseg = floor($tint);

    // Find the scaled Chebychev time
    $chebTime = 2 * ($tint - $nseg) - 1;

    // Evaluate the pointer for the coefficient start
    $pointer += $nseg * $nCoeff * $components;

    // Grab the needed coefficients
    $coeff = [];
    $i     = $pointer;
    for ($j = 1; $j <= $components; $j++) {
      for ($k = 1; $k <= $nCoeff; $k++) {
        $coeff[$j][$k] = $this->chunk[$i];
        $i++;
      }
    }

    // Get the position polynomials
    $posPoly = [];

    // First position polynomials are inherent
    $posPoly[1] = 1;
    $posPoly[2] = $chebTime;

    // Find the additional position polynomials
    for ($j = 3; $j <= $nCoeff; $j++)
      $posPoly[$j] = 2 * $chebTime * $posPoly[$j - 1] - $posPoly[$j - 2];

    // Find the position of each component
    $position = [];
    for ($j = 1; $j <= $components; $j++) {
      $position[$j] = 0;
      for ($k = 1; $k < $nCoeff; $k++) {
        $position[$j] = $position[$j] + $coeff[$j][$k] * $posPoly[$k];
      }

      // Convert the position to AU based on the header definition
      if ($elem <= 11)
        $position[$j] = $position[$j] / $this->header->const->AU;
    }

    // Evaluate the velocity if it should be computed
    if ($components == 6) {
      // Find the velocity polynomials
      $velPoly = [];

      // First polynomials are inherent
      $velPoly[1] = 0;
      $velPoly[2] = 1;
      $velPoly[3] = 4 * $chebTime;

      // Find each velocity polynomial
      for ($j = 4; $j <= $nCoeff; $j++)
        $velPoly[$j] = 2 * $chebTime * $velPoly[$j - 1] +
                2 * $posPoly[$j - 1] -
                $velPoly[$j - 2];

      // Find the velocity of the body
      $velocity = [];
      for ($j = 1; $j <= 3; $j++) {
        $velocity[$j] = 0;

        // Compute each velocity coefficient
        for ($k = 1; $k <= $nCoeff; $k++)
          $velocity[$j] = $velocity[$j] + $coeff[$j][$k] * $velPoly[$k];

        // Scale the velocity
        $velocity[$j] = $velocity[$j] *
                (2.0 * $nSubIntv / $this->header->blockSize);

        // Convert velocity to AU based on the header definition
        if ($elem <= 11)
          $velocity[$j] = $velocity[$j] / $this->header->const->AU;
      }
    }

    // Array for the final results
    $results = [];

    // Add each position to the results
    foreach ($position as $p)
      $results[] = $p;

    // Add each velocity (if present) to the results
    if ($velocity)
      foreach ($velocity as $v)
        $results[] = $v;

    // Return the results
    return $results;
  }

  /**
   * Interpolates the solar system barycentric position of an object
   *
   * @param  SSObj $obj Object to interpolate
   * @return array      Position/Velocity vector
   */
  protected function interpObject(SSObj $obj) {
    // Solar System barycenter is always a zero vector
    if ($obj == SSObj::SolarBary())
      return [0, 0, 0, 0, 0, 0];

    // Calculate Earth position
    if ($obj == SSObj::Earth()) {
      // Earth-Moon mass ratio
      $emrat = $this->header->const->EMRAT;

      // Get Earth-Moon barycenter and geocentric moon positions
      $emb  = $this->interp(SSObj::EarthBary()->id, 6);
      $moon = $this->interp(SSObj::Moon()->id, 6);

      // PV of Earth with respect to Solar System barycenter
      return [
          $emb[0] - 1 / (1 + $emrat) * $moon[0],
          $emb[1] - 1 / (1 + $emrat) * $moon[1],
          $emb[2] - 1 / (1 + $emrat) * $moon[2],
          $emb[3] - 1 / (1 + $emrat) * $moon[3],
          $emb[4] - 1 / (1 + $emrat) * $moon[4],
          $emb[5] - 1 / (1 + $emrat) * $moon[5],
      ];
    }

    // Calculate Moon position
    if ($obj == SSObj::Moon()) {
      $moon  = $this->interp(SSObj::Moon()->id, 6);
      $earth = $this->interpObject(SSObj::Earth());

      return [
          $moon[0] + $earth[0],
          $moon[1] + $earth[1],
          $moon[2] + $earth[2],
          $moon[3] + $earth[3],
          $moon[4] + $earth[4],
          $moon[5] + $earth[5],
      ];
    }

    // Interpolate position
    return $this->interp($obj->id, 6);
  }

  // // // Protected
  /**
   * Evaluates a number of the format +0.143951838384999992D-05
   * @param string $raw
   * @return float
   */
  protected static function evalNumber($raw) {
    // This should be faster? Will have to test that
    return floatval(str_replace('D', 'e', $raw));

    //$array = explode('D', $raw);
    //return floatval($array[0]) * 10 ** intval($array[1]);
  }

  /**
   * Explodes a string to an array filtering any empty values
   * @param string $line
   * @return array
   */
  protected static function filtExplode($line) {
    return array_values(array_filter(explode(' ', trim($line))));
  }

  protected function checkLoadedJDE() {
    $this->checkValidJDE();

    preg_match('/ascp([0-9]{0,6})/', $this->file->getBasename(), $matches);
    $ldYearA = (int)$matches[1];
    $ldYearB = $ldYearA + $this->yearIntvl;

    $year = static::jdToYear($this->jde);
    if ($year < $ldYearA || $year > $ldYearB || $this->chunk == null) {
      $this->selectFile();
      $this->loadChunk();
    }
  }

  protected function checkValidJDE() {
    if (!$this->file)
      $this->selectFile();

    $outOfRangeLower = $this->jde < $this->header->startEpoch;
    $outOfRangeUpper = $this->jde > $this->header->finalEpoch;

    if ($outOfRangeLower || $outOfRangeUpper) {
      $message = <<<MSG
The requested JDE '{$this->jde}' is out of range for the
{$this->header->description} which has a range of JDE
{$this->header->startEpoch} to JDE {$this->header->finalEpoch}.
MSG;

      throw new OutOfBoundsException($message);
    }
  }

  public static function testpo(DE $de) {
    $de   = new Reader($de);
    $path = "{$de->path}/testpo.{$de->de->version}";

    return new FileReader($path);
  }

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

  protected function selectFile() {
    // Get year represented by this instance's JDE
    $year = static::jdToYear($this->jde);

    // Scan the DE directory for ascp coefficient files
    $files = array_values(preg_grep("/ascp[0-9]./", scandir($this->path)));
    preg_match('/ascp([0-9]{0,5})/', $files[0], $matches);

    // First year
    $yearA = $matches[1];

    // Loop through the files
    for ($i = 1; $i < count($files); $i++) {
      preg_match('/ascp([0-9]{0,5})/', $files[$i], $matches);

      // Find second year and check if requested year is in that range
      $yearB = $matches[1];

      $this->yearIntvl = $yearB - $yearA;

      if ($year >= $yearA && $year < $yearB)
        break;
      else
        $yearA = $yearB;
    }

    // Select the appropriate file
    $file       = $files[$i - 1];
    $this->file = new FileReader("{$this->path}/{$file}");
  }

  protected static function jdToYear($jd) {
    return floor(2000 + floor(($jd - 2451544.500000) / 365.25));
  }

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

  protected function getStoragePath() {
    $base = __DIR__ . "/../../../../de";
    $full = "{$base}/{$this->de->version}";

    // Make the base DE storage directory if it doesnt exist
    if (!file_exists($base))
      mkdir($base);

    // Return the real path if the DE folder exists
    return file_exists($full) ? realpath($full) : $full;
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
      foreach (static::filtExplode($chunk[$i]) as $coeff)
        $coeffs[] = static::evalNumber(trim($coeff));
    }

    // Set the coefficients
    $this->chunk = $coeffs;
  }

  // TODO PUT THIS IN HEADER
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

  // // // Overrides

  public function __get($name) {

  }

}
