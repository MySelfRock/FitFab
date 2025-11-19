<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SheetLayoutService
{
    protected int $scale = 10; // Scale factor for SVG (1mm = 10 units)
    protected int $margin = 50; // Margin around the sheet
    protected string $strokeColor = '#333333';
    protected string $fillColor = '#e3f2fd';
    protected string $textColor = '#000000';

    /**
     * Generate SVG for a sheet layout.
     *
     * @param array $sheetData
     * @return string
     */
    public function generateSvg(array $sheetData): string
    {
        $sheetType = explode('x', $sheetData['sheet_type']);
        $sheetWidth = (float) ($sheetType[0] ?? 0);
        $sheetHeight = (float) ($sheetType[1] ?? 0);

        $svgWidth = ($sheetWidth / $this->scale) + ($this->margin * 2);
        $svgHeight = ($sheetHeight / $this->scale) + ($this->margin * 2);

        $svg = $this->generateSvgHeader($svgWidth, $svgHeight);

        // Draw sheet background
        $svg .= $this->drawSheetBackground($sheetWidth, $sheetHeight);

        // Draw each piece
        foreach ($sheetData['placements'] as $placement) {
            $svg .= $this->drawPiece($placement);
        }

        // Draw sheet info
        $svg .= $this->drawSheetInfo($sheetData, $svgWidth, $svgHeight);

        $svg .= $this->generateSvgFooter();

        return $svg;
    }

    /**
     * Generate SVG header.
     *
     * @param float $width
     * @param float $height
     * @return string
     */
    protected function generateSvgHeader(float $width, float $height): string
    {
        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink"
     width="{$width}"
     height="{$height}"
     viewBox="0 0 {$width} {$height}">
  <defs>
    <style>
      .piece { fill: {$this->fillColor}; stroke: {$this->strokeColor}; stroke-width: 2; }
      .piece-label { fill: {$this->textColor}; font-family: Arial, sans-serif; font-size: 12px; text-anchor: middle; }
      .sheet-bg { fill: #ffffff; stroke: #000000; stroke-width: 3; }
      .info-text { fill: {$this->textColor}; font-family: Arial, sans-serif; font-size: 14px; }
    </style>
  </defs>

SVG;
    }

    /**
     * Draw sheet background.
     *
     * @param float $width
     * @param float $height
     * @return string
     */
    protected function drawSheetBackground(float $width, float $height): string
    {
        $x = $this->margin;
        $y = $this->margin;
        $w = $width / $this->scale;
        $h = $height / $this->scale;

        return <<<SVG
  <!-- Sheet Background -->
  <rect class="sheet-bg" x="{$x}" y="{$y}" width="{$w}" height="{$h}" />

SVG;
    }

    /**
     * Draw a piece on the sheet.
     *
     * @param array $placement
     * @return string
     */
    protected function drawPiece(array $placement): string
    {
        $x = $this->margin + ($placement['x'] / $this->scale);
        $y = $this->margin + ($placement['y'] / $this->scale);
        $width = $placement['width'] / $this->scale;
        $height = $placement['height'] / $this->scale;

        $centerX = $x + ($width / 2);
        $centerY = $y + ($height / 2);

        $label = $placement['name'];
        $dimensions = round($placement['width']) . 'x' . round($placement['height']);
        $rotatedText = $placement['rotated'] ? ' (R)' : '';

        return <<<SVG
  <!-- Piece: {$label} -->
  <g>
    <rect class="piece"
          x="{$x}"
          y="{$y}"
          width="{$width}"
          height="{$height}" />
    <text class="piece-label" x="{$centerX}" y="{$centerY}">
      {$label}
    </text>
    <text class="piece-label" x="{$centerX}" y="{$centerY}" dy="15">
      {$dimensions}{$rotatedText}
    </text>
  </g>

SVG;
    }

    /**
     * Draw sheet information.
     *
     * @param array $sheetData
     * @param float $svgWidth
     * @param float $svgHeight
     * @return string
     */
    protected function drawSheetInfo(array $sheetData, float $svgWidth, float $svgHeight): string
    {
        $sheetNumber = $sheetData['sheet_number'];
        $sheetType = $sheetData['sheet_type'];
        $utilization = round($sheetData['utilization_percent'], 1);
        $waste = round($sheetData['waste_percent'], 1);
        $piecesCount = count($sheetData['placements']);

        $infoY = $svgHeight - 20;

        return <<<SVG
  <!-- Sheet Info -->
  <g>
    <text class="info-text" x="10" y="{$infoY}">
      Chapa #{$sheetNumber} - {$sheetType}mm |
      {$piecesCount} peças |
      Aproveitamento: {$utilization}% |
      Desperdício: {$waste}%
    </text>
  </g>

SVG;
    }

    /**
     * Generate SVG footer.
     *
     * @return string
     */
    protected function generateSvgFooter(): string
    {
        return "</svg>";
    }

    /**
     * Generate multiple SVGs for all sheets.
     *
     * @param array $sheets
     * @return array
     */
    public function generateMultipleSvgs(array $sheets): array
    {
        $svgs = [];

        foreach ($sheets as $sheet) {
            $svgs[] = [
                'sheet_number' => $sheet['sheet_number'],
                'svg' => $this->generateSvg($sheet),
            ];
        }

        return $svgs;
    }

    /**
     * Set scale factor.
     *
     * @param int $scale
     * @return self
     */
    public function setScale(int $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * Set colors.
     *
     * @param string $stroke
     * @param string $fill
     * @param string $text
     * @return self
     */
    public function setColors(string $stroke, string $fill, string $text): self
    {
        $this->strokeColor = $stroke;
        $this->fillColor = $fill;
        $this->textColor = $text;
        return $this;
    }
}
