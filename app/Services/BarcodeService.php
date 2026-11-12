<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

class BarcodeService
{
    /**
     * Génère un code-barres Code128 en SVG.
     */
    public function barcodeSvg(string $text, float $width = 240, float $height = 64): string
    {
        $barcode = (new TypeCode128)->getBarcode($text);

        $renderer = (new SvgRenderer)
            ->setSvgType(SvgRenderer::TYPE_SVG_INLINE)
            ->setBackgroundColor([255, 255, 255]);

        return $renderer->render($barcode, $width, $height);
    }

    /**
     * Génère un QR code en SVG.
     */
    public function qrSvg(string $text, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($text);
    }
}
