JPLephem
========
JPLephem is a PHP package capable of reading Jet Propulsion Laboratory Development Ephemeris (JPL DE) files in order to calculate the circumstances describing the the positions and velocities of the major planets, Sun and Earth's Moon. This package can also calculate the Earth's Nutations in longitude and obliquity according to the IAU 1980 model, lunar mantle librations and angular velocity as well as the value of TT-TDB at the geocenter.


Usage
-----

Usage is straightforward. Each planetary object as well as the Sun, Moon and Solar System barycenter has it's own class. A few of these objects have additional methods which are tabulated at the [bottom of this page](https://github.com/marando/JPLephem/blob/dev/README.md#classes-for-the-planets-sun-and-moon).


#### Solar System Barycentric Positions

You can find the Solar System barycentric position of any body like this
```php
echo SolarBary::at(2451545.5)->position(new Mars);

Output:

 X: +1.383898803546533E+0 AU
 Y: +5.657785101770087E-3 AU
 Z: -3.472518577530034E-2 AU
VX: +6.004787752641543E-4 AU/d
VY: +1.380701948006297E-2 AU/d
VZ: +6.316813923448646E-3 AU/d
```

#### Relative Positions
Relative positions between any two bodies can be found as such:
```php
Earth::at(2451545.5)->position(new Mercury);
Earth::at(2451545.5)->position(new Moon);
Pluto::at(2451545.5)->position(new Moon));
```


#### The `CartesianVector` Type
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

The object has a string value as seen below, and can also easily be converted across units as shown:
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


#### About DE Versions
Because of its small size, `DE421` is the default DE version used within this package. The first time you run the package within an installation the neccesary files will automatically be downloaded. For larger DE versions this may take quite some time.

##### Specifying the DE Version

If you wish you can specify an alternate DE version like this:
```php
$de405 = Earth::at(2451545.5)->with('DE405');
```
…and when doing this you can still chain other methods by the way:
```php
$de405 = Earth::at(2451545.5)->with('DE405')->position(new Pluto);
```





#### Classes for the Planets, Sun and Moon
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


