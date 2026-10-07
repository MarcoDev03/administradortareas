---
name: Go Platform System
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#434655'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#784b00'
  on-tertiary: '#ffffff'
  tertiary-container: '#996100'
  on-tertiary-container: '#ffeedd'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  display-hero:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: 56px
    letterSpacing: -0.03em
  display-hero-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 34px
    fontWeight: '800'
    lineHeight: 40px
    letterSpacing: -0.025em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: -0.005em
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0em
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.01em
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 14px
    letterSpacing: 0.04em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-tablet: 1.25rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-tablet: 2rem
  margin-desktop: 3rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2.5rem
---

## Brand & Style

The design system embodies a fast-paced, hyper-local discovery platform built for the vibrant urban density and entrepreneurial pulse of Bolivia. The visual tone balances startup velocity with deep civic trustworthiness. It avoids both sterile utility and nostalgic folklore, leaning decisively into an electric, modern startup aesthetic that feels brisk, reliable, and tactile.

The design movement combines **Modern High-Density Utility** with **Crisp Geometric Minimalism**:
- **Energy & Agility:** Vibrant, luminescent primary and signal accents communicate real-time activity (what is open right now, instant delivery, immediate routes).
- **Radical Legibility:** High contrast, sharp slate typography, and clear visual hierarchy ensure that users navigating on high-glare mobile screens in bright sunlight can orient themselves instantly.
- **Trust & Verification:** Clean structural badges, crisp borders, and calm neutral canvases ground the energetic primary hues, elevating community-reviewed businesses into authentic civic landmarks.

## Colors

The palette establishes immediate recognition through an electric digital indigo alongside high-visibility status indicators. 

### Palette Roles
- **Primary (`#2563EB` - Electric Indigo):** Drives the primary actions, interactive states, brand touchpoints, active category indicators, and navigation focus. 
- **Secondary (`#10B981` - Vivid Emerald):** Reserved strictly for positive operational states: "Abierto Ahora" (Open Now) badges, verified merchant checkmarks, success states, and live capacity meters.
- **Tertiary (`#F59E0B` - Amber Glow):** Dedicated to user ratings, aggregate review stars, closing-soon alerts, and promoted local gems.
- **Neutral (`#0F172A` - Deep Slate):** Anchors high-priority headlines, body copy, and prominent structural framing, paired against ultra-clean background neutrals (`#F8FAFC` base surface, `#FFFFFF` elevated cards).

### State Tokens & Application
- **Surface Base:** `#F8FAFC` provides a clean, glare-resistant background.
- **Surface Elevated:** `#FFFFFF` creates discrete card layers against the base.
- **Surface Subtle:** `#F1F5F9` for chip backgrounds, secondary input fills, and disabled states.
- **Border Subtle:** `#E2E8F0` defines cards and inputs with whisper-thin precision.
- **Text Primary:** `#0F172A` ensures crisp contrast adhering strictly to WCAG AAA targets.
- **Text Secondary:** `#64748B` provides balanced metadata separation without falling into unreadable low contrast.

## Typography

The design system exclusively adopts **Plus Jakarta Sans** across all roles to achieve a unified, contemporary tech profile with friendly geometric curves and exceptional micro-legibility.

### Hierarchy & Typesetting Principles
- **Headlines & Titles:** Set with tighter negative letter spacing (`-0.03em` to `-0.01em`) and punchy weights (`700` and `800`) to create strong editorial hierarchy.
- **Numbers & Metrics:** Ratings, delivery times, and distances inherit the headline weights for immediate glanceability in crowded feeds.
- **Labels & Micro-copy:** Uppercase or small-caps badges utilize `label-sm` with widened letter-spacing (`0.04em`) to ensure legibility when overlaid on imagery or map pins.

## Layout & Spacing

The layout structure relies on a responsive fluid grid designed to accommodate discovery feeds, split map-and-list viewports, and granular business profile panels.

### Grid & Breakpoints
- **Mobile (< 640px):** Single-column presentation, 4-column sub-grid, `1rem` margin, and `1rem` gutter. Bottom-anchored navigation and sticky floating action bars.
- **Tablet (640px – 1024px):** 8-column layout with `1.25rem` gutter and `2rem` outer margins. Accommodates two-column merchant discovery grids or split-screen map layouts.
- **Desktop (> 1024px):** 12-column layout maxing out at `1280px` centered content width with `1.5rem` gutters and `3rem` margins. Supports multi-column category exploration alongside fixed map sidebars.

### Spatial Rhythm
All layout margins, internal component padding, and inter-element distances map rigidly to multiples of `4px` (`0.25rem`), maintaining strict vertical and horizontal alignment across micro-components and macro-containers.

## Elevation & Depth

Visual hierarchy leverages a hybrid model of **crisp structural borders (`#E2E8F0`)** paired with **ambient diffused shadows** tinted with deep neutral blue. This eliminates heavy, murky shadows while preserving tactile separation.

