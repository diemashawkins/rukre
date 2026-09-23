<?php

namespace Tests\Feature;

use App\Media\MediaStreamer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RangeParsingTest extends TestCase
{
    /**
     * @return array<string, array{0: ?string, 1: array{0: int, 1: int}|null|false}>
     */
    public static function ranges(): array
    {
        return [
            'no header' => [null, null],
            'open ended' => ['bytes=100-', [100, 999]],
            'closed' => ['bytes=0-499', [0, 499]],
            'suffix' => ['bytes=-200', [800, 999]],
            'end past size is clamped' => ['bytes=900-5000', [900, 999]],
            'start past size' => ['bytes=1000-', false],
            'reversed' => ['bytes=500-100', false],
            'multiple ranges are ignored' => ['bytes=0-1,5-6', null],
        ];
    }

    #[DataProvider('ranges')]
    public function test_it_parses_range_headers(?string $header, array|null|false $expected): void
    {
        $this->assertSame($expected, app(MediaStreamer::class)->parseRange($header, 1000));
    }
}
