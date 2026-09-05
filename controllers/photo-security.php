<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| PHOTO SECURITY / BLUR HELPER
|--------------------------------------------------------------------------
|
| Purpose:
| - Home Verified viewer  -> original photo
| - Non-verified viewer   -> blurred photo
|
| IMPORTANT:
| This file only controls which photo URL the API returns.
| Direct access to original files under /uploads/profile-photos/
| should also be protected separately for complete privacy.
|
*/

/*
|--------------------------------------------------------------------------
| GET BLURRED PHOTO RELATIVE PATH
|--------------------------------------------------------------------------
*/

function photo_blurred_relative_path(string $relativePath): string
{
    $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');

    $filename = basename($relativePath);

    return 'uploads/profile-photos/blurred/' . $filename . '.jpg';
}


/*
|--------------------------------------------------------------------------
| CREATE / RETURN BLURRED PHOTO
|--------------------------------------------------------------------------
*/

function ensure_blurred_photo(string $relativePath): ?string
{
    $relativePath = ltrim(
        str_replace('\\', '/', trim($relativePath)),
        '/'
    );

    if ($relativePath === '') {
        return null;
    }

    $sourcePath = dirname(__DIR__) . '/' . $relativePath;

    if (!is_file($sourcePath)) {
        return null;
    }

    $blurredRelativePath =
        photo_blurred_relative_path($relativePath);

    $blurredPath =
        dirname(__DIR__) . '/' . $blurredRelativePath;

    /*
    |--------------------------------------------------------------------------
    | RETURN EXISTING BLURRED COPY
    |--------------------------------------------------------------------------
    */

    if (is_file($blurredPath) && filesize($blurredPath) > 0) {
        return $blurredRelativePath;
    }

    /*
    |--------------------------------------------------------------------------
    | GD CHECK
    |--------------------------------------------------------------------------
    */

    if (!function_exists('imagecreatefromjpeg')) {
        return null;
    }

    $imageInfo = @getimagesize($sourcePath);

    if ($imageInfo === false) {
        return null;
    }

    $mime = strtolower((string)($imageInfo['mime'] ?? ''));

    /*
    |--------------------------------------------------------------------------
    | LOAD SOURCE IMAGE
    |--------------------------------------------------------------------------
    */

    $source = null;

    switch ($mime) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($sourcePath);
            break;

        case 'image/png':
            $source = @imagecreatefrompng($sourcePath);
            break;

        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $source = @imagecreatefromwebp($sourcePath);
            }
            break;

        default:
            return null;
    }

    if (!$source) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMIT IMAGE SIZE
    |--------------------------------------------------------------------------
    |
    | Keep the blurred copy at a maximum of 900px.
    | This also reduces storage and processing cost.
    |
    */

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);

    if ($sourceWidth <= 0 || $sourceHeight <= 0) {
        imagedestroy($source);
        return null;
    }

    $maxDimension = 900;

    $scale = min(
        1,
        $maxDimension / max($sourceWidth, $sourceHeight)
    );

    $targetWidth = max(
        1,
        (int)round($sourceWidth * $scale)
    );

    $targetHeight = max(
        1,
        (int)round($sourceHeight * $scale)
    );

    $image = imagecreatetruecolor(
        $targetWidth,
        $targetHeight
    );

    if (!$image) {
        imagedestroy($source);
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | WHITE BACKGROUND
    |--------------------------------------------------------------------------
    |
    | Output is JPEG, so PNG/WebP transparency needs a solid background.
    |
    */

    $white = imagecolorallocate(
        $image,
        255,
        255,
        255
    );

    imagefill(
        $image,
        0,
        0,
        $white
    );

    imagecopyresampled(
        $image,
        $source,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $sourceWidth,
        $sourceHeight
    );

    imagedestroy($source);

    /*
    |--------------------------------------------------------------------------
    | BLUR
    |--------------------------------------------------------------------------
    |
    | 12 Gaussian blur passes.
    |
    | This is intentionally stronger than the previous 6-pass version
    | because the previous output still made the face too recognizable.
    |
    */

    for ($i = 0; $i < 24; $i++) {
        @imagefilter(
            $image,
            IMG_FILTER_GAUSSIAN_BLUR
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXTRA LIGHT SOFTENING
    |--------------------------------------------------------------------------
    |
    | One additional smooth pass helps reduce remaining facial detail
    | without creating an obvious pixelated effect.
    |
    */

    @imagefilter(
        $image,
        IMG_FILTER_SMOOTH,
        6
    );

    /*
    |--------------------------------------------------------------------------
    | CREATE DIRECTORY
    |--------------------------------------------------------------------------
    */

    $blurredDirectory =
        dirname($blurredPath);

    if (
        !is_dir($blurredDirectory) &&
        !@mkdir(
            $blurredDirectory,
            0755,
            true
        )
    ) {
        imagedestroy($image);
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE JPEG
    |--------------------------------------------------------------------------
    */

    $saved = @imagejpeg(
        $image,
        $blurredPath,
        75
    );

    imagedestroy($image);

    if (
        !$saved ||
        !is_file($blurredPath) ||
        filesize($blurredPath) <= 0
    ) {
        return null;
    }

    return $blurredRelativePath;
}


/*
|--------------------------------------------------------------------------
| PHOTO URL FOR VIEWER
|--------------------------------------------------------------------------
|
| Home Verified:
|     original photo
|
| Not Home Verified:
|     blurred photo only
|
| If blur generation fails:
|     DO NOT fall back to the original photo.
|
*/

function photo_url_for_viewer(
    string $relativePath,
    bool $viewerHomeVerified
): ?string {
    $relativePath = ltrim(
        str_replace('\\', '/', trim($relativePath)),
        '/'
    );

    if ($relativePath === '') {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | HOME VERIFIED -> ORIGINAL
    |--------------------------------------------------------------------------
    */

    if ($viewerHomeVerified) {
        return '/matchwithijas-api/' . ltrim($relativePath, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | NOT VERIFIED -> BLURRED ONLY
    |--------------------------------------------------------------------------
    */

    $blurredPath =
        ensure_blurred_photo($relativePath);

    if (!$blurredPath) {
        /*
        | Never expose the original photo if blur creation fails.
        */
        return null;
    }

    return '/matchwithijas-api/' . ltrim($blurredPath, '/');
}
