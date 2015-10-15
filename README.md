# met-office-weather-maps

Copyright © 2015 Wayne D Grant

Licensed under the MIT License

API to fetch weather maps from the [Met Office](http://www.metoffice.gov.uk) using their [DataPoint](http://www.metoffice.gov.uk/datapoint) web services. Written in PHP.

## Overview

The [Met Office](http://www.metoffice.gov.uk) provide an API called [DataPoint](http://www.metoffice.gov.uk/datapoint) which, among other capabilities, exposes web services that allow clients to request a wide selection of time series weather maps for the UK as images.

These maps make a great addition to any UK-centric weather website. However, there are several issues implementors have to overcome:

1. Most of the map images are simple layers with no map. Implementors have to supply their own base and overlay images to fit with the required boundary box.
2. DataPoint has fair use call limits and map types can have very different update schedules. Implementors have to create call schedules that keep their websites up-to-date but do not exceed their call limit.
3. All of the maps are available as a time-series. However, there is no option to request an animated image that combines an entire time series.
4. Most of the map images are simple layers with no indication as to what time they are relevant for.

met-office-weather-maps solves each of these issues:

1. All map images available only as simple layers are processed by met-office-weather-maps to add appropriate base and overlay layers for the correct boundary box around the UK.
2. met-office-weather-maps instruments DataPoint's capabilities API and caches the timestamps of all image web service calls so that it only fetches new map images when new versions are available. This dramatically reduces the number of DataPoint web service calls even if met-office-weather-maps is called on a frequent schedule.
3. met-office-weather-maps automatically creates a GIF animation of each map time series it fetches.
4. Where map images are not timestamped met-office-weather-maps automatically adds a UTC date/time to the top-left corner.
 
For example for this raw layer for precipitation fetched from DataPoint:

![alt tag](precipitation_layer.png)

met-office-weather-maps will create a processed image similar to:

![alt tag](precipitation_processed.png)

and an animated GIF for the entire time series of precipitation images:

![alt tag](precipitation_animated.gif)

The original use case for met-office-weather-maps was as a scheduled job to keep the weather maps on my own weather web site up-to-date. The following instructions concentrate on this particular scenario.

For more examples of met-office-weather-maps in action see [http://www.waynedgrant.com/weather](http://www.waynedgrant.com/weather/maps.html).

## Requirements

1. [DataPoint](http://www.metoffice.gov.uk/datapoint) API key (available for free)
2. PHP version 5.5 or above installed on a web server

## Installation

These instructions will set up met-office-weather-maps on a regular schedule on a generic LAMP stack web server.

* Download the source code for the [latest release](https://github.com/waynedgrant/met-office-weather-maps/releases) and unzip it
* Retrieve a copy of [GifCreator.php](https://github.com/Sybio/GifCreator/blob/master/src/GifCreator/GifCreator.php) by **Clément Guillemain** and place it in the unzipped **met-office-weather-maps/src** directory
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

This example harness code will fetch all available maps into separate folders.

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
