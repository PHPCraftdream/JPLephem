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

/**
 * Represents a Solar system object present in a JPL DE ephemeris
 *
 * @property int    $id   JPL DE ephemeris ID
 * @property string $name Name
 * @property string $abr  Abreviation
 */
class SSObj
{
    //----------------------------------------------------------------------------
    // Constructors
    //----------------------------------------------------------------------------

    /**
     * Creates a new JPL DE solar system object instance
     *
     * @param  type $id JPL DE ID of the object
     *
     * @throws Exception     Occurs if the id does not exist
     */
    public function __construct($id)
    {
        // Set the id
        $this->id = $id;

        // Set object info and throw exception if it is not found
        if (static::getObjInfo($id, $this->name, $this->abr) == -1) {
            throw new Exception("No information for id #{$id}");
        }
    }

    // // // Static

    /**
     * Represents the Solar System barycenter
     *
     * @return static
     */
    public static function SolarBary()
    {
        return new static(0);
    }

    /**
     * Represents Mercury
     *
     * @return static
     */
    public static function Mercury()
    {
        return new static(1);
    }

    /**
     * Represents Venus
     *
     * @return static
     */
    public static function Venus()
    {
        return new static(2);
    }

    /**
     * Represents Earth
     *
     * @return static
     */
    public static function Earth()
    {
        return new static(301);
    }

    /**
     * Represents the Earth-Moon barycenter
     *
     * @return static
     */
    public static function EarthBary()
    {
        return new static(3);
    }

    /**
     * Represents Mars
     *
     * @return static
     */
    public static function Mars()
    {
        return new static(4);
    }

    /**
     * Represents Jupiter
     *
     * @return static
     */
    public static function Jupiter()
    {
        return new static(5);
    }

    /**
     * Represents Saturn
     *
     * @return static
     */
    public static function Saturn()
    {
        return new static(6);
    }

    /**
     * Represents Uranus
     *
     * @return static
     */
    public static function Uranus()
    {
        return new static(7);
    }

    /**
     * Represents Neptune
     *
     * @return static
     */
    public static function Neptune()
    {
        return new static(8);
    }

    /**
     * Represents Pluto
     *
     * @return static
     */
    public static function Pluto()
    {
        return new static(9);
    }

    /**
     * Represents the Earth's Moon
     *
     * @return static
     */
    public static function Moon()
    {
        return new static(10);
    }

    /**
     * Represents the Sun
     *
     * @return static
     */
    public static function Sun()
    {
        return new static(11);
    }

    //----------------------------------------------------------------------------
    // Properties
    //----------------------------------------------------------------------------

    /**
     * JPL DE ephemeris ID
     *
     * @var int
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Abbreviation
     *
     * @var string
     */
    protected $abr;

    public function __get($name)
    {
        switch ($name) {
            case 'id':
            case 'name':
            case 'abr':
                return $this->{$name};
        }
    }

    //----------------------------------------------------------------------------
    // Functions
    //----------------------------------------------------------------------------

    /**
     * Returns object info for a JPL DE ephemeris id, returns -1 on if the id is
     * not found
     *
     * @param  int    $id   JPL DE ephemeris ID
     * @param  string $name Name
     * @param  string $abr  Abbreviation
     *
     * @return int          Status Code (0 = OK, -1 = Not found)
     */
    protected static function getObjInfo($id, &$name, &$abr)
    {
        $obj = [
          0   => ['Solar System barycenter', 'SSB'],
          1   => ['Mercury', 'Me'],
          2   => ['Venus', 'V'],
          3   => ['Earth-Moon barycenter', 'EMB'],
          301 => ['Earth', 'E'],
          4   => ['Mars', 'M'],
          5   => ['Jupiter', 'J'],
          6   => ['Saturn', 'S'],
          7   => ['Uranus', 'U'],
          8   => ['Neptune', 'N'],
          9   => ['Pluto', 'P'],
          10  => ['Moon', 'Lu'],
          11  => ['Sun', 'Su'],
        ];

        // Check if id exists
        if (!key_exists($id, $obj)) {
            return -1;
        }

        // Set the name and abbreviation
        $name = $obj[$id][0];
        $abr  = $obj[$id][1];

        // Return success
        return 0;
    }

    // // //

    /**
     * Represents this instance as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name ? $this->name : (string)$this->id;
    }

}
