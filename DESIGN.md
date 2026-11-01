---
name: Campus Hire
description: A clear, approachable campus employment portal.
colors:
  accent: "#2ECC5E"
  accent-hover: "#26b150"
  accent-soft: "#DDF3E2"
  surface: "#F5F4F0"
  cream: "#F1EBDC"
  ink: "#161616"
  muted: "#6E6A61"
  line: "#E5E0D4"
  white: "#FFFFFF"
typography:
  body:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
    fontSize: "1.05rem"
    fontWeight: 500
    lineHeight: 1.62
  headline:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
    fontWeight: 800
    lineHeight: 1.18
    letterSpacing: "-0.03em"
rounded:
  card: "24px"
  input: "16px"
  pill: "999px"
  badge: "8px"
components:
  button-primary:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.ink}"
    rounded: "{rounded.pill}"
    height: "52px"
    padding: "0 1.85rem"
  card:
    backgroundColor: "{colors.white}"
    rounded: "{rounded.card}"
    padding: "2rem"
---

# Design System: Campus Hire

## Overview

**Creative North Star: "Campus paper desk"**

Keep the interface clear and approachable. Warm paper surfaces, green actions, rounded controls, and subtle depth frame practical tasks for students and employers.

**Key Characteristics:**
- Warm neutral surfaces with a green accent.
- Readable Inter typography and clear hierarchy.
- Rounded cards and pill-shaped actions.
- A dark theme using corresponding dark surfaces and light text.

## Colors

Use the project's color variables in `assets/css/tokens.css`. Green denotes primary actions and positive states; warm neutrals support the content. Dark mode replaces the same semantic tokens with dark surfaces and lighter text.

## Typography

Inter is the default family. Body text uses a 1.05rem size and 1.62 line height; headings use a heavier weight and tighter tracking. Preserve readable text at mobile sizes.

## Layout

Use the established paper container and responsive Bootstrap grid. On smaller screens, stack content and allow tables such as the availability matrix to scroll horizontally.

## Elevation & Depth

Cards are primarily defined by their surface and border. Existing hover treatments add subtle offset shadows and movement.

## Shapes

Cards use 24px corners on desktop and 14px on small screens. Actions use pill-shaped corners; inputs use 16px corners.

## Components

Primary actions are green pills; secondary actions use outlined pills. Cards use a white surface and a thin line. Form fields, navigation, and status chips follow the shared styles in `assets/css/custom.css` and the theme tokens.

## Do's and Don'ts

### Do:
- **Do** use the established theme variables and check both themes.
- **Do** prioritize clear labels and readable text on mobile.

### Don't:
- **Don't** change the established green and warm-neutral identity without a redesign brief.
