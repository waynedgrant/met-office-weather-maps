<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('LayerMap.php');

abstract class ForecastMap extends LayerMap {

    protected function __construct($apiKey, $workingFolder) {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getCapabilitiesUrl() {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/layer/wxfcs/all/json/capabilities?key={0}',
            array($this->apiKey));
    }

    protected function readLatestTimestamp($mapCapabilities){
        $layer = $this->findLayer($mapCapabilities, $this->getLayerName());

        $defaultTime = '@defaultTime'; // This isn't a valid php variable name, hence this trick
        return $layer->Service->Timesteps->$defaultTime;
    }

    protected function readAvailableTimesteps($mapCapabilities) {
        $layer = $this->findLayer($mapCapabilities, $this->getLayerName());

        $timesteps = $layer->Service->Timesteps->Timestep;

        sort($timesteps, SORT_NUMERIC);

        return $timesteps;
    }

    abstract protected function getLayerName();

    protected function getBaseMap() {
        return 'uk_base_colour.png';
    }

    protected function generateMapImageUrl($timestep, $timestamp) {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/layer/wxfcs/{0}/{1}?RUN={2}Z&FORECAST={3}&key={4}',
            array($this->getLayerName(), $this->getImageFormat(), $timestamp, $timestep, $this->apiKey));
    }
}

?>
