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
 * Represents a JPL DE version
 *
 * For more information refer to this document:
 * ftp://ssd.jpl.nasa.gov/pub//eph/planets/README.txt
 *
 * @author Ashley Marando <a.marando@me.com>
 */
class DE
{
    //----------------------------------------------------------------------------
    // Constants
    //----------------------------------------------------------------------------

    /**
     * @var array An array of all known DE versions
     */
    const VERSIONS = [
      102,
      200,
      202,
      403,
      405,
      406,
      410,
      413,
      414,
      418,
      421,
      422,
      423,
      424,
      430,
      '430t',
      431,
      432,
      '432t',
    ];

    //----------------------------------------------------------------------------
    // Constructors
    //----------------------------------------------------------------------------

    /**
     * Creates a new DEVer instance with a version number
     *
     * @param int|string $version
     */
    protected function __construct($version = 421)
    {
        $this->version = $version;
    }

    // // // Static

    /**
     * Creates a new DEVer instance from a string representation of the version
     *
     * @param string $version
     *
     * @return static
     * @throws \Exception
     */
    public static function parse($version)
    {
        // Remove DE if present and convert to lowercase
        $denum = str_replace('de', '', strtolower($version));

        // Try to match the version to all known versions
        $result = array_values(preg_grep("/^{$denum}$/", static::VERSIONS));

        if (count($result) > 0) // Version was found use that
        {
            return new static($result[0]);
        } else // No version found, throw exception
        {
            throw new \Exception("DE{$denum} was not found");
        }
    }

    /**
     * Represents DE102
     *
     * Created September 1981; includes nutations but not librations.
     * Referred to the dynamical equator and equinox of 1950.
     * Covers JED 1206160.5 (-1410 APR 16) to JED 2817872.5 (3002 DEC 22).
     *
     * @return static
     */
    public static function DE102()
    {
        return new static(102);
    }

    /**
     * Represents DE200
     *
     * Created September 1981; includes nutations but not librations.
     * Referred to the dynamical equator and equinox of 2000.
     * Covers JED 2305424.5 (1599 DEC 09)  to  JED 2513360.5 (2169 MAR 31).
     * This ephemeris was used for  the Astronomical Almanac from 1984 to 2003.
     * (See Standish, 1982 and Standish, 1990).
     *
     * @return static
     */
    public static function DE200()
    {
        return new static(200);
    }

    /**
     * Represents DE202
     *
     * Created October 1987; includes nutations and librations.
     * Referred to the dynamical equator and equinox of 2000.
     * Covers JED 2414992.5 (1899 DEC 04) to  JED 2469808.5 (2050 JAN 02).
     *
     * @return static
     */
    public static function DE202()
    {
        return new static(202);
    }

    /**
     * Represents DE403
     *
     * Created May 1993; includes nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 2305200.5 (1599 APR 29) to  JED 2524400.5 (2199 JUN 22).
     * Fit to planetary and lunar laser ranging data.
     * (See Folkner et al. 1994).
     *
     * @return static
     */
    public static function DE403()
    {
        return new static(403);
    }

    /**
     * Represents DE405
     *
     * Created May 1997; includes both nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 2305424.50  (1599 DEC 09)  to  JED 2525008.50  (2201 FEB 20)
     *
     * @return static
     */
    public static function DE405()
    {
        return new static(405);
    }

    /**
     * Represents DE406
     *
     * Created May 1997; includes neither nutations nor librations.
     * Referred to the International Celestial Reference Frame.
     * Spans JED 0625360.5 (-3000 FEB 23) to 2816912.50 (+3000 MAY 06)
     *
     * This is the same integration as DE405, with the accuracy of the
     * interpolating polynomials has been lessened to reduce file size
     * for the longer time span covered by the file.
     *
     * @return static
     */
    public static function DE406()
    {
        return new static(406);
    }

    /**
     * Represents DE410
     *
     * Created April 2003; includes nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 2415056.5 (1900 FEB 06) to JED 2458832.5 (2019 DEC 15).
     * Ephemeris used for Mars Exploration Rover navigation.
     *
     * @return static
     */
    public static function DE410()
    {
        return new static(410);
    }

    /**
     * Represents DE413
     *
     * Created November 2004; includes nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 2414992.5, (1899 DEC 04) to JED 2469872.5 (2050 MAR 07).
     *
     * Created to update the orbit of Pluto to aid in planning
     * for an occultation of a relatively bright star by Charon on 11 July 2005.
     *
     * @return static
     */
    public static function DE413()
    {
        return new static(413);
    }

