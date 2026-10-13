---

name: Lentera Nusantara
description: Design system for a modern Indonesian digital learning platform.
version: 1.0.0

colors:
surface: "#faf8ff"
surface-dim: "#d2d9f4"
surface-bright: "#faf8ff"
surface-container-lowest: "#ffffff"
surface-container-low: "#f2f3ff"
surface-container: "#eaedff"
surface-container-high: "#e2e7ff"
surface-container-highest: "#dae2fd"

on-surface: "#131b2e"
on-surface-variant: "#464555"
inverse-surface: "#283044"
inverse-on-surface: "#eef0ff"

outline: "#777587"
outline-variant: "#c7c4d8"
surface-tint: "#4d44e3"

primary: "#3525cd"
on-primary: "#ffffff"
primary-container: "#4f46e5"
on-primary-container: "#dad7ff"
inverse-primary: "#c3c0ff"

secondary: "#855300"
on-secondary: "#ffffff"
secondary-container: "#fea619"
on-secondary-container: "#684000"

tertiary: "#005338"
on-tertiary: "#ffffff"
tertiary-container: "#006e4b"
on-tertiary-container: "#67f4b7"

error: "#ba1a1a"
on-error: "#ffffff"
error-container: "#ffdad6"
on-error-container: "#93000a"

primary-fixed: "#e2dfff"
primary-fixed-dim: "#c3c0ff"
on-primary-fixed: "#0f0069"
on-primary-fixed-variant: "#3323cc"

secondary-fixed: "#ffddb8"
secondary-fixed-dim: "#ffb95f"
on-secondary-fixed: "#2a1700"
on-secondary-fixed-variant: "#653e00"

tertiary-fixed: "#6ffbbe"
tertiary-fixed-dim: "#4edea3"
on-tertiary-fixed: "#002113"
on-tertiary-fixed-variant: "#005236"

background: "#faf8ff"
on-background: "#131b2e"
surface-variant: "#dae2fd"

typography:
font-family: "Plus Jakarta Sans"

display:
fontSize: "36px"
fontWeight: 800
lineHeight: "44px"
letterSpacing: "-0.03em"

headline-lg:
fontSize: "28px"
fontWeight: 700
lineHeight: "36px"
letterSpacing: "-0.02em"

headline-lg-mobile:
fontSize: "24px"
fontWeight: 700
lineHeight: "32px"
letterSpacing: "-0.02em"

headline-md:
fontSize: "20px"
fontWeight: 700
lineHeight: "28px"
letterSpacing: "-0.015em"

headline-sm:
fontSize: "18px"
fontWeight: 600
lineHeight: "26px"
letterSpacing: "-0.01em"

title-md:
fontSize: "16px"
fontWeight: 600
lineHeight: "24px"
letterSpacing: "-0.005em"

body-lg:
fontSize: "16px"
fontWeight: 400
lineHeight: "26px"

body-md:
fontSize: "14px"
fontWeight: 400
lineHeight: "22px"

body-sm:
fontSize: "12px"
fontWeight: 400
lineHeight: "18px"

label-lg:
fontSize: "14px"
fontWeight: 600
lineHeight: "20px"
letterSpacing: "0.01em"

label-md:
fontSize: "12px"
fontWeight: 600
lineHeight: "16px"
letterSpacing: "0.02em"

label-sm:
fontSize: "10px"
fontWeight: 700
lineHeight: "14px"
letterSpacing: "0.04em"

rounded:
sm: "0.25rem"
DEFAULT: "0.5rem"
md: "0.75rem"
lg: "1rem"
xl: "1.5rem"
full: "9999px"

spacing:
gutter: "1rem"
gutter-sm: "0.75rem"
margin: "1rem"
margin-tablet: "2rem"
space-xs: "0.25rem"
space-sm: "0.5rem"
space-md: "1rem"
space-lg: "1.5rem"
space-xl: "2rem"
----------------

# Lentera Nusantara — Design System

## 1. Brand & Style

