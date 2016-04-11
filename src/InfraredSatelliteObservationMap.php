<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ObservationMap.php');

class InfraredSatelliteObservationMap extends ObservationMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Infrared Satellite Observation Map';
    }

    protected function getLayerName()
    {
        return 'SATELLITE_Infrared_Fulldisk';
    }

    protected function requiresTimestamp()
    {
        return false;
    }

    protected function getBaseMap()
    {
        return null;
    }

    protected function getOverlayMap()
    {
        return 'uk_overlay_yellow_cutout_outline.png';
    }
}

?>
