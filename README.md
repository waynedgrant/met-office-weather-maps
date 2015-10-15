# met-office-weather-maps

Copyright © 2015 Wayne D Grant

Licensed under the MIT License

API to fetch weather maps from the [Met Office](http://www.metoffice.gov.uk) using their [DataPoint](http://www.metoffice.gov.uk/datapoint) web services. Written in PHP.

## Overview

TODO

## Requirements

* [DataPoint](http://www.metoffice.gov.uk/datapoint) API key (available for free)
* PHP version 5.5 and above installed on the web server

## Installation

These instructions will set up met-office-weather-maps on a regular schedule on a generic LAMP stack web server.

* Download the source code for the [latest release](https://github.com/waynedgrant/met-office-weather-maps/releases) and unzip it
* Get a copy of [GifCreator.php](https://github.com/Sybio/GifCreator/blob/master/src/GifCreator/GifCreator.php) by **Clément Guillemain** and place it in the unzipped **met-office-weather-maps/src** directory
* Write a harness in PHP to fetch the maps you need (see **API** and **Example Harness** below)
* Upload all files in **met-office-weather-maps/src** and your **harness** to a directory on your web server
* Set up a cron schedule to kick off your harness regularly (e.g every 15 minutes)

## API

| PHP Class                          | Available Formats | DataPoint Reference                                                            |
|------------------------------------|-------------------|--------------------------------------------------------------------------------|
| CloudCoverAndRainfallForecastMap   | gif, png*         | N/A                                                                            |
| CloudCoverForecastMap              | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/cloud-cover-forecast-map-layer   |
| InfraredSatelliteObservationMap    | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/satellite-infrared-map-layer     |
| LightningObservationMap            | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/lightning-strike-map-layer       |
| RainfallForecastMap                | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/precipitation-forecast-map-layer |
| RainfallObservationMap             | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/rainfall-radar-map-layer         |
| SurfacePressureExtendedForecastMap | gif               | http://www.metoffice.gov.uk/datapoint/product/surface-pressure-charts          |
| SurfacePressureForecastMap         | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/pressure-forecast-map-layer      |
| TemperatureForecastMap             | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/temperature-forecast-map-layer   |
| VisibleSatelliteObservationMap     | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/satellite-visible-map-layer      |

\* - Excepting animation image

### Example Harness

```php
<?php

require_once('CloudCoverAndRainfallForecastMap.php');
require_once('CloudCoverForecastMap.php');
require_once('InfraredSatelliteObservationMap.php');
require_once('LightningObservationMap.php');
require_once('RainfallForecastMap.php');
require_once('RainfallObservationMap.php');
require_once('SurfacePressureExtendedForecastMap.php');
require_once('SurfacePressureForecastMap.php');
require_once('TemperatureForecastMap.php');
require_once('VisibleSatelliteObservationMap.php');

define(API_KEY, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');

$dirName = dirname(__FILE__ );

// Fetch all available maps into separate folders
$maps = array(
    new CloudCoverAndRainfallForecastMap(API_KEY, $dirName . '/cloud-rain-fcast'),
    new CloudCoverForecastMap(API_KEY, $dirName . '/cloud-fcast'),
    new InfraredSatelliteObservationMap(API_KEY, $dirName . '/ir-sat-obs'),
    new LightningObservationMap(API_KEY, $dirName . '/lightning-obs'),
    new RainfallForecastMap(API_KEY, $dirName . '/rain-fcast'),
    new RainfallObservationMap(API_KEY, $dirName . '/rain-obs'),
    new SurfacePressureExtendedForecastMap(API_KEY, $dirName . '/ext-pressure-fcast'),
    new SurfacePressureForecastMap(API_KEY, $dirName . '/pressure-fcast'),
    new TemperatureForecastMap(API_KEY, $dirName . '/temp-fcast'),
    new VisibleSatelliteObservationMap(API_KEY, $dirName . '/vis-sat-obs'));

foreach ($maps as $map) {
    $map->fetch();
}

?>
```