    /**
     * Represents DE414
     *
     * Created May 2005; includes nutations and librations.
     * Covers JED 2414992.5, (1899 DEC 04) to JED 2469872.5 (2050 MAR 07).
     *
     * Fit to ranging data from MGS and Odyssey through 2003.
     * (See Konopliv et al., 2006.)
     *
     * @return static
     */
    public static function DE414()
    {
        return new static(414);
    }

    /**
     * Represents DE418
     *
     * Created August 2007; includes nutations and librations.
     * Covers JED 2414864.5 (1899 JUL 29) to JED 2470192.5 (2051 JAN 21)
     *
     * @return static
     */
    public static function DE418()
    {
        return new static(418);
    }

    /**
     * Represents DE421
     *
     * Created Feb 2008; includes nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 2414864.5 (1899 JUL 29) to JED 2471184.5 (2053 OCT 09)
     *
     * Fit to planetary and lunar laser ranging data.
     * (See Folkner et al., 2009)
     *
     * @return static
     */
    public static function DE421()
    {
        return new static(421);
    }

    /**
     * Represents DE422
     *
     * Created September 2009; includes nutations and librations.
     * Referred to the International Celestial Reference Frame.
     * Covers JED 625648.5, (-3000 DEC 07) to JED 2816816.5, (3000 JAN 30).
     *
     * Intended for the MESSENGER mission to Mercury.
     * Extended integration time to serve as successor to DE406.
     * Fit to ranging data from MGS and Odyssey through 2003.
     * (See Konopliv et al., 2010.)
     *
     * @return static
     */
    public static function DE422()
    {
        return new static(422);
    }

    /**
     * Represents DE423
     *
     * Created February 2010; includes nutations and librations.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED 2378480.5, (1799 DEC 16) to JED  2524624.5, (2200 FEB 02).
     *
     * Intended for the MESSENGER mission to Mercury.
     *
     * @return static
     */
    public static function DE423()
    {
        return new static(423);
    }

    /**
     * Represents DE430
     *
     * Created April 2013; includes librations and 1980 nutation.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED 2287184.5, (1549 DEC 21) to JED 2688976.5, (2650 JAN 25).
     *
     * @return static
     */
    public static function DE430()
    {
        return new static(430);
    }

    /**
     * Represents DE430t
     *
     * Created April 2013; includes librations and 1980 nutation.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED 2287184.5, (1549 DEC 21) to JED 2688976.5, (2650 JAN 25).
     *
     * In addition, this version of DE430 includes Chebyshev polynomial
     * coefficients fit to the integrated value of TT-TDB evaluated
     * at the geocenter.
     *
     * @return static
     */
    public static function DE430t()
    {
        return new static('430t');
    }

    /**
     * Represents DE431
     *
     *  Created April 2013; includes librations and 1980 nutation.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED -0.3100015.5, (-13200 AUG 15) to JED 8000016.5, (17191 MAR
     * 15).
     *
     * DE430 and DE431 are documented in the following document:
     * http://ipnpr.jpl.nasa.gov/progress_report/42-196/196C.pdf
     *
     * @return static
     */
    public static function DE431()
    {
        return new static(431);
    }

    /**
     * Represents DE432
     *
     * Created April 2014; includes librations but no nutations.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED 2287184.5, (1549 DEC 21) to JED 2688976.5, (2650 JAN 25).
     *
     * DE432 is a minor update to DE430, and is intended primarily to
     * aid the New Horizons project targeting of Pluto.
     *
     * @return static
     */
    public static function DE432()
    {
        return new static(432);
    }

    /**
     * Represents DE432t
     *
     * Created April 2014; includes librations but no nutations.
     * Referred to the International Celestial Reference Frame  version 2.0.
     * Covers JED 2287184.5, (1549 DEC 21) to JED 2688976.5, (2650 JAN 25).
     *
     * DE432 is a minor update to DE430, and is intended primarily to
     * aid the New Horizons project targeting of Pluto.
     *
     * In addition, this version of DE430 includes Chebyshev polynomial
     * coefficients fit to the integrated value of TT-TDB evaluated
     * at the geocenter.
     *
     * @return static
     */
    public static function DE432t()
    {
        return new static('432t');
    }

    //----------------------------------------------------------------------------
    // Properties
    //----------------------------------------------------------------------------

    /**
     * Represents the version of this instance
     *
     * @var type
     */
    public $version;

    //----------------------------------------------------------------------------
    // Functions
    //----------------------------------------------------------------------------
    // // // Overrides

    /**
     * Represents this instance as a string
     *
     * @return string
     */
    public function __toString()
    {
        return "DE{$this->version}";
    }

}
