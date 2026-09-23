<?php

use Illuminate\Support\Facades\Schedule;

if ($schedule = config('rukre.scan_schedule')) {
    Schedule::command('rukre:scan')->cron($schedule)->withoutOverlapping();
}
