<?php

# Copyright 2015 Wayne D Grant (www.waynedgrant.com)
# Licensed under the MIT License

require_once('Map.php');

abstract class LayerMap extends Map
{
    protected function __construct($apiKey, $workingFolder)
    {
        parent::__construct($apiKey, $workingFolder);
    }

    protected function findLayer($mapCapabilities, $layerName)
    {
        $layers = $mapCapabilities->Layers->Layer;

        foreach($layers as $layer)
        {
            if ($layer->Service->LayerName == $layerName)
            {
                return $layer;
            }
        }
    }

    protected function getImageFormat()
    {
        return "png";
    }

    protected function getWidth()
    {
        return 500;
    }

    protected function getHeight()
    {
        return 500;
    }
}

?>
