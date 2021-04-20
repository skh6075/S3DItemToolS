<?php

namespace skh6075\s3ditemtools\utils;

use skh6075\s3ditemtools\skin\SkinMap;

final class ImageUtils{

    public static function skinDataToImageResource(string $skinData) {
        $size   = strlen($skinData);
        $width  = SkinMap::SKIN_WIDTH_SIZE[$size];
        $height = SkinMap::SKIN_HEIGHT_SIZE[$size];
        $pos    = 0;
        $image  = imagecreatetruecolor($width, $height);

        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        for ($y = 0; $y < $height; $y ++) {
            for ($x = 0; $x < $width; $x ++) {
                $r = ord($skinData[$pos]);
                $pos ++;
                $g = ord($skinData[$pos]);
                $pos ++;
                $b = ord($skinData[$pos]);
                $pos ++;
                $a = 127 - intdiv(ord($skinData[$pos]), 2);
                $pos ++;
                $color = imagecolorallocatealpha($image, $r, $g, $b, $a);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagesavealpha($image, true);
        return $image;
    }

    public static function imageSizeFix(string $imagePath, int $width, int $height, bool $checkRadius = false) {
        [$imageWidth, $imageHeight] = getimagesize($imagePath);
        $radius = $imageWidth / $imageHeight;
        if ($checkRadius) {
            if ($imageWidth > $imageHeight) {
                $imageWidth = ceil($imageWidth - ($imageWidth * abs($radius - $width / $height)));
            } else {
                $imageHeight = ceil($imageHeight - ($imageHeight * abs($radius - $width / $height)));
            }

            $newWidth = $imageWidth;
            $newHeight = $imageHeight;
        } else {
            if ($width / $height > $radius) {
                $newWidth = $height * $radius;
                $newHeight = $height;
            } else {
                $newHeight = $width / $radius;
                $newWidth = $width;
            }
        }

        $src = imagecreatefrompng($imagePath);
        $dst = imagecreatetruecolor($width, $height);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);

        return $dst;
    }
}