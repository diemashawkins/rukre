---
name: Kinetic Obsidian
colors:
  surface: '#131315'
  surface-dim: '#131315'
  surface-bright: '#39393b'
  surface-container-lowest: '#0e0e10'
  surface-container-low: '#1b1b1d'
  surface-container: '#201f21'
  surface-container-high: '#2a2a2c'
  surface-container-highest: '#353437'
  on-surface: '#e5e1e4'
  on-surface-variant: '#d0c6ab'
  inverse-surface: '#e5e1e4'
  inverse-on-surface: '#303032'
  outline: '#999077'
  outline-variant: '#4d4632'
  surface-tint: '#ecc300'
  primary: '#fff3d4'
  on-primary: '#3b2f00'
  primary-container: '#ffd300'
  on-primary-container: '#705b00'
  inverse-primary: '#715c00'
  secondary: '#c8c6c9'
  on-secondary: '#303033'
  secondary-container: '#47464a'
  on-secondary-container: '#b6b4b8'
  tertiary: '#fff0ef'
  on-tertiary: '#68000a'
  tertiary-container: '#ffcbc7'
  on-tertiary-container: '#b81923'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffe17a'
  primary-fixed-dim: '#ecc300'
  on-primary-fixed: '#231b00'
  on-primary-fixed-variant: '#554500'
  secondary-fixed: '#e4e1e5'
  secondary-fixed-dim: '#c8c6c9'
  on-secondary-fixed: '#1b1b1e'
  on-secondary-fixed-variant: '#47464a'
  tertiary-fixed: '#ffdad7'
  tertiary-fixed-dim: '#ffb3ad'
  on-tertiary-fixed: '#410004'
  on-tertiary-fixed-variant: '#930013'
  background: '#131315'
  on-background: '#e5e1e4'
  surface-variant: '#353437'
  neon-cyan: '#06B6D4'
  electric-yellow-hover: '#FFE600'
  surface-deep: '#0B0B0C'
  surface-card: '#18181B'
  surface-elevated: '#2E2E33'
  text-primary: '#FFFFFF'
  text-muted: '#A1A1AA'
  text-dim: '#71717A'
  border-subtle: '#27272A'
typography:
  display-hero:
    fontFamily: Syne
    fontSize: 56px
    fontWeight: '800'
    lineHeight: 64px
    letterSpacing: -0.03em
  display-hero-mobile:
    fontFamily: Syne
    fontSize: 36px
    fontWeight: '800'
    lineHeight: 42px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Syne
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Syne
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Syne
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Syne
    fontSize: 18px
    fontWeight: '700'
    lineHeight: 24px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 22px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-lg:
    fontFamily: Space Grotesk
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 18px
    letterSpacing: 0.05em
  label-md:
    fontFamily: Space Grotesk
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.06em
  label-sm:
    fontFamily: Space Grotesk
    fontSize: 10px
    fontWeight: '700'
    lineHeight: 12px
    letterSpacing: 0.08em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-lg: 1.5rem
  margin: 1rem
  margin-md: 2rem
  margin-lg: 3rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

This design system powers a high-octane multimedia entertainment platform catering to digital creators, avid binge-watchers, webtoon vertical scrollers, serial fiction readers, and podcast listeners. It bridges the rebellious swagger of indie animation hubs with the obsessive utility of premium streaming apps. The audience demands lightning-fast navigation, visceral visual feedback, and total immersion without visual fatigue during late-night sessions.

The design movement combines **High-Contrast Dark Mode** with **Tactile Cyber-Brutalism and Glassmorphism accents**:
- **Pitch Matte Surfaces:** Absolute blacks paired with deep charcoal containers prevent light bleed and direct 100% focus onto dynamic thumbnail art, vertical panels, and audio waveforms.
- **Electric Accent Striking:** Hyper-saturated neon gold/yellow punctures the dark architecture to signify active streams, critical calls to action, and live statuses.
- **Micro-Textures & Translucency:** Subtle 1px tech borders, glassmorphic floating overlays (like persistent audio docks), and solid pill-shaped badges create distinct spatial layering.

