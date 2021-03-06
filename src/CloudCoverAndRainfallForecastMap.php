<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ForecastMap.php');

class CloudCoverAndRainfallForecastMap extends ForecastMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Cloud Cover and Rainfall Forecast Map';
    }

    protected function getLayerName()
    {
        return 'Total_Cloud_Cover_Precip_Rate_Overlaid';
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
