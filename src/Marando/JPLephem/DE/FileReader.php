<?php

/*
 * Copyright (C) 2015 Ashley Marando
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
 * Extends SplFileObject to some add additional useful functions.
 */
class FileReader extends SplFileObject
{

    /**
     * Splits the specified line while trimming empty data
     *
     * @param string $line  Line number to split
     * @param string $delim Delimiter to split at
     * @param string $trim  Characters to trim
     *
     * @return array
     */
    public function splitLine($line, $delim = ' ')
    {
        $this->seek($line);

        return $this->splitCurrent($delim);
    }

    protected $splits = [];

    /**
     * Splits the current line while trimming empty data
     *
     * @param string $delim Delimiter to split at
     *
     * @return array
     */
    public function splitCurrent($delim = ' ') {
        $current = $this->current();
        if (empty($this->splits[$current])) {
            $this->splits[$current] = [];
        }

        if (!empty($this->splits[$current][$delim])) {
            return $this->splits[$current][$delim];
        }
        $line = $this->current();
        $trimmed = trim($line, " \t\n\r\0\x0B");
        $columns = explode($delim, $trimmed);
        $filtered = array_filter($columns);

        $values = array_values($filtered);
        $this->splits[$current][$delim] = $values;

        return $values;
    }
}
