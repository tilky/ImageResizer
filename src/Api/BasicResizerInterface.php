<?php
/**
 * @by SwiftOtter, Inc., 2/14/17
 * @website https://swiftotter.com
 **/

namespace SwiftOtter\ImageResizer\Api;

interface BasicResizerInterface
{
    /**
     * Takes an absolute path and returns a URL
     *
     * @param string $url
     * @return string
     */
    public function resizeMedia(string $path, $width = null, $height = null): string;
}