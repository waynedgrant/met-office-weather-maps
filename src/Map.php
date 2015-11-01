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

        $lastestTimestamp = $this->readLastestTimestamp($mapCapabilities);
        $currentTimestamp = $this->getCurrentTimestamp();

        if ($lastestTimestamp != $currentTimestamp)
        {
            $baseDateTime = $this->createDateTime($lastestTimestamp);
            $mapsInfo = array();
            $timesteps = $this->readAvailableTimesteps($mapCapabilities);

            for ($i = 0; $i < count($timesteps); $i++)
            {
                $timestep = $timesteps[$i];

                $mapImageUrl = $this->generateMapImageUrl($timestep, $lastestTimestamp);

                $mapFile = $this->workingFolder . '/' . $i . '.' . $this->getImageFormat();

                copy($mapImageUrl, $mapFile);

                $mapsInfo[] = $this->processMap($mapFile, $baseDateTime, $timestep);
            }

            $this->writeInfoJson($lastestTimestamp, $baseDateTime, $mapsInfo);
        }
    }

    abstract protected function getName();
    abstract protected function getCapabilitiesUrl();
    abstract protected function readLastestTimestamp($mapCapabilities);
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

    private function processMap($mapFile, $baseDateTime, $timestep)
    {
        $mapDateTime = $this->calculateMapDateTime($baseDateTime, $timestep);
        $mapRelativeMinutes = $this->calculateMapRelativeMinutes($baseDateTime, $timestep);

        $mapInfo = array(
            'image' => basename($mapFile),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'date_time' => $mapDateTime->format('Y-m-d H:i T'),
            'relative_minutes' => $mapRelativeMinutes);

        $baseMap = $this->getBaseMap();

        if (!is_null($baseMap))
        {
            $baseMapFile = $this->imagesDir . $baseMap;
            $this->addBaseMap($mapFile, $baseMapFile);
        }

        $overlayMap = $this->getOverlayMap();

        if (!is_null($overlayMap))
        {
            $overlayMapFile = $this->imagesDir . $overlayMap;
            $this->addOverlayMap($mapFile, $overlayMapFile);
        }

        if ($this->requiresTimestamp())
        {
            $this->timestampMap($mapFile, $mapDateTime);
        }

        return $mapInfo;
    }

    private function addBaseMap($mapFile, $baseMapFile)
    {
        $this->combineMaps($mapFile, $baseMapFile, $mapFile);
    }

    private function addOverlayMap($mapFile, $overlayMapFile)
    {
        $this->combineMaps($overlayMapFile, $mapFile, $mapFile);
    }

    private function calculateMapDateTime($baseDateTime, $timestep)
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

    private function calculateMapRelativeMinutes($mapDateTime, $timestep)
    {
        if (is_numeric($timestep)) // Timestep is hours relative of timestamp date/time
        {
            return $timestep * 60;
        }
        else // Timestep is already a date/time of format yyyy-mm-ddThh:dd:ssZ
        {
            $interval = $this->createDateTime($timestep)->getTimeStamp() - $mapDateTime->getTimestamp();
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

    private function timestampMap($mapFile, $mapDateTime)
    {
        $mapDateTime->setTimezone(new DateTimeZone('Europe/London'));
        $timestamp = $mapDateTime->format('Y-m-d D H:i T');

        $map = imagecreatefrompng($mapFile);

        $font = 2;
        $x = 5;
        $y = 5;

        $black = imagecolorallocate($map, 0, 0, 0);

        imagestring($map, $font, $x, $y, $timestamp, $black);
        imagepng($map, $mapFile);

        imagedestroy($map);
    }

    private function writeInfoJson($lastestTimestamp, $baseDateTime, $mapsInfo)
    {
        $infoJson =
            json_encode(array(
                'created_by' => array(
                    'api' => 'met-office-weather-maps (https://github.com/waynedgrant/met-office-weather-maps)',
                    'version' => '1.1'),
                'name' => $this->getName(),
                'datapoint_timestamp' => $lastestTimestamp,
                'base_date_time' => $baseDateTime->format('Y-m-d H:i T'),
                'maps' => $mapsInfo));

        file_put_contents($this->infoJsonFile, $infoJson);
    }
}

?>
