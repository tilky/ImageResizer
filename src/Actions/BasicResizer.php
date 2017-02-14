<?php
/**
 * @by SwiftOtter, Inc., 2/14/17
 * @website https://swiftotter.com
 **/

namespace SwiftOtter\ImageResizer\Actions;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManager;
use SwiftOtter\ImageResizer\Api\BasicResizerInterface;

class BasicResizer implements BasicResizerInterface
{
    private $fileSystem;
    private $imageFactory;
    private $storeManager;

    public function __construct(
        Filesystem $fileSystem,
        AdapterFactory $imageFactory,
        StoreManager $storeManager
    )
    {
        $this->fileSystem = $fileSystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
    }

    public function resizeMedia(string $path, $width = null, $height = null): string
    {
        $path = $this->makePathRelative($path);
        $mediaPath = $this->getMediaPath();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if ($this->baseFileExists($path) && !$this->resizedFileExists($path, $width, $height)) {
            $this->completeResize($mediaPath, $path, $width, $height);

            return $mediaUrl . $this->getResizedPath($path, false, $width, $height);
        } else if ($this->resizedFileExists($path, $width, $height)) {
            return $mediaUrl . $this->getResizedPath($path, false, $width, $height);
        } else {
            return $mediaUrl . $path;
        }
    }

    private function completeResize($baseDirectory, $path, $width, $height)
    {
        $imageResize = $this->imageFactory->create($this->getImageAdapter());
        $imageResize->open($baseDirectory . $path);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);

        $imageResize->save($this->getResizedPath($path, true, $width, $height));
    }

    private function getImageAdapter()
    {
        if (extension_loaded('imagick')) {
            return 'IMAGEMAGICK';
        } else {
            return 'GD2';
        }
    }

    private function makePathRelative($path)
    {
        if (strstr($path, $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath()) !== false) {
            return str_replace($this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA), '', $path);
        } else if (strstr($path, $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)) !== false) {
            return str_replace($this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA), '', $path);
        } else {
            return $path;
        }
    }

    private function getMediaPath(): string
    {
        return $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
    }

    private function baseFileExists(string $path): bool
    {
        $result = file_exists($this->getMediaPath() . $path);
        return $result;
    }

    private function resizedFileExists(string $path, $width = null, $height = null): bool
    {
        $result = file_exists($this->getResizedPath($path, true, $width, $height));
        return $result;
    }

    private function getResizedPath($path, $absolute = true, $width = null, $height = null): string
    {
        $file = basename($path);
        $path = str_replace($file, '', $path);

        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        if (!$width && !$height) {
            return ($absolute ? $this->getMediaPath() : '') . $path . $file;
        }

        return ($absolute ? $this->getMediaPath() : '') . $path . 'resized/' . $this->getResizedWidthHeightCombination($width, $height) . $file;
    }

    private function getResizedWidthHeightCombination($width = null, $height = null): string
    {
        if ($width && $height) {
            return $width . 'x' . $height . '/';
        } else if (!$width && $height) {
            return 'x'. $height . '/';
        } else if ($width && !$height) {
            return $width . 'x' . '/';
        }

        return '/';
    }
}