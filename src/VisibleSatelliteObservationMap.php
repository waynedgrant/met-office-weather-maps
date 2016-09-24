<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ObservationMap.php');

class VisibleSatelliteObservationMap extends ObservationMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName()
    {
        return 'Visible Satellite Observation Map';
    }

    protected function getLayerName()
    {
        return 'SATELLITE_Visible_N_Section';
    }

    protected function requiresTimestamp()
    {
        return false;
    }

    protected function getBaseMap()
    {
        return 'blank_black.png';
    }

    protected function getOverlayMap()
    {
        return 'uk_overlay_yellow_cutout_outline.png';
    }
}

?>
