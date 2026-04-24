---
name: Academic Precision
colors:
  surface: '#faf8ff'
  surface-dim: '#d9d9e5'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3fe'
  surface-container: '#ededf9'
  surface-container-high: '#e7e7f3'
  surface-container-highest: '#e1e2ed'
  on-surface: '#191b23'
  on-surface-variant: '#434655'
  inverse-surface: '#2e3039'
  inverse-on-surface: '#f0f0fb'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#505f76'
  on-secondary: '#ffffff'
  secondary-container: '#d0e1fb'
  on-secondary-container: '#54647a'
  tertiary: '#3f49b2'
  on-tertiary: '#ffffff'
  tertiary-container: '#5863cc'
  on-tertiary-container: '#f1efff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#d3e4fe'
  secondary-fixed-dim: '#b7c8e1'
  on-secondary-fixed: '#0b1c30'
  on-secondary-fixed-variant: '#38485d'
  tertiary-fixed: '#e0e0ff'
  tertiary-fixed-dim: '#bdc2ff'
  on-tertiary-fixed: '#000767'
  on-tertiary-fixed-variant: '#2f3aa3'
  background: '#faf8ff'
  on-background: '#191b23'
  surface-variant: '#e1e2ed'
typography:
  h1:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  h2:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: -0.01em
  body-base:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: '0'
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
  button:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1'
    letterSpacing: 0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 40px
  container-max: 1024px
  gutter: 24px
---

## Brand & Style

This design system establishes a focused, high-utility environment for academic excellence. The personality is intellectual, organized, and encouraging, aimed at students and educators who require a distraction-free space for intensive study. 

The aesthetic is a hybrid of **Minimalism** and **Modern Corporate**, drawing heavily from the structured clarity of Notion dashboards and the functional accessibility of Google Material Design. The interface prioritizes content hierarchy and task completion through a centered, card-based architecture that creates a sense of "digital paper." The emotional response is one of calm productivity and professional reliability.

## Colors

The palette is anchored by a vibrant Primary Blue to denote action and intelligence, complemented by a Secondary Slate for metadata and structural UI elements. 

- **Primary (#2563EB):** Used for main actions, active states, and focus indicators.
- **Secondary (#64748B):** Used for non-primary typography and utility icons.
- **Background (#F8FAFC):** A cool, neutral base that prevents eye strain.
- **Accent (Soft Indigo):** Leveraged for subtle highlights and progress indicators.
- **Success (Soft Green):** Reserved for "Completed" states and correct answers, utilizing a high-legibility dark green text on a soft green background.

## Typography

This design system utilizes **Inter** for its exceptional legibility and systematic feel. The type scale is tight and functional, favoring clear vertical rhythm over dramatic size shifts. 

Headlines utilize a slightly heavier weight and tighter letter-spacing to command attention, while body text is optimized for long-form reading with a generous 1.6 line height. Label styles are used for metadata, tags, and category headers to provide a clear distinction from primary content.

## Layout & Spacing

The layout philosophy follows a **Fixed Grid** approach for the main content area to maintain a centered, document-like feel. The content is capped at a 1024px container to ensure optimal line lengths for reading and study.

Spacing follows a 4px baseline grid. Elements within cards use `md` (16px) spacing, while major layout sections are separated by `xl` (40px) vertical margins. This generous use of whitespace mimics the "openness" of a clean sheet of paper, reducing cognitive load.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Ambient Shadows**. 

The background is the lowest level. Cards sit on this background with a subtle, multi-layered shadow (0px 1px 3px rgba(0,0,0,0.05), 0px 10px 15px -3px rgba(0,0,0,0.03)). This creates a soft, tactile lift without appearing aggressive. 

Interactions, such as hovering over a card or button, should slightly increase the shadow's spread to provide immediate tactile feedback. Modals and dropdowns use a more pronounced elevation with a soft backdrop blur to maintain context while focusing the user.

## Shapes

The shape language is consistently defined by a **10px (rounded-lg)** corner radius for all primary containers, buttons, and input fields. 

This specific radius strikes a balance between the sharpness of traditional academic software and the friendliness of modern consumer apps. Small elements like checkboxes and tags use a slightly reduced radius (4px) to maintain visual proportions, while the "pill" shape is reserved exclusively for status indicators and badges.

## Components

### Buttons
- **Primary:** Solid #2563EB with white text. 10px rounded corners.
- **Secondary:** Subtle #F1F5F9 background with #64748B text.
- **Ghost:** No background, #64748B text, appearing only on hover.

### Cards
- White background, 10px rounded corners, 1px border (#E2E8F0), and soft ambient shadow. 
- Padding should be uniform at 24px (lg).

### Input Fields
- White background, 1px border (#CBD5E1). 
- On focus: Border changes to #2563EB with a 2px soft indigo outer glow.

### Chips & Tags
- Used for categories (e.g., "Mathematics," "Timed").
- Small text, 4px rounded corners, using the soft indigo accent color with dark indigo text.

### Progress Indicators
- Linear bars with #E2E8F0 tracks and #2563EB fills. 
- Circular "Score" rings for results pages.

### Lists
- Clean rows with subtle 1px bottom dividers.
- Hover states include a light grey (#F1F5F9) background shift.