Lentera Nusantara is a modern digital learning experience designed for ambitious Indonesian students and lifelong learners.

The visual identity combines academic credibility with the simplicity of contemporary consumer applications. It should feel inspiring, focused, approachable, and culturally relevant without overwhelming users with unnecessary visual elements.

### Design philosophy: Modern Tactile Minimalist

The interface follows five core principles:

* **Clarity:** Make information easy to scan and understand.
* **Focus:** Prioritize learning content and meaningful user actions.
* **Consistency:** Reuse design tokens and components across the application.
* **Warmth:** Balance academic professionalism with an approachable visual identity.
* **Purposeful feedback:** Use visual indicators to communicate progress, status, and achievement.

Use generous whitespace, clear typography, layered surfaces, restrained shadows, and deliberate color accents.

Avoid excessive gamification, decorative gradients, unnecessary animation, and inconsistent component styling.

## 2. Color System

The color system is organized into primary, secondary, tertiary, neutral, and semantic roles. Use the color tokens defined in the YAML configuration at the beginning of this document as the source of truth.

### 2.1 Primary — Royal Indigo

* Primary: `#3525CD`
* Primary container: `#4F46E5`
* On primary: `#FFFFFF`
* Primary fixed: `#E2DFFF`
* Primary darker interaction state: `#3323CC`

**Usage:**

* Primary calls to action.
* Active navigation and selected states.
* Important links and interactive controls.
* Course progress and learning milestones.
* Brand identity and key interface highlights.

Use the primary container color for emphasis when appropriate. Do not substitute different indigo shades arbitrarily across components.

### 2.2 Secondary — Warm Amber

* Secondary: `#855300`
* Secondary container: `#FEA619`
* On secondary: `#FFFFFF`
* On secondary container: `#684000`
* Secondary fixed: `#FFDDB8`

**Usage:**

* Academic achievements and certification highlights.
* Course ratings and star icons.
* Premium membership indicators.
* Promotional labels and selected accents.
* Important supporting actions.

Use amber selectively to preserve its visual significance.

### 2.3 Tertiary — Emerald

* Tertiary: `#005338`
* Tertiary container: `#006E4B`
* On tertiary: `#FFFFFF`
* Tertiary fixed: `#6FFBBE`

**Usage:**

* Completed learning modules.
* Successful validation and completion states.
* Positive feedback and progress achievements.

Do not use emerald as a replacement for the primary brand color.

### 2.4 Neutral Surfaces and Text

Use the following tokens for the light interface:

| Role               | Token                       | Value     |
| ------------------ | --------------------------- | --------- |
| Main background    | `background`                | `#FAF8FF` |
| Main surface       | `surface`                   | `#FAF8FF` |
| Lowest surface     | `surface-container-lowest`  | `#FFFFFF` |
| Low container      | `surface-container-low`     | `#F2F3FF` |
| Standard container | `surface-container`         | `#EAEDFF` |
| High container     | `surface-container-high`    | `#E2E7FF` |
| Highest container  | `surface-container-highest` | `#DAE2FD` |
| Primary text       | `on-surface`                | `#131B2E` |
| Supporting text    | `on-surface-variant`        | `#464555` |
| Border             | `outline-variant`           | `#C7C4D8` |
| Outline            | `outline`                   | `#777587` |

Use surface containers to establish hierarchy instead of adding shadows to every element.

### 2.5 Semantic Colors

* **Error:** `#BA1A1A`
* **On error:** `#FFFFFF`
* **Error container:** `#FFDAD6`
* **On error container:** `#93000A`

Use semantic colors consistently for errors, validation feedback, and other relevant system states.

Do not communicate important status using color alone. Combine color with text, icons, or other clear indicators.

### 2.6 Dark Mode

The interface should support a dark appearance if enabled by the application.

Use the following dark-mode reference palette:

| Role                 | Value     |
| -------------------- | --------- |
| Base background      | `#0F172A` |
| Elevated surface     | `#1E293B` |
| Borders and dividers | `#334155` |
| Primary text         | `#F8FAFC` |
| Secondary text       | `#94A3B8` |

