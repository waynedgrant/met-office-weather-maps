<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('GifCreator.php');
use GifCreator\GifCreator;

abstract class Map
{
    protected $apiKey;
    private $workingFolder;
    private $timestampFile;
    private $imagesDir;

    protected function __construct($apiKey, $workingFolder)
    {
        $this->apiKey = $apiKey;
        $this->workingFolder = $workingFolder;
        $this->timestampFile = $workingFolder . '/timestamp.txt';
        $this->imagesDir = dirname(__FILE__ ) . '/images/';
    }

    public function fetch()
    {
        $mapCapabilities = json_decode(file_get_contents($this->getCapabilitiesUrl()));

        $lastestTimestamp = $this->getLastestTimestamp($mapCapabilities);
        $currentTimestamp = $this->getCurrentTimestamp();
        $this->updateCurrentTimestamp($lastestTimestamp);

        if ($lastestTimestamp != $currentTimestamp)
        {
            $timesteps = $this->getAvailableTimesteps($mapCapabilities);

            $downloadedMaps = array();
            $animationDurations = array();

            $index = 0;

            foreach ($timesteps as $timestep)
            {
                $mapImageUrl = $this->getMapImageUrl($timestep, $lastestTimestamp);

                $downloadedMap = $this->workingFolder . '/' . $index . '.' . $this->getImageFormat();

                copy($mapImageUrl, $downloadedMap);

                $this->processMap($downloadedMap, $lastestTimestamp, $timestep);

                $downloadedMaps[] = $downloadedMap;
                $animationDurations[] = 200; // 2 seconds per frame
                $index++;
            }

            $this->animateMaps($downloadedMaps, $animationDurations, $this->flipAnimation());
        }
    }

    abstract protected function getCapabilitiesUrl();
    abstract protected function getLastestTimestamp($mapCapabilities);
    abstract protected function getAvailableTimesteps($mapCapabilities);
    abstract protected function getImageFormat();
    abstract protected function getWidth();
    abstract protected function getHeight();
    abstract protected function getThumbnailWidth();
    abstract protected function getThumbnailHeight();
    abstract protected function getMapImageUrl($timestep, $timestamp);
    abstract protected function getBaseMap();
    abstract protected function getOverlayMap();
    abstract protected function requiresTimestamp();
    abstract protected function flipAnimation();

    private function getCurrentTimestamp()
    {
        if (!is_dir($this->workingFolder))
        {
            mkdir($this->workingFolder, 0777, true);
        }

        if (is_file($this->timestampFile))
        {
            $currentTimestamp = file_get_contents($this->timestampFile);
        }

        return $currentTimestamp;
    }

    private function updateCurrentTimestamp($lastUpdateTimestamp)
    {
        file_put_contents($this->timestampFile, $lastUpdateTimestamp);
    }

    private function processMap($mapFile, $timestamp, $timestep)
    {
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
            $mapDateTime = $this->calculateMapDateTime($timestamp, $timestep);
            $this->timestampMap($mapFile, $mapDateTime);
        }

        $thumbnailMap = $this->createThumbnail($mapFile);

        if ($this->getImageFormat() == 'png')
        {
            $this->createGifMap($thumbnailMap);
        }
    }

    private function addBaseMap($mapFile, $baseMapFile)
    {
        $this->combineMaps($mapFile, $baseMapFile, $mapFile);
    }

    private function addOverlayMap($mapFile, $overlayMapFile)
    {
        $this->combineMaps($overlayMapFile, $mapFile, $mapFile);
    }

    private function calculateMapDateTime($timestamp, $timestep)
    {
        if (is_numeric($timestep)) // Timestep is hours relative of timestamp date/time
        {
            $dateTime = $this->createDateTime($timestamp);
            date_add($dateTime, date_interval_create_from_date_string($timestep . ' hours'));
            return $dateTime;
        }
        else // Timestep is already a date/time
        {
            return $this->createDateTime($timestep);
        }
    }

    private function createThumbnail($mapFile)
    {
        if ($this->getImageFormat() == 'png')
        {
            $map = imagecreatefrompng($mapFile);
        }
        else
        {
            $map = imagecreatefromgif($mapFile);
        }

        $thumbnail = imagecreatetruecolor($this->getThumbnailWidth(), $this->getThumbnailHeight());

        imagecopyresampled(
            $thumbnail, $map, 0, 0, 0, 0,
            $this->getThumbnailWidth(),
            $this->getThumbnailHeight(),
            $this->getWidth(),
            $this->getHeight());

        if ($this->getImageFormat() == 'png')
        {
            $thumbnailFile = str_replace('.png', '-thumbnail.png', $mapFile);
            imagepng($thumbnail, $thumbnailFile);
        }
        else
        {
            $thumbnailFile = str_replace('.gif', '-thumbnail.gif', $mapFile);
            imagegif($thumbnail, $thumbnailFile);
        }

        imagedestroy($map);
        imagedestroy($thumbnail);

        return $thumbnailFile;
    }

    private function createDateTime($timestamp)
    {
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 5, 2);
        $day = substr($timestamp, 8, 2);
        $hour = substr($timestamp, 11, 2);
        $minute = substr($timestamp, 14, 2);

        $dateTime = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':00';

        return date_create($dateTime, timezone_open("UTC"));
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
        $timestamp = date_format($mapDateTime, 'Y-m-d H:i') . ' UTC';

        $map = imagecreatefrompng($mapFile);

        $font = 2;
        $x = 5;
        $y = 5;

        $black = imagecolorallocate($map, 0, 0, 0);

        imagestring($map, $font, $x, $y, $timestamp, $black);
        imagepng($map, $mapFile);

        imagedestroy($map);
    }

    private function animateMaps($downloadedMaps, $animationDurations, $flipAnimation)
    {
        if ($this->getImageFormat() == 'png')
        {
            $downloadedMaps = $this->createGifMaps($downloadedMaps);
        }

        if ($flipAnimation)
        {
            $maps = array();

            for ($i = count($downloadedMaps)-1; $i >= 0; $i--)
            {
                $maps[] = $downloadedMaps[$i];
            }
        }
        else
        {
            $maps = $downloadedMaps;
        }

        $gifCreator = new GifCreator();
        $gifCreator->create($maps, $animationDurations, 0);

        $animatedMapFile = $this->workingFolder . '/animated.gif';
        file_put_contents($animatedMapFile, $gifCreator->getGif());
    }

    private function createGifMaps($pngMapFiles)
    {
        $gifMaps = array();

        foreach ($pngMapFiles as $pngMapFile)
        {
            $gifMapFile = $this->createGifMap($pngMapFile);
            $gifMaps[] = $gifMapFile;
        }

        return $gifMaps;
    }

    private function createGifMap($pngMapFile)
    {
        $png = imagecreatefrompng($pngMapFile);
        $gifMapFile = str_replace('.png', '.gif', $pngMapFile);

        imagegif($png, $gifMapFile);

        imagedestroy($png);

        return $gifMapFile;
    }
}

?>
