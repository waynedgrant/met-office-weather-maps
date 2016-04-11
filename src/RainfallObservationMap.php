<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ObservationMap.php');

class RainfallObservationMap extends ObservationMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Rainfall Observation Map';
    }

    protected function getLayerName()
    {
        return 'RADAR_UK_Composite_Highres';
    }

    protected function requiresTimestamp()
    {
        return true;
    }

    protected function getBaseMap()
    {
        return 'uk_base_colour.png';
    }

    protected function getOverlayMap()
    {
        return 'uk_overlay_black_full_outline.png';
    }
}

?>