These values are dark-mode implementation references. Map them to the appropriate application theme tokens rather than replacing the canonical light-mode YAML values.

Check text contrast, input borders, modal overlays, and interactive states independently in dark mode.

## 3. Typography

### 3.1 Typeface

Use **Plus Jakarta Sans** as the primary typeface throughout the application.

The typeface provides a contemporary, approachable appearance while maintaining readability across learning dashboards, course catalogs, pricing sections, and lesson content.

Ensure the font is loaded using the project's existing asset or font-loading strategy. Provide a suitable sans-serif fallback.

### 3.2 Typography Scale

Use the typography tokens defined in the YAML configuration.

| Token                | Size | Weight | Line height | Primary usage                                 |
| -------------------- | ---: | -----: | ----------: | --------------------------------------------- |
| `display`            | 36px |    800 |        44px | Landing highlights and major progress metrics |
| `headline-lg`        | 28px |    700 |        36px | Major page headings                           |
| `headline-lg-mobile` | 24px |    700 |        32px | Mobile page highlights                        |
| `headline-md`        | 20px |    700 |        28px | Section headings                              |
| `headline-sm`        | 18px |    600 |        26px | Course and card headings                      |
| `title-md`           | 16px |    600 |        24px | Compact titles and important labels           |
| `body-lg`            | 16px |    400 |        26px | Prominent body text                           |
| `body-md`            | 14px |    400 |        22px | Standard body text                            |
| `body-sm`            | 12px |    400 |        18px | Supporting information                        |
| `label-lg`           | 14px |    600 |        20px | Pricing and prominent metadata                |
| `label-md`           | 12px |    600 |        16px | Labels, ratings, and navigation captions      |
| `label-sm`           | 10px |    700 |        14px | Compact badges and secondary metadata         |

### 3.3 Typography Rules

* Use `display` sparingly for prominent landing-page headings and major learning achievements.
* Use `headline-lg-mobile` for appropriate mobile highlights.
* Use `headline-sm` or `title-md` for course titles.
* Limit course-card titles to two lines, with a clear truncation strategy.
* Use `label-lg` or `title-md` for prices and subscription metrics.
* Use `label-md` and `label-sm` for metadata, badges, and lesson duration.
* Avoid long passages in uppercase.
* Maintain sufficient contrast and comfortable line lengths for extended reading.
* Do not use font size alone to communicate hierarchy; combine size, weight, spacing, and color.

## 4. Layout & Spacing

The layout system uses a mobile-first approach with a consistent spacing scale.

### 4.1 Mobile — Below 600px

* Use a single-column layout for primary page content.
* Use approximately 16px outer margins.
* Use 16px gutters between standard content columns.
* Keep controls accessible to touch.
* Avoid horizontal overflow except for intentionally scrollable components.
* Account for fixed navigation and device safe areas.

### 4.2 Tablet and Larger Screens — 600px and Above

* Expand content into balanced multi-column layouts where appropriate.
* Use approximately 24px gutters and 32px outer margins on suitable tablet and desktop layouts.
* Increase content density carefully without compromising readability.
* Use responsive breakpoints according to the existing frontend framework and project conventions.

### 4.3 Horizontal Carousels

Use horizontal carousels for featured courses, recommended mentors, and other genuinely useful collections.

* Allow intentional edge-to-edge scrolling.
* Maintain approximately 16px leading padding for the first visible card.
* Preserve consistent card widths and spacing.
* Provide clear scroll affordances where necessary.
* Ensure keyboard and touch navigation remain usable.
* Avoid clipping important text or interactive elements.

### 4.4 Safe Areas and Fixed Navigation

* Reserve sufficient bottom space for fixed or docked navigation.
* Use a 64px navigation content height, excluding device safe-area padding.
* Maintain at least approximately 72px of content clearance where required by the fixed navigation and surrounding layout.
* Add additional safe-area padding for device home indicators.
* Prevent fixed elements from covering buttons, form fields, or lesson content.

