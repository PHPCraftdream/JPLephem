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
And you're ready to interpolate positions:
```php
$de->position(SSObj::Mercury());
$de->position(SSObj::Pluto());
```
The results are an array of 6 values representing respectively the position and velocity x, y and z coordinates. For example: 
```php
print_r( $de->position(SSObj::Pluto()) );

Output:
Array
(
    [0] => -9.8809726622956
    [1] => -27.982087174796
    [2] => -5.7552504336267
    [3] => 0.0030341820284709
    [4] => -0.0011342010879702
    [5] => -0.0012681328951126
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


