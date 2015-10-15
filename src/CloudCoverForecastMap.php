<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ForecastMap.php');

class CloudCoverForecastMap extends ForecastMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getLayerName()
    {
        return 'Total_Cloud_Cover';
    }

    protected function requiresTimestamp()
    {
        return true;
    }

    protected function getBaseMap()
    {
        return "uk_base_colour.png";
    }

    protected function getOverlayMap()
    {
        return "uk_overlay_black_full_outline.png";
    }
}

?>