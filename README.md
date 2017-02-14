# SwiftOtter Basic Image Resizer (Magento 2)

##Installation:

* `composer require swiftotter/image-resizer`
* `php bin/magento module:enable SwiftOtter_ImageResizer`
* `php bin/magento setup:upgrade`
* `php bin/magento setup:di:compile`

**Minimum PHP version: 7.0+**

##Description:

Prefers ImageMagick, but falls back to GD2 if not present.

Provides a very easy way to resize images (in the `media/` folder) anywhere in your code. All you have to do is install it (per the above directions) and then:
```php

/// Add it to your __construct() method:
private $imageResizer;

public function __construct(
    \SwiftOtter\ImageResizer\Api\BasicResizerInterface $imageResizer
) {
    $this->imageResizer = $imageResizer;
}

public function getImageUrl() {
    $this->imageResizer->resizeMedia($this->getData('image_url'), $width, $height);
}
```

Methods:

* `resizeMedia`: resizes an image in the `media/` folder.
    * `path` (string): path to the file. This could be a url (which must include the store's base url) or the absolute path to the media folder or a relative path
    inside the media folder.
    * `width` (optional): the width of the image
    * `height` (optional): the height of the image
    
If neither the width and the height are set, the original path will be returned.