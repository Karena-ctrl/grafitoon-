<?php
require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;

$homepageUrl = 'https://www.yourdomain.com/Grafitoon_index.php'; // Change to your actual homepage URL

$result = Builder::create()
    ->writer(new PngWriter())
    ->data($homepageUrl)
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
    ->size(250)
    ->margin(10)
    ->labelText('Visit Our Homepage')
    ->labelFont(new NotoSans(12))
    ->labelAlignment(new LabelAlignmentCenter())
    ->build();

header('Content-Type: ' . $result->getMimeType());
echo $result->getString();
