<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ForecastMap.php');

class SurfacePressureForecastMap extends ForecastMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Surface Pressure Forecast Map';
    }

    protected function getLayerName()
    {
        return 'Atlantic';
    }

    protected function requiresTimestamp()
    {
        return true;
    }

    protected function getOverlayMap()
    {
        return null;
    }
}

?>
