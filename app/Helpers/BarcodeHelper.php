<?php

namespace App\Helpers;

class BarcodeHelper
{
    
    private static array $encoding = [
        '0' => ['0001101', '0100111', '1110010'],
        '1' => ['0011001', '0110011', '1100110'],
        '2' => ['0010011', '0011011', '1101100'],
        '3' => ['0111101', '0100001', '1000010'],
        '4' => ['0100011', '0011101', '1011100'],
        '5' => ['0110001', '0111001', '1001110'],
        '6' => ['0101111', '0000101', '1010000'],
        '7' => ['0111011', '0010001', '1000100'],
        '8' => ['0110111', '0001001', '1001000'],
        '9' => ['0001011', '0010111', '1110100'],
    ];

    
    private static array $parity = [
        '0' => [0,0,0,0,0,0],
        '1' => [0,0,1,0,1,1],
        '2' => [0,0,1,1,0,1],
        '3' => [0,0,1,1,1,0],
        '4' => [0,1,0,0,1,1],
        '5' => [0,1,1,0,0,1],
        '6' => [0,1,1,1,0,0],
        '7' => [0,1,0,1,0,1],
        '8' => [0,1,0,1,1,0],
        '9' => [0,1,1,0,1,0],
    ];

  
    public static function ean13Svg(
        string $code,
        int    $barWidth  = 2,
        int    $barHeight = 50,
        string $color     = '#003d5c'
    ): string {
        
        if (!preg_match('/^\d{13}$/', $code)) {
            return self::errorSvg('Invalid EAN13');
        }

       
        if (!self::verifyChecksum($code)) {
            return self::errorSvg('Bad checksum');
        }

        $digits  = str_split($code);
        $first   = $digits[0];
        $left    = array_slice($digits, 1, 6);
        $right   = array_slice($digits, 7, 6);
        $pattern = self::$parity[$first];

       
        $bars = '101';

       
        foreach ($left as $i => $d) {
            $type = $pattern[$i]; // 0=L, 1=G
            $bars .= self::$encoding[$d][$type];
        }

        
        $bars .= '01010';

      
        foreach ($right as $d) {
            $bars .= self::$encoding[$d][2];
        }

      
        $bars .= '101';

        // Total bars = 95 modules
        $totalWidth  = strlen($bars) * $barWidth + 2; 
        $totalHeight = $barHeight + 16; 
        $textY       = $barHeight + 11;
        $quietZone   = $barWidth; 

       
        $rects = '';
        $x     = $quietZone;
        foreach (str_split($bars) as $i => $bit) {
            if ($bit === '1') {
                $rects .= sprintf(
                    '<rect x="%d" y="0" width="%d" height="%d" fill="%s"/>',
                    $x, $barWidth, $barHeight,
                    htmlspecialchars($color)
                );
            }
            $x += $barWidth;
        }

       
        $fontSize    = max(8, $barWidth * 4);
        $leftStart   = $quietZone + (3 * $barWidth); 
        $rightStart  = $quietZone + (3 + 42 + 5) * 

       
        $textSvg  = sprintf(
            '<text x="%d" y="%d" font-family="monospace" font-size="%d" fill="%s" text-anchor="middle">%s</text>',
            max(0, $quietZone - $barWidth),
            $textY, $fontSize,
            htmlspecialchars($color),
            $first
        );

      
        for ($i = 0; $i < 6; $i++) {
            $cx = $leftStart + ($i * 7 + 3) * $barWidth;
            $textSvg .= sprintf(
                '<text x="%d" y="%d" font-family="monospace" font-size="%d" fill="%s" text-anchor="middle">%s</text>',
                $cx, $textY, $fontSize,
                htmlspecialchars($color),
                $left[$i]
            );
        }

       
        for ($i = 0; $i < 6; $i++) {
            $cx = $rightStart + ($i * 7 + 3) * $barWidth;
            $textSvg .= sprintf(
                '<text x="%d" y="%d" font-family="monospace" font-size="%d" fill="%s" text-anchor="middle">%s</text>',
                $cx, $textY, $fontSize,
                htmlspecialchars($color),
                $right[$i]
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" style="display:block;max-width:100%%">%s%s</svg>',
            $totalWidth, $totalHeight,
            $totalWidth, $totalHeight,
            $rects, $textSvg
        );
    }


    public static function verifyChecksum(string $code): bool
    {
        $digits = str_split($code);
        $sum    = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$digits[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $check = (10 - ($sum % 10)) % 10;
        return $check === (int)$digits[12];
    }

   
    private static function errorSvg(string $msg): string
    {
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="30">'
            . '<text x="5" y="20" font-size="9" fill="#c00">%s</text></svg>',
            htmlspecialchars($msg)
        );
    }
}