Treat these dimensions as layout references and verify them against actual viewport behavior.

## 5. Elevation & Depth

The interface uses tonal surface layering and restrained ambient shadows rather than strong, persistent drop shadows.

### Level 0 — Canvas

* Light mode: `#FAF8FF`
* Dark-mode reference: `#0F172A`

Use this layer as the foundation of each page.

### Level 1 — Cards and Course Tiles

* Use white or the appropriate surface-container token.
* Apply a subtle border when needed.
* Use a restrained ambient shadow:

```css
box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
border: 1px solid rgba(226, 232, 240, 0.8);
```

Dark-mode reference:

```css
box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35);
border-color: #334155;
```

### Level 2 — Sticky Headers

Use a subtle translucent surface and backdrop blur when the header overlaps scrolling content.

```css
backdrop-filter: blur(12px);
```

* Light overlay reference: `rgba(250, 248, 255, 0.85)`
* Dark overlay reference: `rgba(15, 23, 42, 0.85)`

Provide a bottom divider when needed to preserve separation.

### Level 3 — Bottom Navigation

Use a restrained upward shadow:

```css
box-shadow: 0 -4px 24px rgba(15, 23, 42, 0.06);
```

Ensure the navigation remains visually separate from scrolling content without becoming distracting.

### Level 4 — Modal Sheets and Drawers

Use a stronger shadow and a translucent scrim for overlays.

```css
box-shadow: 0 -12px 32px rgba(15, 23, 42, 0.16);
```

Scrim reference:

```css
background: rgba(15, 23, 42, 0.5);
```

Overlay contrast, focus management, and dismissal behavior must remain accessible.

## 6. Shapes & Border Radius

Use the radius tokens from the YAML configuration.

| Component                          | Recommended radius |
| ---------------------------------- | ------------------ |
| Small controls                     | `4px`              |
| Standard controls                  | `8px`              |
| Medium containers                  | `12px`             |
| Primary buttons and inputs         | `16px`             |
| Course cards                       | `16px–20px`        |
| Modal and bottom-sheet top corners | `24px`             |
| Badges and pills                   | `9999px`           |

Guidelines:

* Use `lg` for standard 16px interactive elements.
* Use `xl` for large cards and containers where appropriate.
* Use `full` for badges, chips, and pill-shaped controls.
* Keep radii consistent within the same component family.
* Avoid excessive rounding that makes the interface feel visually inconsistent.

## 7. Components

### 7.1 Bottom Navigation Bar

The primary mobile navigation contains four destinations:

1. **Beranda**
2. **Kursus**
3. **Langganan**
4. **Profil**

Specifications:

* Fixed to the bottom of the viewport.
* Navigation content height: 64px, excluding device safe-area padding.
* Inactive icons: muted supporting color.
* Active icon and label: primary indigo.
* Caption: `label-md`, adjusted when needed for compact layouts.
* Active destination: visually distinguishable through color, weight, or a small indicator.
* Optional active indicator: centered pill, approximately 4px high.

Behavior:

* Each item must navigate to its intended destination.
* The active state must reflect the current route.
* Respect device safe areas.
* Prevent content from being obscured.
* Provide accessible labels and visible focus states.

### 7.2 Search Header

Create a compact, sticky header when the page benefits from persistent search or navigation.

Elements may include:

* A user greeting or back button.
* A search field or search trigger.
* A filter action.
* An optional filter-state indicator.

Specifications:

* Search input height: approximately 48px.
* Border radius: 16px.
* Light-mode input surface: use an appropriate surface-container token.
* Dark-mode input surface: `#1E293B`.
* Leading search icon.
* Placeholder: "Cari kursus, topik, atau mentor..."
* Filter control with a clear interactive state.

Ensure that the search and filter controls perform real actions and provide appropriate feedback.

### 7.3 Course Cards

#### Vertical Course Card

Recommended width: 240px–280px in horizontal carousels.

