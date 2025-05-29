<?php
$imageUrl = $_GET['imageUrl'] ?? '';
$watermarkUrl = $_GET['watermarkUrl'] ?? 'https://connecteo.in/x10-property-listing/assets/images/watermark.png';
$applyWatermark = $_GET['watermark'] == 1;
$alpha = isset($_GET['alpha']) ? floatval($_GET['alpha']) : 0.5; // Default to 50% transparency

// Validate image URL
if (!$imageUrl) {
    http_response_code(400);
    echo 'Missing image URL.';
    exit;
}

// Get image metadata
$imageInfo = getimagesize($imageUrl);
if (!$imageInfo) {
    http_response_code(400);
    echo 'Invalid image URL.';
    exit;
}
$mime = $imageInfo['mime'];
$imageData = file_get_contents($imageUrl);
if (!$imageData) {
    http_response_code(404);
    echo 'Unable to fetch image.';
    exit;
}

// Create image from string
$srcImage = imagecreatefromstring($imageData);
if (!$srcImage) {
    http_response_code(500);
    echo 'Invalid image data.';
    exit;
}

// Apply watermark only if requested
if ($applyWatermark && $watermarkUrl) {
    $watermarkData = file_get_contents($watermarkUrl);
    if ($watermarkData) {
        $watermark = imagecreatefromstring($watermarkData);
        if ($watermark) {
            $srcWidth = imagesx($srcImage);
            $srcHeight = imagesy($srcImage);

            $wmOriginalWidth = imagesx($watermark);
            $wmOriginalHeight = imagesy($watermark);
            $wmAspect = $wmOriginalWidth / $wmOriginalHeight;

            // Target watermark size (50% of original image)
            $targetWidth = $srcWidth * 0.5;
            $targetHeight = $srcHeight * 0.5;

            if ($wmAspect > ($targetWidth / $targetHeight)) {
                $newWmWidth = $targetWidth;
                $newWmHeight = $newWmWidth / $wmAspect;
            } else {
                $newWmHeight = $targetHeight;
                $newWmWidth = $newWmHeight * $wmAspect;
            }

            // Resize watermark
            $resizedWatermark = imagecreatetruecolor($newWmWidth, $newWmHeight);
            imagealphablending($resizedWatermark, false);
            imagesavealpha($resizedWatermark, true);
            imagecopyresampled(
                $resizedWatermark,
                $watermark,
                0,
                0,
                0,
                0,
                $newWmWidth,
                $newWmHeight,
                $wmOriginalWidth,
                $wmOriginalHeight
            );

            // Center watermark
            $dstX = ($srcWidth - $newWmWidth) / 2;
            $dstY = ($srcHeight - $newWmHeight) / 2;

            // imagecopy($srcImage, $resizedWatermark, $dstX, $dstY, 0, 0, $newWmWidth, $newWmHeight);
            mergeWithAlpha($srcImage, $resizedWatermark, $dstX, $dstY, $newWmWidth, $newWmHeight, $alpha); // 0.5 = 50% transparency

            imagedestroy($watermark);
            imagedestroy($resizedWatermark);
        }
    }
}

function mergeWithAlpha($dst, $src, $dstX, $dstY, $srcW, $srcH, $pct)
{
    // Create a temporary image to hold the watermark
    $tmp = imagecreatetruecolor($srcW, $srcH);
    imagealphablending($tmp, false);
    imagesavealpha($tmp, true);

    // Fill with transparent color
    $transparent = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
    imagefill($tmp, 0, 0, $transparent);

    // Copy watermark to temporary image
    imagecopy($tmp, $src, 0, 0, 0, 0, $srcW, $srcH);

    // Apply alpha blending
    for ($x = 0; $x < $srcW; $x++) {
        for ($y = 0; $y < $srcH; $y++) {
            $rgba = imagecolorat($tmp, $x, $y);
            $alpha = ($rgba & 0x7F000000) >> 24;
            $alpha = min(127, $alpha + (127 - $alpha) * (1 - $pct)); // blend with target alpha
            $color = imagecolorsforindex($tmp, $rgba);
            $newColor = imagecolorallocatealpha($tmp, $color['red'], $color['green'], $color['blue'], $alpha);
            imagesetpixel($tmp, $x, $y, $newColor);
        }
    }

    // Merge watermark with alpha onto destination
    imagecopy($dst, $tmp, $dstX, $dstY, 0, 0, $srcW, $srcH);
    imagedestroy($tmp);
}

// Output image
switch ($mime) {
    case 'image/jpeg':
        header('Content-Type: image/jpeg');
        imagejpeg($srcImage, null, 100);
        break;
    case 'image/png':
        header('Content-Type: image/png');
        imagepng($srcImage, null, 0);
        break;
    case 'image/webp':
        header('Content-Type: image/webp');
        imagewebp($srcImage, null, 100);
        break;
    default:
        http_response_code(415);
        echo 'Unsupported image type.';
        imagedestroy($srcImage);
        exit;
}

imagedestroy($srcImage);