### Elevation Levels
- **Level 0 (Flat):** Base canvas (`#F8FAFC`). No shadow. Internal utility panels, segment dividers, and inactive form fields.
- **Level 1 (Card Rest):** Background `#FFFFFF`, border `1px solid #E2E8F0`, shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.03)`. Used for directory cards, list tiles, and secondary buttons.
- **Level 2 (Hover / Active Card):** Background `#FFFFFF`, border `1px solid #CBD5E1`, shadow: `0 10px 15px -3px rgba(15, 23, 42, 0.07), 0 4px 6px -4px rgba(15, 23, 42, 0.04)`. Applied to active list cards, interactive cards on hover, and active filter popovers.
- **Level 3 (Floating Overlays / Modals):** Background `#FFFFFF`, border `1px solid rgba(226, 232, 240, 0.8)`, shadow: `0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05)`. Used for bottom sheets on mobile, sticky floating search bars, map control groupings, and modal dialogs.

## Shapes

The system implements a consistent **12px (`0.75rem` / rounded-xl)** curvature baseline for cards and interactive components. This produces a approachable, modern profile that stays structural without drifting into bubble-like forms.

### Shape Scale
- **Pill / Full (`9999px`):** Used exclusively for status chips ("Abierto Ahora"), category tags, interactive filter toggles, search bars, and circular icon triggers.
- **Rounded-xl (`12px` / `0.75rem`):** The primary container radius. Applied to merchant listing cards, promotional banners, modal dialogs, and primary input controls.
- **Rounded-lg (`8px` / `0.5rem`):** Applied to internal media thumbnails, map marker preview bubbles, nested dropdown panels, and sub-action buttons.
- **Rounded-sm (`4px` / `0.25rem`):** Reserved for checkboxes, micro-ratings pills, and progress bars.

## Components

### Buttons
- **Primary:** Background `#2563EB`, text `#FFFFFF`, font `label-lg`, radius `12px` (or `9999px` for quick-action pills). Hover state transitions to `#1D4ED8` with subtle scale-up (`scale(1.01)`).
- **Secondary:** Surface `#FFFFFF`, border `1px solid #E2E8F0`, text `#0F172A`. Hover shifts to background `#F8FAFC` and border `#CBD5E1`.
- **Tertiary / Ghost:** No border, background transparent, text `#2563EB` or `#64748B`. Hover renders subtle `#EFF6FF` wash.

### Chips & Badges
- **Status Badge ("Abierto Ahora"):** Pill shape (`9999px`), background `#ECFDF5`, text `#065F46`, border `1px solid #A7F3D0`. Includes a 6px pulsing emerald dot (`#10B981`) preceding the copy.
- **Verification Badge:** Icon-only or compact lockup with `#2563EB` fill and `#FFFFFF` checkmark or `#10B981` subtle outline badge.
- **Category Filter Chips:** Pill shape (`9999px`), default background `#FFFFFF`, border `1px solid #E2E8F0`, text `#475569`. Selected state triggers background `#0F172A`, text `#FFFFFF`, border `#0F172A`.

### Listing & Merchant Cards
- Composed of `#FFFFFF` surface, `1px solid #E2E8F0` border, `12px` radius, and Level 1 elevation.
- Includes a 16:10 aspect ratio image container with top corners rounded (`12px`), featuring an absolute-positioned status badge ("Abierto Ahora" / "Cerrado") top-left, and favorite trigger top-right.
- Content zone stacks: Business title (`headline-sm`), verified badge icon inline, category & neighborhood metadata (`body-sm`), rating score + review tally, and estimated transit/delivery distance in bold metadata.

### Inputs & Search Bars
- **Global Discovery Search:** Pill shape (`9999px`), background `#FFFFFF`, border `1px solid #E2E8F0`, Level 2 ambient elevation. Prefix with vibrant `#2563EB` search icon; suffix with quick-access geolocation or filter modal triggers.
- **Form Inputs:** Height `48px`, border `1px solid #CBD5E1`, radius `12px`, padding `0.75rem 1rem`. Focus state transitions to border `#2563EB` and subtle outline ring (`4px` width of `#2563EB` at 15% opacity).

### Checkboxes & Radio Controls
- **Checkboxes:** `20px x 20px` square, `4px` radius, border `2px solid #CBD5E1`. Checked state: background `#2563EB`, border `#2563EB` with white checkmark icon.
- **Radio Buttons:** `20px x 20px` circular ring. Selected state: outer border `#2563EB`, filled inner dot `#2563EB` with `4px` white gap.

### Map Markers & Overlays
- Dynamic custom map pins with drop pin profile or rounded pill format displaying price tier or venue name. Active selection switches background to `#2563EB` with a floating quick-view summary card anchored above the pin.