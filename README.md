JPLephem
========
JPLephem is a PHP package capable of reading Jet Propulsion Laboratory Development Ephemeris (JPL DE) files in order to calculate the circumstances describing the the positions and velocities of the major planets, Sun and Earth's Moon. The package can also calculate the Earth's Nutations in longitude and obliquity according to the IAU 1980 model, Lunar mantle librations and angular velocity as well as the value of TT-TDB at the geocenter.


Usage
-----

Usage is straightforward as each planetary object (including the Sun and Moon) has it's own object. Auxilary properties are held within the relavant object and are described at the bottom of this page.


#### Finding Positions

##### Solar System Barycentric Positions

You can find the position of any body relative to the Solar System barycenter for a given JDE like this:
```php
echo SolarBary::at(2451545.5)->position(new Mars);
```

##### Relative Positions
Relative positions between two bodies can be found as such:
```php
echo Earth::at(2451545.5)->position(new Mercury);

Output:

 X: +6.636772399398923E-2 AU
 Y: -1.288855445918994E+0 AU
 Z: -5.869394103679338E-1 AU
VX: +3.872803747094754E-2 AU/d
VY: -1.304262869784337E-3 AU/d
VZ: -3.237232425388239E-3 AU/d
```

#### About DE Versions
Because of its small size, `DE421` is the default DE version used within this package. The first time you run the package within an installation the neccesary files will automatically be downloaded.

If you wish you can specify an alternate DE version like this:
```php
$de405 = Earth::at(2451545.5)->with('DE405')->position(new Pluto);
```


#### The `CartesianVector` Type
All ephemeris position results are returned using the `CartesianVector` type, which provides the rectangular position and velocity of an object.

For string values, you can easily change between units as shown:
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

The underlying elements of 




Classes for the Planets, Sun and Moon
-------
Class       | Description             | Provides
------------|-------------------------|------------
`SolarBary` | Solar System barycenter | `ttTDB()`
`Sun`       | Sun                     |
`Mercury`   | Mercury                 |
`Venus`     | Venus                   |
`EarthBary` | Earth-Moon barycenter   |
`Earth`     | Earth                   | `nutation()`
`Moon`      | Moon                    | `libration()`, `mantleVelocity()`


