<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ForecastMap.php');

class RainfallForecastMap extends ForecastMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Rainfall Forecast Map';
    }

    protected function getLayerName()
    {
        return 'Precipitation_Rate';
    }

    protected function requiresTimestamp()
    {
        return true;
    }

    protected function getOverlayMap()
    {
        return 'uk_overlay_black_full_outline.png';
    }
}

?>
