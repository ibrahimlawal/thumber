<?php

if(file_exists(__dir__ . DIRECTORY_SEPARATOR . '_thumber.config.php')){
    require('_thumber.config.php');
}

if(!isset($error_reporting_level)){
    $error_reporting_level = E_ALL; // specify a PHP error reporting level
}
if(!isset($home)){
    $home = __dir__;
}
if(!isset($outhome)){
    $outhome = __dir__;
}

error_reporting($error_reporting_level);

function give_blank_image($w = 20, $h = 20)
{
    header('HTTP/1.0 404 Not Found');
    header('Content-Type: image/png');
    $im = @imagecreatetruecolor($w? : 20, $h? : 20)
        or die('');
    $transparentinavatar = imagecolorallocate($im, 255, 254, 253);
    imagefill($im, 0, 0, $transparentinavatar);
    imagecolortransparent($im, $transparentinavatar);
    imagepng($im);
    imagedestroy($im);
    die();
}

function imageCreateFromAny($filepath)
{
    $ret = new stdClass();
    $ret->type = exif_imagetype($filepath); // [] if you dont have exif you could use getImageSize()
    $allowedTypes = array(
        1, // [] gif
        2, // [] jpg
        3, // [] png
    );
    if (!in_array($ret->type, $allowedTypes)) {
        return false;
    }
    switch ($ret->type) {
        case 1 :
            $ret->image = imageCreateFromGif($filepath);
            break;
        case 2 :
            $ret->image = imageCreateFromJpeg($filepath);
            break;
        case 3 :
            $ret->image = imageCreateFromPng($filepath);
            break;
    }
    return $ret;
}

function Get_Image_Size($img)
{
    $get = getimagesize($img);
    $ret = new stdClass();
    $ret->width = $get[0];
    $ret->height = $get[1];
    $ret->type = $get[2];
    $ret->attr = $get[3];
    $ret->bits = $get['bits'];
    $ret->mime = $get['mime'];
    return $ret;
}
$path = filter_input(INPUT_GET, 'src');

// check that we have the right format
$path_arr = explode('/', $path);

// must be exactly 3 values first 2, int; the last, the filename of an
// image present in the configured home folder
if (!(count($path_arr) === 3) && !(count($path_arr) === 4)) {
    // invalid path
    give_blank_image();
}

if (!(ctype_digit($path_arr[0]))) {
    // invalid width
    give_blank_image();
}

if (!(ctype_digit($path_arr[1]))) {
    // invalid height
    give_blank_image();
}

$padded=false;
if($path_arr[2] === 'pad'){
    $padded = true;
    $path_arr[2] = $path_arr[3];
}

if (!isset($path_arr[2]) || (!is_file($home . DIRECTORY_SEPARATOR . $path_arr[2]))) {
    // invalid image
    // give_blank_image(intval($path_arr[0]), intval($path_arr[1]));
    $path_arr[2] = 'pink1.jpg';
}

$width = intval($path_arr[0]);
$height = intval($path_arr[1]);
$origfilename = $path_arr[2];
$filename = $home . DIRECTORY_SEPARATOR . $origfilename;

$orig = Get_Image_Size($filename);
if ($orig->width and $orig->height) {
    if ($width === 0 && $height === 0) {
        $outwidth = $orig->width;
        $outheight = $orig->height;
        $ratio = 1;
    } else if ($width === 0 && $height !== 0) {
        $outwidth = ($orig->width * $height) / $orig->height;
        $outheight = $height;
        $ratio = floatval($outheight) / $orig->height;
    } else if ($width !== 0 && $height === 0) {
        $outwidth = $width;
        $outheight = ($orig->height * $width) / $orig->width;
        $ratio = floatval($outwidth) / $orig->width;
    } else {
        $outwidth = $width;
        $outheight = $height;
        $wratio = floatval($outwidth) / $orig->width;
        $hratio = floatval($outheight) / $orig->height;
        if($padded){
            $ratio = ($wratio > $hratio) ? $hratio : $wratio;
        } else {
            $ratio = ($wratio > $hratio) ? $wratio : $hratio;
        }
    }
} else {
    give_blank_image();
}

$baseimg = imageCreateFromAny($filename);
if (!$baseimg) {
    give_blank_image();
}

$outfilename = $outhome . DIRECTORY_SEPARATOR . $width . DIRECTORY_SEPARATOR . $height . DIRECTORY_SEPARATOR . ( $padded ? 'pad' . DIRECTORY_SEPARATOR : '' ) . $origfilename;
$base = $baseimg->image;
$out = imagecreatetruecolor($outwidth, $outheight);
$transparentinavatar = imagecolorallocate($out, 255, 254, 253);
imagefill($out, 0, 0, $transparentinavatar);
imagecopyresampled($out, $base, floor(($outwidth - floor($ratio * $orig->width)) / 2),
                                                         floor(($outheight - floor($ratio * $orig->height)) / 2),
                                                                                   0, 0,
                                                                                   floor($ratio * $orig->width),
                                                                                         floor($ratio * $orig->height),
                                                                                               $orig->width ,
                                                                                               $orig->height);

// create directories if non-existent
if (!file_exists(dirname($outfilename))) {
    mkdir(dirname($outfilename), 0755, true);
}
switch ($baseimg->type) {
    case 1 :
        // Make the background transparent
        imagecolortransparent($out, $transparentinavatar);
        imagegif($out, $outfilename);
        break;
    case 2 :
        imagejpeg($out, $outfilename);
        break;
    case 3 :
        // Make the background transparent
        imagecolortransparent($out, $transparentinavatar);
        imagepng($out, $outfilename);
        break;
}

// show image
header('Content-Type: image/png');
imagepng($out);

// free resources used by this image
imagedestroy($out);

// due to .htaccess accompanying this file, the same image will not be resized twice, hopefully