## Colors

The palette is engineered exclusively for an OLED-optimized dark experience.

- **Primary (`#FFD300` / Electric Yellow):** The signature brand shock value. Reserved for high-value interactive states: primary CTA buttons, scrubber heads, current playback tracks, live broadcast indicators, and VIP badge highlights. Never used for massive background slabs.
- **Secondary (`#27272A` / Zinc Graphite):** Provides structural baseline contrasts, interactive button outlines, chip fills, and subtle grid dividers.
- **Tertiary (`#EF4444` / Crimson Blast):** Dedicated to high-urgency triggers: live tags, NSFW/mature content warnings, exclusive drop markers, and critical destructive actions.
- **Neutral (`#121214` / Pitch Obsidian):** The foundational base canvas across the entire platform. Subdued just enough above `#000000` to allow true pitch overlays and depth staging.
- **Accent Named Colors:**
  - `neon-cyan` (`#06B6D4`): Used specifically for Sci-Fi/Cyberpunk genres, system notices, and digital collectible/creator flair.
  - Tonal surfaces (`surface-deep` at `#0B0B0C`, `surface-card` at `#18181B`, `surface-elevated` at `#2E2E33`) handle progressive spatial elevation without introducing color cast.

## Typography

The typography creates a high-energy contrast between avant-garde structural display fonts, ultra-legible modern body sans, and technical monospace-tinged labels:

- **Headlines (Syne):** Syne delivers distinct, bold geometry that feels artistic, editorial, and punchy. In uppercase or tight title case, it establishes a recognizable, unapologetic media-first presence across banner carousels, show titles, and creator headers.
- **Body (Plus Jakarta Sans):** Offers balanced proportions, open apertures, and exceptional readability at small sizes on OLED screens. It powers story synopses, user comments, community forums, and episode metadata.
- **Labels, Badges, & Metadata (Space Grotesk):** Gives runtime counters, episode trackers, chapter tags, and category chips an edgy, technical precision.
- **Editorial Fiction Mode:** For long-form serial text reading (AO3/Wattpad style), the interface must offer users an inline switch to a serif typeface (`Literata` or `Source Serif 4`) at 18px with relaxed line heights (1.75).

## Layout & Spacing

The layout utilizes a dynamic fluid grid system constructed on an 8pt spatial grid, scaling horizontally across screen viewports:

- **Desktop (12 Columns, `> 1200px`):** Max container width of 1600px. 24px gutters, 48px outer canvas margin. Used for multi-shelf homepages, side-by-side video/chat feeds, and panoramic catalog views.
- **Tablet (8 Columns, `768px - 1199px`):** 16px gutters, 32px margins. Reflows media shelves into horizontal scroll carousels with peek overflow (15% glimpse of next card).
- **Mobile (4 Columns, `< 768px`):** 16px gutters, 16px margins. Content transitions into single-column vertical feeds (webtoon continuous roll, stacked video feeds) and pinned floating controls.
- **Bottom Clearance Rule:** All primary views must account for a permanent 64px–80px bottom safe zone on desktop and mobile to accommodate the persistent Spotify-style mini-player bar and mobile thumb navigation.

## Elevation & Depth

Rather than relying on diffuse daylight drop shadows, this design system establishes hierarchy through **tactile tonal layering, edge definition, and luminous glows**:

