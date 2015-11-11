JPLephem
========
JPLephem is a PHP package capable of reading Jet Propulsion Laboratory Development Ephemeris (JPL DE) files and caclulating the positions and velocities of the major planets, the Sun and Earth's Moon. The package can also interpolate additional information held within the ephemeris files (depending upon the DE version) such as the Earth's Nutations in longitude and obliquity according to the IAU 1980 model, lunar mantle librations and angular velocity as well as the value of TT-TDB at the geocenter.

Installation
------------
#### With Composer

```
$ composer require marando/jplephem
```

Usage
-----
Usage is fairly straightfoward, first you'll want to import the reader class and instantiate a new reader instance:
```php
use \Marando\JPLephem\DE\Reader;
$de = new Reader();
```

Then just specify the observation JDE:
```php
// JDE should be in Barycentric Dynamical Time (TDB)
$de->jde(2451545.5);
```
And you're ready to interpolate positions!  

#### Solar System Barycentric Positions
Solar system barycentric positions can be found by calling the `position()` method with only the first parameter as shown:
```php
$de->position(SSObj::Mercury());
$de->position(SSObj::Pluto());
```
The `position()` method returns an array of six values representing respectively the position x, y and z and velocity x, y and z coordinates. For example: 
```php
print_r( $de->position(SSObj::Pluto()) );

Output:
Array
(
    [0] => -9.8809726622956     // x position
    [1] => -27.982087174796     // y position
    [2] => -5.7552504336267     // z position
    [3] => 0.0030341820284709   // x velocity
    [4] => -0.0011342010879702  // y velocity
    [5] => -0.0012681328951126  // z velocity
)
```
By default the distance units are in AU and the velocity units are in AU/day, but they can be changed to km and km/day by using the following methods:
```php
$de->km();  // Use km & km/day
$de->au();  // Use AU & AU/day
```

#### Target -> Center Relative Positions
To find the relative position of a target body with respect to a defined center body, simply call the `position()` method as above, and provided a second argument defining the center:
```php
              // Target       // Center
$de->position(SSObj::Pluto(), SSObj::Mercury());
```

#### Adjusting for Light-Time
You can also find positions adjusted for light travel time by calling the `observe()` method:
```php
$de->observe(SSObj::Pluto(), SSObj::Earth());
```
To get the light-time simply supply a third pass-by reference variable to the call:
```php
$de->observe(SSObj::Pluto(), SSObj::Earth(), $lt);

echo $lt;  // Result: 0.179 days
```

#### Interpolating Additional Values
Depending on the DE version, there may be additional information held within the ephemeris files, such as the Earth's nutations and lunar libration angles. To interpolate these values you can use the `interp()` method and provide the element number and number of components shown below (which can be found in [JPL's documentation](ftp://ssd.jpl.nasa.gov/pub/eph/planets/ascii/ascii_format.txt))


 Description                      | Element  | Components | Units
----------------------------------|----------|------------|-------------
 Mercury—Pluto, Sun & Moon        | 1–11     | 6          | au, au/day
 Earth Nutations (IAU 1980 model) | 12       | 2          | radians
 Lunar mantle libration           | 13       | 3          | radians
 Lunar mantle angular velocity    | 14       | 3          | radians/day
 TT-TDB (at geocenter)            | 15 or 17 | 1          | seconds

For example, to find the Earth's nutations in longitude and obliquity according to the 1980 IAU model, you first need to know that the nutations are element 12 and there are 2 components, then call:
```php
$nutations = $de->interp(12, 2);
```
The results are an array in the units specified by the DE version. For example, for the Earth's nutations, the first element is nutation in longitude (Δψ), and the second is nutation in obliquity (Δε), and in this case both values are expressed radians:
```php
Array
(
    [0] => -6.7464277868885E-5  // Δψ in radians
    [1] => -2.8042821016313E-5  // Δε in radians
)
```




#### Specifying the DE Version
Because of its small size, `DE421` is the default DE version used within this package. The first time you run the package within any installation the neccesary files will be automatically downloaded. For larger DE versions this may take quite some time.

If you wish you can specify an alternate DE version like this:
```php
$DE431 = new Reader(DE::DE431());
```