Structure:

1. Course thumbnail with a 16:9 aspect ratio.
2. Category badge positioned over the thumbnail.
3. Course level or other relevant metadata.
4. Course title, limited to two lines.
5. Mentor or instructor name.
6. Rating and review count when available.
7. Price or access status.

Specifications:

* Thumbnail top corners: 16px.
* Course title: `headline-sm` or `title-md`.
* Rating star: secondary amber.
* Price: `label-lg` or `title-md`.
* Example price: `Rp249.000`.

Only display real ratings, review counts, and prices supplied by the application. Do not fabricate social proof or transaction data.

#### Horizontal Course Card

Use a full-width layout for dashboards and learning lists.

Structure:

* Thumbnail: approximately 84px × 84px.
* Thumbnail radius: 12px.
* Content aligned beside the thumbnail.
* Course title and supporting metadata.
* Optional progress indicator or current lesson.
* Optional price or enrollment status.

Maintain consistent alignment and prevent text overflow on narrow screens.

### 7.4 Progress Bars

Specifications:

* Track height: 6px.
* Border radius: fully rounded.
* Track: neutral surface or outline color.
* Progress fill: primary indigo.
* Completed state: tertiary emerald.

The indigo gradient may be used as a decorative enhancement if it remains consistent with the design system:

```css
background: linear-gradient(90deg, #4338CA, #4F46E5);
```

Guidelines:

* Progress must represent actual learning data.
* Display a percentage or completion label when it improves understanding.
* Use emerald only when completion is genuinely achieved.
* Do not rely on color alone to communicate progress.

### 7.5 Badges and Category Chips

Specifications:

* Height: approximately 28px–32px.
* Shape: pill.
* Horizontal padding: consistent with the spacing scale.

Category filters:

* Unselected: light indigo surface with indigo text.
* Selected: primary indigo background with white text.
* Keyboard focus: clearly visible.
* Active selection: programmatically identifiable.

Promotional badges:

* Use soft amber backgrounds and readable foreground colors.
* Example background: `#FEF3C7`.
* Example text: `#D97706`.

Do not apply promotional badges to content unless the corresponding status is true.

### 7.6 Buttons

#### Primary Button

* Height: approximately 52px.
* Background: primary indigo.
* Text: white.
* Radius: 16px.
* Weight: semi-bold.
* Provide hover, focus, pressed, disabled, and loading states where relevant.

Optional pressed feedback:

```css
transform: scale(0.98);
```

Use restrained transitions and honor reduced-motion preferences.

#### Secondary Button

* Transparent or surface background.
* Border: approximately 1.5px using the appropriate outline token.
* Text: primary text color.
* Radius: 16px.
* Clear hover and focus states.

#### Button Behavior

* Every interactive button must perform its advertised action.
* Disable repeated submission while an operation is in progress when appropriate.
* Show validation or server errors clearly.
* Require confirmation for destructive or difficult-to-reverse actions.
* Use semantic buttons rather than clickable decorative elements.

### 7.7 Input Fields

Specifications:

* Height: approximately 50px.
* Radius: 16px.
* Background: appropriate surface token.
* Border: visible in both light and dark modes.
* Focus state: primary indigo border, approximately 2px.
* Error state: semantic error color with an associated message.

Guidelines:

* Use persistent labels where possible.
* Do not rely exclusively on placeholder text.
* Display validation messages close to the relevant input.
* Preserve entered values after recoverable validation failures.
* Support keyboard navigation and appropriate input types.

### 7.8 Modal Sheets and Bottom Sheets

Use bottom sheets for focused tasks such as checkout details, course chapters, or filter options when appropriate for the viewport.

Specifications:

* Enter from the bottom on mobile.
* Top corner radius: 24px.
* Drag handle: approximately 36px × 4px.
* Handle top margin: 8px.
* Handle bottom margin: 16px.
* Internal padding: approximately 24px.
* Use a scrim to separate the overlay from the background.

Behavior:

