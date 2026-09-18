# Antigravity Global Rule — UI/UX Design System

**Scope:** Applies to every project Antigravity starts or touches (web app, dashboard, admin panel, landing page, mobile-responsive site). Place this in `.antigravity/rules/ui-design-system.md` (or your global rules folder) so it loads automatically on every new project.

---

## 0. Prime Directive

> Never ship a generic website or dashboard. Every project must look **intentionally designed** for its domain — not a reskinned Bootstrap/shadcn/AdminLTE template with swapped text.

Before writing a single line of UI code, Antigravity must complete **Step 1 (Discovery)** and lock the **Design Tokens** for that project. All components must consume those tokens — no hardcoded hex codes, font names, or magic spacing values inside components.

---

## 1. Project Discovery (mandatory, runs once per project at kickoff)

1. **Identify the domain & audience** — e.g. NGO/blood-bank platform, exam management system, e-commerce store, real estate SaaS, voter/civic app, LinkedIn content tool. Domain drives mood.
2. **Pick a visual mood** (choose one, state it explicitly in the token file):
   - Corporate / Trustworthy (finance, legal, gov, NGO)
   - Bold / Techy (SaaS, dev tools, exam/data systems)
   - Elegant / Editorial (real estate, luxury, portfolios)
   - Playful / Friendly (consumer apps, social tools)
   - Minimal / Clinical (health, admin-heavy tools)
3. **Derive the palette from the mood + domain**, not from defaults:
   - 1 primary brand color, 1 secondary/accent, full neutral scale (9–11 steps), semantic colors (success, warning, danger, info) that harmonize with the brand hue — not raw Tailwind red-500/green-500 out of the box.
4. **Pick typography with intent**:
   - 1 display/heading font (personality) + 1 body font (readability). Pair a serif/distinctive display face with a clean sans body font, or a geometric sans pair — avoid defaulting to system-ui/Inter everywhere unless the mood is deliberately "minimal/neutral."
   - Define a type scale (e.g. 12/14/16/18/20/24/30/36/48) and consistent weights (400/500/600/700).
5. **Define motion personality**: snappy & functional (SaaS/admin) vs. soft & elegant (real estate/NGO) vs. energetic (consumer). Set standard durations (150ms micro, 250–300ms standard, 400ms+ modals) and one consistent easing curve (e.g. `cubic-bezier(0.16,1,0.3,1)`).
6. **Write it down once** as design tokens (Tailwind config `theme.extend`, CSS custom properties, or `design-tokens.json`) — this is the single source of truth every component pulls from.

---

## 2. Non-Negotiable Global Requirements

Every screen, in every project, must satisfy:

| Area | Requirement |
|---|---|
| Color System | Tokenized palette (brand, neutral, semantic) with dark & light variants for each token — never raw hex in components |
| Typography | Locked type scale + font pairing applied consistently; no ad-hoc font sizes |
| Layout | Responsive grid (mobile-first, breakpoints for sm/md/lg/xl/2xl), consistent gutters/spacing on a 4px or 8px base scale |
| Navbar/Sidebar | Consistent height/width, consistent icon set, consistent active/hover states |
| Cards & Containers | Consistent radius, elevation/shadow scale, padding scale — one card style reused everywhere, not five variants |
| Buttons | Defined variants (primary, secondary, ghost, destructive, icon) each with default/hover/active/disabled/loading/focus-visible states |
| Form Inputs | Consistent height, border, focus ring, error state, helper text, label style across every form in the app |
| Icons & Spacing | One icon library only (e.g. lucide), consistent icon sizing (16/20/24), consistent icon-to-label gap |
| Loading & Empty States | Every list, table, and chart has a skeleton/loading state AND a designed empty state (icon + message + CTA) — never a blank screen or bare spinner |
| Dark/Light Mode | Full parity — every token has both variants; no mode should ever look like an unfinished afterthought |

---

## 3. Component Rules

