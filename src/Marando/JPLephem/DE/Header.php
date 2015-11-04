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

class Header {

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($file) {
    // Check if file exists
    if (!file_exists($file))
      throw new Exception("Invalid path {$file}");

    // Create new FileReader instance
    $fRead = new FileReader($file);

    // Parse each section
    $this->parseMeta($fRead);
    $this->parseGroup1010($fRead);
    $this->parseGroup1030($fRead);
    $this->parseGroup1040and1041($fRead);
    $this->parseGroup1050($fRead);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  protected $desc;
  protected $startEpoch;
  protected $finalEpoch;
  protected $blockSize;
  protected $kSize;
  protected $nCoeff;
  protected $const;
  protected $coeffStart = [];
  protected $coeffCount = [];
  protected $coeffSets  = [];

  public function __get($name) {
    switch ($name) {
      case 'desc':
      case 'startEpoch':
      case 'finalEpoch':
      case 'blockSize':
      case 'kSize':
      case 'nCoeff':
      case 'const':
      case 'coeffStart':
      case 'coeffCount':
      case 'coeffSets':
        return $this->{$name};
    }
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
    $this->const = new Constant();

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
