<?php

include '../vendor/autoload.php';

$htmlInput = $_POST['html-input'];

$sanitizer = new Phlib\XssSanitizer\Sanitizer();

$sanitized = $sanitizer->sanitize($htmlInput);

header('X-XSS-Protection:0');

echo $sanitized;
