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

class SSObj {

  public $id;

  protected function __construct($id) {
    $this->id = $id;
  }

  public static function SolarBary() {
    return new static(0);
  }

  public static function Mercury() {
    return new static(1);
  }

  public static function Venus() {
    return new static(2);
  }

  public static function Earth() {
    return new static(301);
  }

  public static function EarthBary() {
    return new static(3);
  }

  public static function Mars() {
    return new static(4);
  }

  public static function Jupiter() {
    return new static(5);
  }

  public static function Saturn() {
    return new static(6);
  }

  public static function Uranus() {
    return new static(7);
  }

  public static function Neptune() {
    return new static(8);
  }

  public static function Pluto() {
    return new static(9);
  }

  public static function Moon() {
    return new static(10);
  }

  public static function Sun() {
    return new static(11);
  }

}
