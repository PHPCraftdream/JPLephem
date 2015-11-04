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
