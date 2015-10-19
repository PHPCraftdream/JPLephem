<?php

namespace Marando\JPLephem\DE;

use \SplFileObject;

/**
 * Extends SplFileObject to some add additional useful functions
 */
class FileReader extends SplFileObject {

  public function readBytes($byte1, $byteN) {

  }

  /**
   * Splits the specified line while trimming empty data
   *
   * @param string $line  Line number to split
   * @param string $delim Delimiter to split at
   * @param string $trim  Characters to trim
   *
   * @return array
   */
  public function splitLine($line, $delim = ' ', $trim = " \t\n\r\0\x0B") {
    $this->seek($line);
    return $this->splitCurrent($delim, $trim);
  }

  /**
   * Splits the current line while trimming empty data
   * 
   * @param string $delim Delimiter to split at
   * @param string $trim  Characters to trim
   *
   * @return array
   */
  public function splitCurrent($delim = ' ', $trim = " \t\n\r\0\x0B") {
    $line     = $this->current();
    $trimmed  = trim($line, $trim);
    $columns  = explode($delim, $trimmed);
    $filtered = array_filter($columns);

    return array_values($filtered);
  }

}