- **Level 0 (Canvas Base):** `#0B0B0C` to `#121214`. Pure background for primary content viewing.
- **Level 1 (Card & Content Blocks):** `#18181B` surface color with a 1px continuous solid border of `#27272A`. Zero blur shadow.
- **Level 2 (Dropdowns, Flyouts & Modals):** `#222226` background, bordered with `#3F3F46`, combined with a directional hard drop shadow: `0px 12px 32px rgba(0, 0, 0, 0.7)`.
- **Level 3 (Floating HUD & Persistent Mini-Player):** Frosted glass panel with `backdrop-filter: blur(20px)`, background of `rgba(18, 18, 20, 0.82)`, and an electric accent top-line highlight: 1px linear gradient (`rgba(255, 211, 0, 0.4)` to `transparent`).
- **Active State Glow:** Focused cards or active episode selectors emit a localized neon rim: `box-shadow: 0 0 16px rgba(255, 211, 0, 0.25)`.

## Shapes

The shape system adopts a hybrid approach: **tight, controlled geometric radii** for content surfaces, juxtaposed with **extreme pill rounds** for interactive controls:

- **Media & Content Cards:** 8px (`rounded-md` / `roundedness: 2`) border radius preserves screen real estate and keeps video thumbnails (16:9) and webtoon posters (3:4) structured and punchy.
- **Pill Elements:** Interactive chips, filter pills, follow buttons, and audio playheads use `9999px` full-rounded capsules to encourage instantaneous tap recognition and contrast against blocky thumbnail grids.
- **Modals & Bottom Sheets:** 16px (`rounded-xl`) on upper corners for mobile sheets, conveying modern mobile-native touchpoints.

## Components

### Buttons
- **Primary Action:** Solid `#FFD300` background with pitch black text (`#000000`), bold `Space Grotesk` uppercase label, full pill radius (`rounded-full`), height 44px (48px on mobile). On hover: `#FFE600` with subtle scale transition (`scale(1.02)`).
- **Secondary Action:** Transparent background with 1.5px solid border of `#27272A`, white text (`#FFFFFF`). Hover fills with `#27272A`.
- **Ghost/Icon Button:** 40px circular touch target, `#18181B` background, `#A1A1AA` icon color, transitioning to `#FFD300` icon with white glow on hover.

### Chips & Tags
- **Category / Filter Chips:** Pill-shaped capsules with 8px horizontal padding, 4px vertical. Inactive: `#18181B` fill, `#27272A` stroke, `#A1A1AA` text. Active: `#FFD300` fill with `#000000` text.
- **Badge Alerts ("LIVE", "NEW", "18+"):** Compact rectangular or semi-pill labels. Tertiary crimson (`#EF4444`) with white bold text for "LIVE", or neon cyan (`#06B6D4`) for original platform exclusives.

### Media Cards
- **Video Cards (16:9):** Subtle 1px `#27272A` border, 8px radius. Hover reveals an animated scrubber preview line in `#FFD300` at card base and duration pill overlay in the bottom right corner (`rgba(0,0,0,0.85)`).
- **Webtoon / Comic Cards (3:4):** Vertical format featuring full-bleed artwork, gradient dark scrim at bottom, bold chapter counter pill on top left, and vibrant progress bar.
- **Audio / Novel Shelves (1:1):** Compact square cover art with title underneath in `body-md` bold, creator handle in `text-dim`.

### Persistent Mini-Player (Bottom Bar)
- Floats docked at the bottom of the screen above mobile navigation. Height 68px.
- Glassmorphism dark backing (`rgba(18, 18, 20, 0.85)`), hairline top border (`#27272A`).
- Left: Square thumbnail + title + creator marquee. Center: Play/Pause/Skip with yellow active progress scrubber running across the entire top edge of the bar. Right: Volume slider, chapter list, expand icon.

### Form Inputs & Search
- Inputs feature `#18181B` background, 1px `#27272A` border, 8px corner radius, and crisp `#FFFFFF` typography.
- Focus state drops border and gains a crisp 2px border in `#FFD300` with zero ambient halo for a sharp technical look. Placeholder text styled in `#71717A`.

### Lists & Episode Grids
- Rows contain 48px square thumbnail, episode index (in bold `Space Grotesk`), episode title, release date, and duration.
- Row hover produces a `#18181B` solid background fill and turns the index number into a yellow "Play" icon.