# RUKRE

RUKRE is a private streaming site for the media on your home server. It indexes your **videos**, **music and audio**, and **eBooks** (EPUB, PDF, CBZ comics and more), then lets you watch, listen and read in the browser.

It's built with Laravel 13, and the interface follows the "Kinetic Obsidian" design system from the Stitch export in [`docs/DESIGN.md`](docs/DESIGN.md).

## Features

- **Videos:** grid with folder filters, a player that saves your position and resumes it, episode lists for show folders, and autoplay of the next episode.
- **Audio:** albums and folders, track lists, and a mini-player that keeps playing while you browse. It supports shuffle and lock-screen / media-key controls.
- **Books:**
  - EPUB reader with chapters, serif/sans switch, text size and progress tracking.
  - Continuous-scroll reader for CBZ comics and webtoons.
  - PDFs open in the browser's viewer. Plain `.txt` files open in a reading view.
  - Other formats (MOBI, AZW3, FB2, DJVU, CBR) can be downloaded.
- **Continue shelf** on the home page, saved separately for each user.
- **Search** across titles, artists, authors, albums and folders.
- **Metadata and cover art:**
  - Audio and video: ID3 and other tags (via getID3), including embedded album art.
  - EPUB: title, author and cover.
  - Artwork files next to the media, such as `Movie.jpg`, `cover.jpg` or `folder.jpg`.
  - Optional: a poster frame from each video (needs `ffmpeg`) and a cover from each PDF's first page (needs `pdftoppm`).
- **Streaming with seeking support** (HTTP Range) from a local folder, an NFS/SMB mount, or over **SFTP**.
- **Sign-in required by default**, so your library isn't open to the internet.

## Requirements

- PHP 8.3+ with the `gd`, `zip`, `fileinfo`, `pdo_sqlite` (or MySQL/Postgres) extensions
- Composer
- Node.js 20+ (only to build the CSS/JS)

## Setup

```bash
git clone <this repo> rukre && cd rukre
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
```

### 1. Point RUKRE at your media

Your media folder should look something like this (folder names are configurable):

```
/srv/media
├── Videos/
│   ├── Some Movie (2020).mp4
│   ├── Some Movie (2020).jpg        ← optional poster
│   └── My Show/                     ← a folder becomes a "collection"
│       ├── S01E01 Pilot.mp4
│       └── poster.jpg
├── Music/
│   └── Artist - Album/
│       ├── 01 - First Song.mp3
│       └── cover.jpg
└── Books/
    ├── Fiction/Great Novel.epub
    ├── Manuals/Router.pdf
    └── Comics/Chapter 1.cbz
```

Then edit `.env`. Choose the option that matches your setup.

**A. RUKRE runs on the home server itself** (simplest)

```dotenv
RUKRE_MEDIA_DRIVER=local
RUKRE_MEDIA_ROOT=/srv/media
RUKRE_VIDEO_DIRS=Videos
RUKRE_AUDIO_DIRS=Music
RUKRE_BOOK_DIRS=Books
```

**B. Media sits on a NAS.** Mount the share (NFS or SMB/CIFS) read-only on the machine running RUKRE, then use option A with the mount path.

```bash
# Example: SMB share mounted read-only
sudo mount -t cifs //nas.local/media /mnt/media -o ro,username=me,uid=www-data
```

**C. Read straight from another machine over SSH/SFTP**

```dotenv
RUKRE_MEDIA_DRIVER=sftp
RUKRE_MEDIA_ROOT=/srv/media
RUKRE_SFTP_HOST=192.168.1.20
RUKRE_SFTP_USERNAME=media
RUKRE_SFTP_PRIVATE_KEY=/home/rukre/.ssh/id_ed25519
```

Over SFTP, RUKRE downloads files up to `RUKRE_REMOTE_DOWNLOAD_LIMIT` MB (default 60) to read their tags and covers. Larger files are indexed by file name only. Options A and B are faster for big video libraries.

To index several folders for one library, separate them with commas: `RUKRE_VIDEO_DIRS="Movies,TV Shows"`.

Whatever user runs PHP (for example `www-data`) needs **read** access to the media folders.

### 2. Create your account and scan

```bash
php artisan rukre:user you@example.com --name="You"   # prompts for a password
php artisan rukre:scan                                 # index everything
```

`rukre:scan` only re-reads files that changed. Deleted files are removed from the library. If a library folder is missing, for example when the NAS is offline, nothing is removed. `--force` re-reads every file.

To keep the library up to date automatically, run Laravel's scheduler. It rescans every hour by default (`RUKRE_SCAN_SCHEDULE`):

```cron
* * * * * cd /path/to/rukre && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Serve it

For a quick try: `composer run dev`, or `php artisan serve --host=0.0.0.0`. Then open `http://<server-ip>:8000`.

The built-in PHP server handles one request at a time, so one video stream can block other requests. For everyday use, run RUKRE behind **nginx + PHP-FPM**, **Caddy + PHP-FPM**, or **FrankenPHP**, pointing the web root at `public/`. Put it behind HTTPS if you reach it from outside your home network.

## Browser format support

Browsers play MP4 (H.264/AAC), WebM, MP3, AAC/M4A, FLAC, OGG/Opus and WAV. MKV, AVI and WMV files often won't play in the browser. RUKRE shows a warning on those, plus a download button and a direct stream link you can open in VLC. To make them play everywhere, re-encode them to MP4.

## Settings reference

| Variable | Default | Purpose |
| --- | --- | --- |
| `RUKRE_MEDIA_DRIVER` | `local` | `local` or `sftp` |
| `RUKRE_MEDIA_ROOT` | `storage/app/media` | Root folder of your media |
| `RUKRE_VIDEO_DIRS` / `RUKRE_AUDIO_DIRS` / `RUKRE_BOOK_DIRS` | `Videos` / `Music` / `Books` | Library folders (comma separated, empty to disable) |
| `RUKRE_REQUIRE_LOGIN` | `true` | Require sign-in before browsing or streaming |
| `RUKRE_SCAN_SCHEDULE` | `0 * * * *` | Cron schedule for automatic rescans (empty to disable) |
| `RUKRE_REMOTE_DOWNLOAD_LIMIT` | `60` | Max MB fetched over SFTP to read tags/covers |
| `RUKRE_FFMPEG` / `RUKRE_PDFTOPPM` | `ffmpeg` / `pdftoppm` | Optional tools for video posters and PDF covers |

## Development

```bash
composer run dev          # app server, queue, logs and Vite together
php artisan test          # test suite
vendor/bin/pint           # code style
```

The main code:

- `app/Media`: the scanner, metadata and cover extraction, and Range streaming
- `app/Http/Controllers`: the pages
- `resources/views`: the Blade templates
- `resources/js/modules`: the mini-player, video player and readers