### Sidebar / Navigation Drawer
- Logo + app name pinned at top
- User profile card (avatar, name, role/plan) directly under the logo
- Nav items grouped under section labels (e.g. "Overview," "Management," "Reports") — never one long flat list
- Icon + label for every item, consistent icon weight/size
- Clear active-state indicator (accent left border, pill background, or bold + accent icon color — pick one pattern and use it everywhere)
- Badges/counters on items with pending counts (e.g. notifications, unread, pending approvals)
- Collapse/expand toggle that shrinks to icon-only rail (tooltips on hover when collapsed)
- Logout/Settings anchored at the bottom, visually separated from nav groups

### Topbar / Header
- Page title / context on the left, in sync with the active sidebar item
- Global search (when relevant to the app)
- Notification bell with unread badge
- Theme toggle (dark/light)
- User menu (avatar → dropdown: profile, settings, logout)
- Sticky on scroll, subtle shadow/border on scroll only (not always-on heavy shadow)

### Breadcrumbs
- Always reflect real hierarchy (Dashboard / Section / Current Page)
- Current page not clickable, styled as muted/bold; ancestors are links
- Truncate gracefully on mobile (show only parent + current, or a "…" collapse)

### Stat Cards / KPI Widgets
- Label, big number, trend indicator (↑/↓ with color + %), optional mini sparkline
- Icon in a tinted badge (not a giant literal icon dominating the card)
- Consistent grid (2/3/4-up depending on breakpoint), equal height

### Data Tables
- Sticky header, zebra or hover row highlight (pick one, stay consistent)
- Sortable columns with a visible sort indicator
- Row actions in a consistent overflow/kebab menu or inline icon buttons
- Pagination or infinite scroll — never both patterns in the same app
- Column-level filters where relevant; empty and loading (skeleton rows) states required

### Charts & Graphs
- One charting style per data type (e.g. always area for trends, always horizontal bar for rankings)
- Colors pulled from the semantic/brand token palette, not chart-library defaults
- Legible legends, tooltips styled to match the design system (not the library's default white box)

### Forms & Filters
- Logical grouping/sectioning for long forms, not one giant column
- Inline validation with clear error text under the field, not just a red border
- Filter bars: chips for active filters with a one-click "clear all"
- Consistent primary/secondary button placement (primary right-aligned, cancel/reset to its left)

### Modals / Dialogs
- Consistent max-width tiers (sm/md/lg) chosen by content, not ad hoc
- Header (title + close), scrollable body, sticky footer with actions
- Backdrop blur/dim consistent across the app; entrance/exit animation matches the motion tokens
- Destructive actions get a confirmation step and a visually distinct (danger-colored) confirm button

### Badges & Status Tags
- Fixed set of status-to-color mappings defined once (e.g. Pending=amber, Approved=green, Rejected=red, Draft=neutral) and reused everywhere — never re-invented per screen
- Consistent shape (pill) and size across the app

---

## 4. Consistent UI Checklist (run before considering any screen "done")

- [ ] Uses only tokens from the design-tokens file — zero hardcoded colors/fonts/spacing
- [ ] Sidebar, topbar, cards, buttons, inputs, icons match every other screen in the app
- [ ] Responsive at mobile / tablet / desktop — sidebar collapses to a drawer on mobile, tables become scrollable or card-based
- [ ] Every interactive element has hover, active, focus-visible, and disabled states
- [ ] Loading skeleton + empty state designed for every data view
- [ ] Dark mode checked side-by-side with light mode — no washed-out or unreadable states
- [ ] Motion/transitions use the project's defined duration + easing tokens, not default browser transitions
- [ ] Nothing on the screen looks like an untouched component-library default

---

## 5. Anti-Patterns (auto-reject if seen)

- Reusing the same generic admin-template look across unrelated projects
- Default Tailwind/shadcn gray-500 + blue-600 palette with no customization
- Inconsistent spacing (px values that don't belong to the spacing scale)
- Icon buttons or nav items with no hover/active feedback
- Tables/lists with no loading or empty state
- Dark mode that's just "invert the light theme colors" with no dedicated tuning
- Mixing two different icon libraries or two different sidebar active-state patterns in one app

---

### How to use this rule
At the start of every new Antigravity project, run **Section 1 (Discovery)** first, produce the token file, then generate components strictly from **Sections 2–4**. Re-check every generated screen against the **Section 4 checklist** before marking it complete.
