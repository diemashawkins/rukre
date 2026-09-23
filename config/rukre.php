<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk (see config/filesystems.php) that holds the media on
    | your home server. By default this is the "media" disk, which points at
    | RUKRE_MEDIA_ROOT on this machine or at an SFTP host on your network.
    |
    */

    'disk' => env('RUKRE_MEDIA_DISK', 'media'),

    /*
    |--------------------------------------------------------------------------
    | Libraries
    |--------------------------------------------------------------------------
    |
    | Folders (relative to the media disk root) that are scanned for each kind
    | of media. Separate several folders with commas. Leave a value empty to
    | switch that library off.
    |
    */

    'libraries' => [
        'video' => env('RUKRE_VIDEO_DIRS', 'Videos'),
        'audio' => env('RUKRE_AUDIO_DIRS', 'Music'),
        'book' => env('RUKRE_BOOK_DIRS', 'Books'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Access
    |--------------------------------------------------------------------------
    |
    | When true, visitors must sign in before browsing or streaming. Create
    | accounts with `php artisan rukre:user`. Only switch this off when the
    | site is reachable from your home network alone.
    |
    */

    'require_login' => (bool) env('RUKRE_REQUIRE_LOGIN', true),

    /*
    |--------------------------------------------------------------------------
    | Scanning
    |--------------------------------------------------------------------------
    |
    | How often the scheduler re-indexes the libraries (a cron expression, or
    | empty to only scan manually with `php artisan rukre:scan`). Remote files
    | larger than "remote_download_limit" megabytes are never copied locally
    | just to read their metadata or cover art.
    |
    */

    'scan_schedule' => env('RUKRE_SCAN_SCHEDULE', '0 * * * *'),

    'remote_download_limit' => (int) env('RUKRE_REMOTE_DOWNLOAD_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Optional Tools
    |--------------------------------------------------------------------------
    |
    | When available, ffmpeg grabs a poster frame from videos and pdftoppm
    | (from poppler-utils) renders the first page of PDFs as a cover.
    |
    */

    'ffmpeg' => env('RUKRE_FFMPEG', 'ffmpeg'),

    'pdftoppm' => env('RUKRE_PDFTOPPM', 'pdftoppm'),

];
