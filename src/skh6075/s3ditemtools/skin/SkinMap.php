<?php

namespace skh6075\s3ditemtools\skin;

final class SkinMap{

    /** @var int[] */
    public const SKIN_WIDTH_SIZE = [
        64 * 32 * 4   => 64,
        64 * 64 * 4   => 64,
        128 * 128 * 4 => 128
    ];

    /** @var int[] */
    public const SKIN_HEIGHT_SIZE = [
        64 * 32 * 4   => 32,
        64 * 64 * 4   => 64,
        128 * 128 * 4 => 128
    ];
}