<?php

namespace App\Service\ImageOptimizer;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;

class ImageOptimizer implements ImageOptimizerInterface
{
    private const MAX_WIDTH = 200;

    private const MAX_HEIGHT = 150;

    public function __construct(
        private readonly ImagineInterface $imagine,
    )
    {
    }

    public function resize(string $filename): void
    {
        list($iwidth, $iheight) = getimagesize($filename);

        $ratio = $iwidth / $iheight;

        $width = self::MAX_WIDTH;
        $height = self::MAX_HEIGHT;

        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        $photo = $this->imagine->open($filename);
        $photo->resize(new Box($width, $height));
        $photo->save($filename);
    }
}
