<?php

namespace App\Support;

/**
 * Renders the smooth area/line chart used on the dashboard.
 *
 * This mirrors the `areaChart()` helper in caf-system2.html so the server
 * rendered SVG is visually identical to the prototype, and the chart still
 * renders with JavaScript disabled.
 */
class AreaChart
{
    private const WIDTH = 600;

    private const HEIGHT = 190;

    private const PAD = ['t' => 16, 'r' => 8, 'b' => 26, 'l' => 8];

    /**
     * @param  list<float|int>  $values
     * @param  list<string>  $labels
     */
    public static function render(
        array $values,
        array $labels,
        string $color = 'var(--primary)',
        string $fill = '#E4572E',
        string $ariaLabel = 'Trend chart',
    ): string {
        $values = array_map(static fn (float|int|null $v): float => (float) $v, $values);
        $values = $values === [] ? [0.0] : array_values($values);
        $labels = array_values($labels);

        $max = (max($values) ?: 1) * 1.15;
        $innerWidth = self::WIDTH - self::PAD['l'] - self::PAD['r'];
        $innerHeight = self::HEIGHT - self::PAD['t'] - self::PAD['b'];
        $last = count($values) - 1;

        $points = [];
        foreach ($values as $i => $value) {
            $points[] = [
                self::PAD['l'] + ($last === 0 ? 0 : $i / $last * $innerWidth),
                self::PAD['t'] + $innerHeight - ($value / $max) * $innerHeight,
            ];
        }

        $line = self::smoothPath($points);
        $baseline = self::PAD['t'] + $innerHeight;
        $area = sprintf(
            '%s L%s,%s L%s,%s Z',
            $line,
            self::num($points[$last][0]),
            self::num($baseline),
            self::num($points[0][0]),
            self::num($baseline),
        );

        $grid = '';
        foreach ([0, 1, 2, 3] as $i) {
            $y = self::PAD['t'] + ($innerHeight / 3) * $i;
            $grid .= sprintf(
                '<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="var(--border)" stroke-width="1" vector-effect="non-scaling-stroke" stroke-dasharray="%s"/>',
                self::num(self::PAD['l']),
                self::num($y),
                self::num(self::WIDTH - self::PAD['r']),
                self::num($y),
                $i === 3 ? '0' : '3 5',
            );
        }

        $texts = '';
        foreach ($labels as $i => $label) {
            $x = $last === 0
                ? self::PAD['l']
                : self::PAD['l'] + $i / max(1, count($labels) - 1) * $innerWidth;

            $texts .= sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-size="10" fill="var(--text-3)" font-family="inherit" font-weight="700">%s</text>',
                self::num($x),
                self::num(self::HEIGHT - 6),
                e($label),
            );
        }

        $dots = '';
        foreach ($points as $i => $point) {
            $title = e(($labels[$i] ?? '').': '.number_format($values[$i], 0));
            $dots .= sprintf(
                '<circle cx="%s" cy="%s" r="3.5" fill="var(--surface)" stroke="%s" stroke-width="2" vector-effect="non-scaling-stroke"><title>%s</title></circle>',
                self::num($point[0]),
                self::num($point[1]),
                $color,
                $title,
            );
        }

        $gradientId = 'grad-'.substr(md5($area), 0, 8);

        return sprintf(
            '<div class="chart-wrap"><svg viewBox="0 0 %d %d" preserveAspectRatio="none" role="img" aria-label="%s">'
            .'<defs><linearGradient id="%s" x1="0" y1="0" x2="0" y2="1">'
            .'<stop offset="0%%" stop-color="%s" stop-opacity="0.32"/><stop offset="100%%" stop-color="%s" stop-opacity="0"/>'
            .'</linearGradient></defs>%s<path d="%s" fill="url(#%s)"/>'
            .'<path d="%s" fill="none" stroke="%s" stroke-width="2.4" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"/>%s%s</svg></div>',
            self::WIDTH,
            self::HEIGHT,
            e($ariaLabel),
            $gradientId,
            e($fill),
            e($fill),
            $grid,
            e($area),
            $gradientId,
            e($line),
            e($color),
            $dots,
            $texts,
        );
    }

    /**
     * Catmull-Rom style smoothing, matching the prototype's `smoothPath()`.
     *
     * @param  list<array{0: float, 1: float}>  $points
     */
    private static function smoothPath(array $points): string
    {
        if ($points === []) {
            return '';
        }

        $path = 'M'.self::num($points[0][0]).','.self::num($points[0][1]);
        $count = count($points);

        for ($i = 0; $i < $count - 1; $i++) {
            [$x0, $y0] = $points[$i];
            [$x1, $y1] = $points[$i + 1];
            $cx = ($x0 + $x1) / 2;

            $path .= sprintf(
                ' C%s,%s %s,%s %s,%s',
                self::num($cx), self::num($y0),
                self::num($cx), self::num($y1),
                self::num($x1), self::num($y1),
            );
        }

        return $path;
    }

    private static function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') ?: '0';
    }
}
