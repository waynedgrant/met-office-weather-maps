<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('ObservationMap.php');

class LightningObservationMap extends ObservationMap
{
    public function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getLayerName()
    {
        return 'ATDNET_Sferics';
    }

    protected function requiresTimestamp()
    {
        return true;
    }

    protected function getBaseMap()
    {
        return "uk_base_greyscale.png";
    }

    protected function getOverlayMap()
    {
        return null;
    }
}

?>
