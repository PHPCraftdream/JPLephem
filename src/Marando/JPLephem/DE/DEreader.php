<?php

namespace Marando\JPLephem\DE;

use \Marando\JPLephem\DE\DEheader;

/**
 * @property float  $jde    JDE (Julian Ephemeris Day) of this instance
 * @property DE     $de     JPL DE version
 * @property DEheader $header JPL DE header
 */
class DEreader {

  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  const PARTIAL_FLAG     = '.partial';
  const DE_SOURCE_DOMAIN = 'ssd.jpl.nasa.gov';
  const DE_SOURCE_PATH   = '//pub//eph/planets/ascii/';
  const DE_DEFAULT       = 'DE421';

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($jde, DE $de = null) {
    $this->jde  = $jde;
    $this->de   = $de == null ? DE::parse(static::DE_DEFAULT) : $de;
    $this->path = $this->getStoragePath();

    // Download the ephemeris data if it does not exist
    if (!$this->exists())
      $this->download();

    // Get parsed DE header
    $this->header = new DEheader($this->selectHeaderFile());

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
   * @var SplFileObject
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
      if (count(preg_grep("/\.{$this->de}/", scandir($this->path))) > 3)
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
    return new \SplFileObject("{$this->path}/{$file}");
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

}
