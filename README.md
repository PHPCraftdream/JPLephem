JPLephem
========
JPLephem is a PHP package that is capable of reading Jet Propulsion Laboratory Development Ephemeris (JPL DE) files in order to calculate the positions and velocities of the major planets, the Sun and Earth's Moon. The package can also interpolate additional information held within the ephemeris files (depending upon the DE version) such as the Earth's Nutations in longitude and obliquity according to the IAU 1980 model, lunar mantle librations and angular velocity as well as the value of TT-TDB at the geocenter.


Usage
-----
Usage is fairly straightfoward, first you'll want to import the reader class and instantiate a new reader instance:
```php
use \Marando\JPLephem\DE\Reader;
$de = new Reader();
```

Then just specify the observation JDE:
```php
$de->jde(2451545.5);
```
And you're ready to interpolate positions!  

#### Solar System Barycentric Positions
Solar system barycentric positions can be found by calling the `position()` method with only the first parameter as shown:
```php
$de->position(SSObj::Mercury());
$de->position(SSObj::Pluto());
```
The `position()` method returns an array of six values representing respectively the position x, y and z and velocity x, y and z coordinates in astronomical units. For example: 
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
Depending on the DE version number, there may be additional information held within the ephemeris files, such as the Earth's nutations and lunar libration angles. To interpolate these values you can use the `interp()` method and provide the DE element number and number of components which can be found in JPL's documentation.

For example, to find the Earth's nutations in longitude and obliquity according to the 1980 IAU model, you first need to know that the nutations are element 12 and there are 2 components, then call:
```php
$nutations = $de->interp(12, 2);
```
The results are an array in the units specified by the DE version. For the Earth's nutations, the first element is nutation in longitude (Δψ), and the second is nutation in obliquity (Δε), and in this case both values are expressed radians:
```
Array
(
    [0] => -6.7464277868885E-5  // Δψ in radians
    [1] => -2.8042821016313E-5  // Δε in radians
)
```






Usage is fairly straightforward. Each planetary object as well as the Sun, Moon and Solar System barycenter has it's own class which you then supply Julian ephemeris day (JDE) assumed to already be converted to Barycentric Dynamical Time (TDB). You can then find the position by calling the `position()` method, which is demonstrated below. Also, a few of these objects have additional methods which are tabulated at the [bottom of this page](https://github.com/marando/JPLephem/blob/dev/README.md#classes-for-the-planets-sun-and-moon).


### Solar System Barycentric Positions

You can find the Solar System barycentric position of any body like this
```php
// Find position of mars at JDE 2451545.5 as seen from the Solar System barycenter
echo SolarBary::at(2451545.5)->position(new Mars);

Output:

 X: +1.383898803546533E+0 AU
 Y: +5.657785101770087E-3 AU
 Z: -3.472518577530034E-2 AU
VX: +6.004787752641543E-4 AU/d
VY: +1.380701948006297E-2 AU/d
VZ: +6.316813923448646E-3 AU/d
```

### Relative Positions
Relative positions between any two bodies can be found as such:
```php
Earth::at(2451545.5)->position(new Mercury);  // Mercury as seen from Earth
Earth::at(2451545.5)->position(new Moon);     // Earth's Moon as seen from Earth
Pluto::at(2451545.5)->position(new Moon));    // Earth's Moon as seen from Pluto
```


### The `CartesianVector` Type
All position results are returned using the `CartesianVector` type, which stores rectangular position and velocity components.

```php
$vector = new CartesianVector();

// Position
$vector->x;
$vector->y;
$vector->z;

// Velocity
$vector->vx;
$vector->vy;
$vector->vz;
```

The object has a string value as seen below, and can also easily be converted across the units `AU AU/d`, `km km/d` and `km km/s` as shown:
```php
echo Earth::at(2451545.5)->position(new Mercury)->setUnit('km km/d');

Output:

 X: +9.928470192706089E+6 km
 Y: -1.928100303495806E+8 km
 Z: -8.780488602095641E+7 km
VX: +5.793631942043564E+6 km/d
VY: -1.951149481528082E+5 km/d
VZ: -4.842830777990772E+5 km/d
```

Or you can get the components of a position like such:
```php
$saturn = Earth::at(2451545.5)->position(new Saturn);

echo $saturn->x->mi;  // get x-coord in miles       Output: 612578251.23309
echo $saturn->x->km;  // get x-coord in kilometers  Output: 985849133.15246
```

The underlying components of the `CartesianVector` type are comprised of the `Distance` and `Velocity` types from the Units package documented further [here](https://github.com/marando/Units).


### About DE Versions
Because of its small size, `DE421` is the default DE version used within this package. The first time you run the package within any installation the neccesary files will be automatically downloaded. For larger DE versions this may take quite some time.

### Specifying the DE Version

If you wish you can specify an alternate DE version like this:
```php
$de405 = Earth::at(2451545.5)->with('DE405');
```
…and when doing this you can still chain other methods by the way:
```php
$de405 = Earth::at(2451545.5)->with('DE405')->position(new Pluto);
```





### Classes for the Planets, Sun and Moon
Here is a summary of each class provided by this package as well as any additional abilities each class provides:

Class       | Description             | Provides
------------|-------------------------|---------------------------------
`SolarBary` | Solar System barycenter | `ttTDB()`
`Sun`       | Sun                     |
`Mercury`   | Mercury                 |
`Venus`     | Venus                   |
`EarthBary` | Earth-Moon barycenter   |
`Earth`     | Earth                   | `nutation()`
`Moon`      | Moon                    | `libration()`, `mantleVelocity()`
`Mars`      | Mars                    |
`Jupiter`   | Jupiter                 |
`Saturn`    | Saturn                  |
`Uranus`    | Uranus                  |
`Neptune`   | Neptune                 |
`Pluto`     | Pluto                   |


