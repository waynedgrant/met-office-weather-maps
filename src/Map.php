<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

abstract class Map
{
    protected $apiKey;
    private $workingFolder;
    private $infoJsonFile;
    private $imagesDir;

    protected function __construct($apiKey, $workingFolder)
    {
        $this->apiKey = $apiKey;
        $this->workingFolder = $workingFolder;
        $this->infoJsonFile = $workingFolder . '/info.json';
        $this->imagesDir = dirname(__FILE__ ) . '/images/';
    }

    public function fetch()
    {
        $mapCapabilities = json_decode(file_get_contents($this->getCapabilitiesUrl()));

        $latestTimestamp = $this->readLatestTimestamp($mapCapabilities);
        $currentTimestamp = $this->getCurrentTimestamp();

        if ($latestTimestamp != $currentTimestamp)
        {
            $baseDateTime = $this->createDateTime($latestTimestamp);
            $imagesInfo = array();
            $timesteps = $this->readAvailableTimesteps($mapCapabilities);

            for ($i = 0; $i < count($timesteps); $i++)
            {
                $timestep = $timesteps[$i];

                $mapImageUrl = $this->generateMapImageUrl($timestep, $latestTimestamp);

                $mapImageFile = $this->workingFolder . '/' . $i . '.' . $this->getImageFormat();

                copy($mapImageUrl, $mapImageFile);

                $imagesInfo[] = $this->processMapImage($mapImageFile, $baseDateTime, $timestep);
            }

            $this->writeInfoJson($latestTimestamp, $baseDateTime, $imagesInfo);
        }
    }

    abstract protected function getName();
    abstract protected function getCapabilitiesUrl();
    abstract protected function readLatestTimestamp($mapCapabilities);
    abstract protected function readAvailableTimesteps($mapCapabilities);
    abstract protected function getImageFormat();
    abstract protected function getWidth();
    abstract protected function getHeight();
    abstract protected function generateMapImageUrl($timestep, $timestamp);
    abstract protected function getBaseMap();
    abstract protected function getOverlayMap();
    abstract protected function requiresTimestamp();

    private function getCurrentTimestamp()
    {
        if (!is_dir($this->workingFolder))
        {
            mkdir($this->workingFolder, 0777, true);
        }

        if (is_file($this->infoJsonFile))
        {
            $info = json_decode(file_get_contents($this->infoJsonFile));
            $currentTimestamp = $info->datapoint_timestamp;
        }

        return $currentTimestamp;
    }

    private function processMapImage($mapImageFile, $baseDateTime, $timestep)
    {
        $mapImageDateTime = $this->calculateMapImageDateTime($baseDateTime, $timestep);
        $timestepMins = $this->calculateTimestepMins($baseDateTime, $timestep);

        $mapInfo = array(
            'file' => basename($mapImageFile),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'time' => $mapImageDateTime->format('Y-m-d H:i T'),
            'timestep_mins' => $timestepMins);

        $baseMap = $this->getBaseMap();

        if (!is_null($baseMap))
        {
            $baseMapFile = $this->imagesDir . $baseMap;
            $this->addBaseMap($mapImageFile, $baseMapFile);
        }

        $overlayMap = $this->getOverlayMap();

        if (!is_null($overlayMap))
        {
            $overlayMapFile = $this->imagesDir . $overlayMap;
            $this->addOverlayMap($mapImageFile, $overlayMapFile);
        }

        if ($this->requiresTimestamp())
        {
            $this->timestampMapImage($mapImageFile, $mapImageDateTime);
        }

        return $mapInfo;
    }

    private function addBaseMap($mapImageFile, $baseMapFile)
    {
        $this->combineMaps($mapImageFile, $baseMapFile, $mapImageFile);
    }

    private function addOverlayMap($mapImageFile, $overlayMapFile)
    {
        $this->combineMaps($overlayMapFile, $mapImageFile, $mapImageFile);
    }

    private function calculateMapImageDateTime($baseDateTime, $timestep)
    {
        if (is_numeric($timestep)) // Timestep is hours relative of timestamp date/time
        {
            $dateTime = clone $baseDateTime;
            date_add($dateTime, date_interval_create_from_date_string($timestep . ' hours'));
            return $dateTime;
        }
        else // Timestep is already a date/time of format yyyy-mm-ddThh:dd:ssZ
        {
            return $this->createDateTime($timestep);
        }
    }

    private function calculateTimestepMins($baseDateTime, $timestep)
    {
        if (is_numeric($timestep)) // Timestep is hours relative of timestamp date/time
        {
            return $timestep * 60;
        }
        else // Timestep is already a date/time of format yyyy-mm-ddThh:dd:ssZ
        {
            $interval = $this->createDateTime($timestep)->getTimeStamp() - $baseDateTime->getTimestamp();
            return $interval / 60;
        }
    }

    private function createDateTime($timestamp)
    {
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 5, 2);
        $day = substr($timestamp, 8, 2);
        $hour = substr($timestamp, 11, 2);
        $minute = substr($timestamp, 14, 2);

        $dateTime = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';

        return new DateTime($dateTime, timezone_open('UTC'));
    }

    private function combineMaps($topMapFile, $bottomMapFile, $outputMapFile)
    {
        $topMap = imagecreatefrompng($topMapFile);
        $bottomMap = imagecreatefrompng($bottomMapFile);

        imagecopy($bottomMap, $topMap, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
        imagepng($bottomMap, $outputMapFile);

        imagedestroy($topMap);
        imagedestroy($bottomMap);
    }

    private function timestampMapImage($mapImageFile, $mapImageDateTime)
    {
        $mapImageDateTime->setTimezone(new DateTimeZone('Europe/London'));
        $timestamp = $mapImageDateTime->format('Y-m-d D H:i T');

        $map = imagecreatefrompng($mapImageFile);

        $font = 2;
        $x = 5;
        $y = 5;

        $black = imagecolorallocate($map, 0, 0, 0);

        imagestring($map, $font, $x, $y, $timestamp, $black);
        imagepng($map, $mapImageFile);

        imagedestroy($map);
    }

    private function writeInfoJson($latestTimestamp, $baseDateTime, $imagesInfo)
    {
        $infoJson =
            json_encode(array(
                'name' => $this->getName(),
                'created_by' => array(
                    'api' => 'met-office-weather-maps (https://github.com/waynedgrant/met-office-weather-maps)',
                    'version' => '1.2'),
                'datapoint_timestamp' => $latestTimestamp,
                'base_time' => $baseDateTime->format('Y-m-d H:i T'),
                'images' => $imagesInfo));

        file_put_contents($this->infoJsonFile, $infoJson);
    }
}

?>
