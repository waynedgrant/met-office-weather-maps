<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('Map.php');

class SurfacePressureExtendedForecastMap extends Map {

    public function __construct($apiKey, $workingFolder) {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function getName() {
        return 'Surface Pressure Extended Forecast Map';
    }

    protected function getCapabilitiesUrl() {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/image/wxfcs/surfacepressure/json/capabilities?key={0}',
            array($this->apiKey));
    }

    protected function readLatestTimestamp($mapCapabilities) {
        return $mapCapabilities->BWSurfacePressureChartList->BWSurfacePressureChart[0]->DataDate;
    }

    protected function readAvailableTimesteps($mapCapabilities) {
        $surfacePressureCharts = $mapCapabilities->BWSurfacePressureChartList->BWSurfacePressureChart;

        $timesteps = array();

        foreach ($surfacePressureCharts as $surfacePressureChart) {
            $timesteps[] = $surfacePressureChart->ForecastPeriod;
        }

        sort($timesteps, SORT_NUMERIC);

        return $timesteps;
    }

    protected function generateMapImageUrl($timestep, $timestamp) {
        return MessageFormatter::formatMessage('en_GB',
            'http://datapoint.metoffice.gov.uk/public/data/image/wxfcs/surfacepressure/{0}?timestep={1}&key={2}',
            array($this->getImageFormat(), $timestep, $this->apiKey));
    }

    protected function getImageFormat() {
        return 'gif';
    }

    protected function getWidth() {
        return 891;
    }

    protected function getHeight() {
        return 601;
    }

    protected function requiresTimestamp() {
        return false;
    }

    protected function getBaseMap() {
        return null;
    }

    protected function getOverlayMap() {
        return null;
    }

    protected function getDownloadErrorMap() {
        return 'error_downloading_map_891x601.png';
    }
}

?>
