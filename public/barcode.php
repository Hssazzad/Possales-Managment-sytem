<?php
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (!$code) {
    http_response_code(400);
    exit('No code provided');
}

header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=3600');

// Code 128B Patterns (Index 0 to 106)
$c128 = [
    "11011001100", "11001101100", "11001100110", "10010011000", "10010001100",
    "10001001100", "10011001000", "10011000100", "10001100100", "11001001000",
    "11001000100", "11000100100", "10110011100", "10011011100", "10011001110",
    "10111001100", "10011101100", "10011100110", "11001110010", "11001011100",
    "11001001110", "11011100100", "11001110100", "11101101110", "11101001100",
    "11100101100", "11100100110", "11101100100", "11100110100", "11100110010",
    "11011011000", "11011000110", "11000110110", "10100011000", "10001011000",
    "10001000110", "10110001000", "10001101000", "10001100010", "11010001000",
    "11000101000", "11000100010", "10110111000", "10110001110", "10001101110",
    "10111011000", "10111000110", "10001110110", "11101110110", "11010001110",
    "11000101110", "11011101000", "11011100010", "11011101110", "11101011000",
    "11101000110", "11100010110", "11101101000", "11101100010", "11100011010",
    "11101111010", "11001000010", "11110001010", "10100110000", "10100001100",
    "10010110000", "10010000110", "10000101100", "10000100110", "10110010000",
    "10110000100", "10011010000", "10011000010", "10000110100", "10000110010",
    "11000010010", "11001010000", "11110111010", "11000010100", "10001111010",
    "10100111100", "10010111100", "10010011110", "10111100100", "10011110100",
    "10011110010", "11110100100", "11110010100", "11110010010", "11011011110",
    "11011110110", "11110110110", "10101111000", "10100011110", "10001011110",
    "10111101000", "10111100010", "11110101000", "11110100010", "10111011110",
    "10111101110", "11101011110", "11110101110", "11010000100", "11010010000",
    "11010011100", "1100011101011" // Index 106 is STOP character
];

// Start B value is 104
$start_val = 104;
$seq = $c128[$start_val]; 
$checksum = $start_val;

// Add Characters
$chars = str_split($code);
foreach ($chars as $index => $ch) {
    $val = ord($ch) - 32;
    if ($val < 0 || $val > 95) {
        $val = 0; 
    }
    $seq .= $c128[$val];
    $checksum += $val * ($index + 1);
}

// Calculate modulo 103 checksum and add it
$check_val = $checksum % 103;
$seq .= $c128[$check_val];

// Add Stop character (Value 106)
$seq .= $c128[106];

// Sizing configuration
$barW   = 2;
$barH   = 55;
$quiet  = 30; // Quiet zone ?????? ?????
$textH  = 16;
$totalW = strlen($seq) * $barW + ($quiet * 2);
$totalH = $barH + $textH + 10; // Extra padding

// Generate SVG (Added shape-rendering="crispEdges" to prevent blur/anti-aliasing)
$svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$totalW.'" height="'.$totalH.'" viewBox="0 0 '.$totalW.' '.$totalH.'" style="background:#fff;" shape-rendering="crispEdges">';
$svg .= '<rect width="100%" height="100%" fill="#ffffff" stroke="none"/>';

// ????? ???????? ???? <path> ??????? ??? ???????????? ??? ???????????? ???? ???? ????
$pathData = "";
$x = $quiet;
for ($i = 0; $i < strlen($seq); $i++) {
    if ($seq[$i] === '1') {
        $pathData .= "M {$x} 5 V {$barH} h {$barW} V 5 Z "; // Top margin 5px
    }
    $x += $barW;
}
$svg .= '<path d="'.$pathData.'" fill="#000000"/>';

// Add text below barcode (Removed crispEdges for text so it looks smooth)
$svg .= '<text x="'.($totalW/2).'" y="'.($barH + $textH + 3).'" text-anchor="middle" font-family="monospace" font-size="14" fill="#000000" shape-rendering="auto">'.htmlspecialchars($code).'</text>';
$svg .= '</svg>';

echo $svg;