* Provide a clear dismissal method.
* Prevent background interaction when required.
* Manage keyboard focus correctly.
* Support keyboard and assistive technologies.
* Avoid clipping content on short screens.
* Use a full-screen dialog or another responsive pattern when a bottom sheet is unsuitable.

## 8. Interaction & Motion

Motion should clarify changes in state rather than decorate every interaction.

* Use short, consistent transitions.
* Animate loading, selection, expansion, and progress changes only when helpful.
* Avoid excessive bouncing, pulsing, or continuous animation.
* Respect `prefers-reduced-motion`.
* Ensure animation does not delay important actions.
* Preserve visible focus indicators.

Interactive states should include, where applicable:

* Default.
* Hover.
* Focus.
* Pressed.
* Selected.
* Disabled.
* Loading.
* Success.
* Error.

## 9. Accessibility

Accessibility is a core design requirement.

* Maintain readable foreground and background contrast.
* Aim for WCAG 2.2 AA contrast requirements.
* Use semantic HTML elements.
* Provide accessible labels for icon-only buttons.
* Ensure keyboard navigation works throughout the interface.
* Do not communicate status through color alone.
* Provide meaningful alternative text for informative images.
* Mark decorative images appropriately.
* Maintain visible focus states.
* Ensure dialogs and bottom sheets manage focus correctly.
* Respect reduced-motion preferences.
* Keep touch targets comfortably usable.

## 10. Responsive Behavior

Design for mobile first, then adapt the interface to larger screens.

* Avoid horizontal page overflow.
* Keep course cards readable across viewport sizes.
* Switch between single-column and multi-column layouts as appropriate.
* Make dialogs and forms usable on short screens.
* Preserve navigation clarity on all supported devices.
* Do not rely on fixed widths when fluid sizing is more appropriate.
* Verify sticky headers, carousels, and fixed navigation on actual viewport sizes.

The 600px breakpoint is a design reference, not a restriction. Use the breakpoints already configured in the project where possible.

## 11. Implementation Guidelines

When translating this design system into application code:

1. Read this document before creating or modifying UI components.
2. Inspect the existing framework, component structure, and styling configuration.
3. Treat the YAML token block at the beginning of this file as the canonical source for the defined design tokens.
4. Reuse existing design tokens instead of introducing arbitrary colors or spacing values.
5. Keep shared components consistent across pages.
6. Use the existing Tailwind CSS configuration or theme mechanism to expose design tokens.
7. Avoid duplicating token definitions across unrelated components.
8. Preserve responsive behavior, accessibility, and loading/error states.
9. Do not introduce a new UI library without a clear reason.
10. Verify the rendered interface at mobile and desktop sizes.

If a token is missing, introduce it only when necessary and document its purpose. Do not silently change existing token values to match a single screen.

## 12. Design Quality Checklist

Before considering a UI task complete, verify the following:

* [ ] The design follows the Lentera Nusantara visual identity.
* [ ] Colors use the defined design tokens.
* [ ] Plus Jakarta Sans is applied consistently.
* [ ] Typography follows the documented hierarchy.
* [ ] Spacing and border radii are consistent.
* [ ] Cards, buttons, and inputs have appropriate interaction states.
* [ ] Layout works on mobile, tablet, and desktop.
* [ ] Fixed navigation does not cover page content.
* [ ] Search, filters, buttons, and forms perform their intended actions.
* [ ] Loading, empty, success, and error states are handled where applicable.
* [ ] Accessibility and keyboard navigation have been considered.
* [ ] No fabricated course prices, ratings, or progress values are presented as real data.
* [ ] Existing application functionality remains intact.
* [ ] Relevant tests and available quality checks have been completed.

---

**Final principle:** Every interface should feel clear, trustworthy, purposeful, and welcoming. The design system exists to support learning—not to compete with it.

Use indigo to establish focus, amber to highlight meaningful achievements, emerald to communicate successful progress, and the surface system to preserve readability. Apply these principles consistently across the entire Lentera Nusantara experience.
