<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('LayerMap.php');

abstract class ObservationMap extends LayerMap
{
    protected function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getCapabilitiesUrl()
    {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/layer/wxobs/all/json/capabilities?key={0}',
            array($this->apiKey));
    }

    protected function getLastestTimestamp($mapCapabilities)
    {
        $layer = $this->findLayer($mapCapabilities, $this->getLayerName());

        return $layer->Service->Times->Time[0];
    }

    protected function getAvailableTimesteps($mapCapabilities)
    {
        $layer = $this->findLayer($mapCapabilities, $this->getLayerName());

        $timesteps = $layer->Service->Times->Time;

        rsort($timesteps, SORT_STRING);

        return $timesteps;
    }

    protected function getMapImageUrl($timestep, $timestamp)
    {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/layer/wxobs/{0}/png?TIME={1}Z&key={2}',
            array($this->getLayerName(), $timestep, $this->apiKey));
    }

    abstract protected function getLayerName();
}

?>
