<?php
session_start();

// CAPTCHA generation
function generateCaptcha() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_length = 6;
    $captcha_string = '';
    for ($i = 0; $i < $captcha_length; $i++) {
        $captcha_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    $_SESSION['captcha'] = $captcha_string; // Store in session
    return $captcha_string;
}

// Generate CAPTCHA image
function generateCaptchaImage($captcha_string) {
    $width = 100;
    $height = 40;
    $image = imagecreatetruecolor($width, $height);
    $bg_color = imagecolorallocate($image, 255, 255, 255); // White background
    $text_color = imagecolorallocate($image, 0, 0, 0); // Black text
    imagefill($image, 0, 0, $bg_color);
    
    // Path to a font file (make sure to replace with the correct path to a TTF font on your server)
    $font = 'C:/Windows/Fonts/arial.ttf';
    $font_size = 15; // Set the font size to 15 for a smaller image
    $angle = 0; // No rotation for simplicity
    $x = 10; // X position of the text
    $y = 30; // Y position of the text (adjusted to fit the smaller image)
    
    // Add the CAPTCHA string to the image
    imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font, $captcha_string);
    
    // Set the content type header for PNG
    header('Content-Type: image/png');
    
    // Output the image
    imagepng($image);
    imagedestroy($image);
}

// Generate and display CAPTCHA
$captcha_string = generateCaptcha();
generateCaptchaImage($captcha_string);
?>
