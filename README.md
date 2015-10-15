# met-office-weather-maps

Copyright © 2015 Wayne D Grant

Licensed under the MIT License

API to fetch weather maps from the [Met Office](http://www.metoffice.gov.uk) using their [DataPoint](http://www.metoffice.gov.uk/datapoint) web services. Written in PHP.

## Overview

TODO

## Requirements

TODO

## Installation

TODO

## API

| PHP Class                          | Type        | Base Map          | Overlay Map                      | Available Formats | DataPoint Reference                                                            |
|------------------------------------|-------------|-------------------|----------------------------------|-------------------|--------------------------------------------------------------------------------|
| CloudCoverAndRainfallForecastMap   | Forecast    | uk_base_colour    | uk_overlay_black_full_outline    | gif, png*         | N/A                                                                            |
| CloudCoverForecastMap              | Forecast    | uk_base_colour    | uk_overlay_black_full_outline    | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/cloud-cover-forecast-map-layer   |
| InfraredSatelliteObservationMap    | Observation | N/A               | uk_overlay_yellow_cutout_outline | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/satellite-infrared-map-layer     |
| LightningObservationMap            | Observation | uk_base_greyscale | N/A                              | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/lightning-strike-map-layer       |
| RainfallForecastMap                | Forecast    | uk_base_colour    | uk_overlay_black_full_outline    | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/precipitation-forecast-map-layer |
| RainfallObservationMap             | Observation | uk_base_colour    | uk_overlay_black_full_outline    | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/rainfall-radar-map-layer         |
| SurfacePressureExtendedForecastMap | Forecast    | N/A               | N/A                              | gif               | http://www.metoffice.gov.uk/datapoint/product/surface-pressure-charts          |
| SurfacePressureForecastMap         | Forecast    | uk_base_greyscale | N/A                              | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/pressure-forecast-map-layer      |
| TemperatureForecastMap             | Forecast    | uk_base_greyscale | uk_overlay_black_full_outline    | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/temperature-forecast-map-layer   |
| VisibleSatelliteObservationMap     | Observation | N/A               | uk_overlay_yellow_cutout_outline | gif, png*         | http://www.metoffice.gov.uk/datapoint/product/satellite-visible-map-layer      |

``* - Excepting animation image

## Example

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
