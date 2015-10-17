<?php

namespace Marando\JPLephem\DE;

use \SplFileObject;

class FileReader extends SplFileObject {

  public function readBytes($byte1, $byteN) {

  }

  public function splitLine($line, $delim = ' ', $trim = " \t\n\r\0\x0B") {
    $this->seek($line);
    return $this->splitCurrent($delim, $trim);
  }

  public function splitCurrent($delim = ' ', $trim = " \t\n\r\0\x0B") {
    $line     = $this->current();
    $trimmed  = trim($line, $trim);
    $columns  = explode($delim, $trimmed);
    $filtered = array_filter($columns);

    return array_values($filtered);
  }

}
