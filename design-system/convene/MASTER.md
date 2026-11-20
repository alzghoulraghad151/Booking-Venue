# Convene design system

Convene’s approved palette defines color. The admin interface uses measurements from the local Kiddos admin stylesheet and components. Start here, then read the override for the page you are changing.

## Palette and semantic roles

| Token | Value | Admin use |
|---|---|---|
| Navy | #172B3A | Navigation, primary text and readable text on Warm |
| Cream | #F5F2EC | Page background, table headers and subtle interactive states |
| Warm | #E5A66A | Primary actions, active navigation marker and dark-surface focus |
| White | #FFFFFF | Cards, inputs and text on the Navy sidebar |
| Sage | #526C68 | Secondary text, active navigation fill, input borders and light-surface focus |

Admin dividers use the existing #DEDBD5 and the existing list hover uses #FAF6EF. Venue status colors stay in the venue view. Badges retain text labels.

The checked text combinations meet 4.5:1: Navy on Cream (13.02:1), Navy on Warm (6.93:1), White on Navy (14.55:1), White on Sage (5.67:1) and Sage on Cream (5.07:1). Warm and White have a 2.10:1 ratio, so Warm buttons use Navy labels.

## Shared admin rules

| Kiddos reference | Measured CSS | Convene implementation |
|---|---|---|
| Shared admin shell and sidebar | 240px desktop rail, 64px brand row, 44px navigation items, 16px × 12px sidebar padding | Shared Blade layout, admin sidebar and logout |
| Management content | 20px top, 20px right, 24px bottom, 16px left. 16px between sections. | Scoped admin content and panel tokens |
| Typography | Nunito 14px/1.5 body, Baloo 2 30px/1.2 page heading, 22px panel heading | Bundled variable fonts, system fallbacks and 28px compact heading |
| Cards | 20px padding, 16px radius, subtle 4px × 16px shadow | White surface, neutral border and navy-tinted shadow. 16px mobile padding. |
| Tables | 48px headers, 12px/700 header text, 60px standard body rows, 14px body text, 16px horizontal cell padding | Shared compact table treatment. Wrapped rows expand. Narrow tables scroll inside focusable regions. |
| Controls | 40px standard button and toolbar field, 42px form fields, 13px labels and 6px label gap | Reused form and toolbar styling. Row actions remain labeled. |
| Pagination | 36px controls, 6px gaps, wrapping footer | Shared Laravel pagination for users and venues |
| Mobile navigation | Drawer below 992px with up to 260px width and 44px trigger | Native modal dialog using the existing sidebar; Escape and focus return; inline navigation fallback without JavaScript |

Keyboard focus uses a 3px outline. Keep native form labels, links, buttons and disclosures. Reduce transitions when reduced motion is enabled. Keep scroll containers local to the content needing overflow.
