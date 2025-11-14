# CollabFlow Design System & Requirements for Laravel 12 Implementation

## Project Overview

**CollabFlow** is an adaptive human-AI workflow system that helps users create projects, generate tasks using AI, and intelligently distribute work between humans and AI agents. The design philosophy follows **"Notion meets Claude"** - clean, spacious, intelligent, and conversational.

---

## 🎨 Complete Design System

### Color Palette

```css
/* Primary Brand Colors */
--bittersweet: #EB5E55       /* Primary CTAs, important actions */
--eggplant: #512D38          /* Dark headers, navigation, footer */
--glaucous: #5C80BC          /* AI features, links, intelligence */
--tea-green: #C4D6B0         /* Success, human tasks, completion */
--orange-peel: #FF9F1C       /* HITL markers, warnings, attention */

/* Light Theme (Default) */
:root[data-theme="light"] {
  /* Text */
  --text-500: #a45b71;       /* Primary body text */
  --text-600: #84485a;       /* Secondary text */
  --text-700: #633644;       /* Headings */
  --text-800: #42242d;       /* Strong emphasis */
  
  /* Backgrounds */
  --background-50: #eef7ef;  /* Lightest bg */
  --background-100: #dcefde; /* Card backgrounds */
  --background-200: #badebe; /* Subtle elements */
  --background-300: #97ce9d; /* Borders, dividers */
  
  /* Primary (Bittersweet variations) */
  --primary-500: #e4281b;    /* Base primary */
  --primary-600: #b72015;    /* Hover state */
  
  /* Accent (Glaucous variations) */
  --accent-50: #edf1f8;      /* Lightest accent bg */
  --accent-100: #dbe3f0;     /* Light accent bg */
  --accent-500: #4a71b5;     /* Base accent */
  --accent-600: #3b5b91;     /* Hover state */
}

/* Semantic Color Mapping */
--color-ai: #5C80BC;          /* AI tasks and features */
--color-human: #C4D6B0;       /* Human tasks */
--color-hitl: #FF9F1C;        /* Human-in-the-loop checkpoints */
--color-success: #C4D6B0;     /* Success states */
--color-error: #EB5E55;       /* Errors */
--color-warning: #FF9F1C;     /* Warnings */
--color-info: #5C80BC;        /* Information */
```

### Typography

```css
/* Fonts */
@import url('https://fonts.googleapis.com/css?family=Tahoma:700|Montserrat:400');

--font-heading: 'Tahoma', sans-serif;      /* Bold, strong headings */
--font-body: 'Montserrat', sans-serif;     /* Clean, readable body */
--font-mono: 'JetBrains Mono', monospace;  /* Code/technical */

/* Type Scale */
h1: 4.210rem (67.36px)  - Tahoma Bold
h2: 3.158rem (50.56px)  - Tahoma Bold
h3: 2.369rem (37.92px)  - Tahoma Bold
h4: 1.777rem (28.48px)  - Tahoma Bold
h5: 1.333rem (21.28px)  - Tahoma Bold
body: 1rem (16px)       - Montserrat Regular
small: 0.750rem (12px)  - Montserrat Regular
```

### Spacing System (Notion-inspired)

```css
--space-xs: 4px;
--space-sm: 8px;
--space-md: 16px;
--space-lg: 24px;
--space-xl: 32px;
--space-2xl: 48px;
--space-3xl: 64px;
--space-4xl: 96px;

/* Page Padding */
--page-padding-x: 64px;  /* Horizontal */
--page-padding-y: 48px;  /* Vertical */
```

### Border Radius

```css
--radius-sm: 6px;     /* Small elements */
--radius-md: 8px;     /* Cards, inputs */
--radius-lg: 12px;    /* Large cards */
--radius-xl: 16px;    /* Hero sections */
--radius-full: 9999px; /* Pills, avatars */
```

### Shadows (Subtle, Claude-inspired)

```css
--shadow-sm: 0 1px 2px 0 rgba(81, 45, 56, 0.05);
--shadow-md: 0 4px 6px -1px rgba(81, 45, 56, 0.1);
--shadow-lg: 0 10px 15px -3px rgba(81, 45, 56, 0.1);
--shadow-xl: 0 20px 25px -5px rgba(81, 45, 56, 0.1);
```

---

## 🎯 Component Specifications

### Buttons

**Primary Button**
```
Background: #EB5E55 (Bittersweet)
Text: White
Padding: 10px 20px
Border-radius: 8px
Font: Montserrat, 14px, Medium (500)
Hover: Background #d64f47, Transform translateY(-2px)
Shadow: shadow-sm on default, shadow-primary on hover
Transition: all 0.2s
```

**Secondary Button**
```
Background: White
Text: #633644
Border: 1px solid #97ce9d
Padding: 10px 20px
Border-radius: 8px
Hover: Background #dcefde, Border #5C80BC
```

**Ghost Button**
```
Background: Transparent
Text: #5C80BC
Padding: 10px 20px
Border-radius: 8px
Hover: Background #edf1f8
```

### Form Elements

**Input Field**
```
Width: 100%
Padding: 12px 16px
Border: 1px solid #97ce9d
Border-radius: 8px
Font: Montserrat, 16px
Background: White
Focus: Border #5C80BC, Ring 3px #edf1f8
```

**Textarea**
```
Min-height: 120px
Padding: 12px 16px
Border: 1px solid #97ce9d
Border-radius: 8px
Resize: vertical
```

**Select/Dropdown**
```
Padding: 12px 16px
Border: 1px solid #97ce9d
Border-radius: 8px
Background: White with chevron icon
```

### Cards

**Standard Card**
```
Background: White
Border: 1px solid #97ce9d
Border-radius: 12px
Padding: 24px
Shadow: shadow-sm
Hover: shadow-md, Transform translateY(-2px)
Transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)
```

**Project Card**
```
Same as standard card, plus:
- Header: Icon (40x40px rounded) + Title (h5) + Status badge
- Progress bar: 8px height, rounded-full
- Meta row: Domain badge, task count, updated time
- Footer: Avatar stack + action menu
```

### Badges

**Status Badge**
```
Padding: 4px 12px
Border-radius: 9999px (full)
Font: Montserrat, 12px, Medium
Variants:
- Draft: bg #badebe, text #633644
- Active: bg #C4D6B0 opacity-30, text #316837
- Completed: bg #dbe3f0, text #2c446d
- Archived: bg #badebe, text #84485a
```

**Type Badges (AI/Human/HITL)**
```
Padding: 4px 8px
Border-radius: 6px
Font: 11px, Medium
- AI: bg #edf1f8, text #2c446d, icon 🤖
- Human: bg #C4D6B0 opacity-30, text #316837, icon 👤
- HITL: bg #fff4e5, text #663c00, icon ⚠️
```

---

## 📐 Layout Specifications

### Global Layout

**Header (Fixed)**
```
Height: 56px (not 64px - slightly shorter, Notion-style)
Background: White
Border-bottom: 1px solid #97ce9d
Position: Fixed, top: 0
Z-index: 50

Layout:
- Left: Sidebar toggle + Breadcrumbs
- Center: Search bar (when relevant)
- Right: Theme toggle, Notifications, User menu
```

**Sidebar**
```
Width: 280px (expanded), 60px (collapsed)
Background: #eef7ef
Border-right: 1px solid #97ce9d
Position: Fixed (desktop), Bottom nav (mobile)

Sections:
1. Logo + Brand (top)
2. Main navigation
3. Quick actions (New Project button)
4. Bottom section (Settings, Help)
```

**Main Content**
```
Padding: 48px 64px (desktop)
Background: #eef7ef
Min-height: calc(100vh - 56px)
Max-width: 1400px (centered)
Margin: 0 auto
```

### Responsive Breakpoints

```css
/* Mobile */
@media (max-width: 640px) {
  --page-padding-x: 16px;
  --page-padding-y: 16px;
  /* Sidebar becomes bottom tab bar */
  /* Single column grids */
}

/* Tablet */
@media (min-width: 641px) and (max-width: 1023px) {
  /* 2-column grids */
  /* Collapsible sidebar */
}

/* Desktop */
@media (min-width: 1024px) {
  /* 3-column grids */
  /* Full sidebar */
}
```

---

## 🎭 UI States & Interactions

### Loading States

**Skeleton Loading**
```css
background: linear-gradient(
  90deg,
  #dcefde 0%,
  #97ce9d 50%,
  #dcefde 100%
);
background-size: 200% 100%;
animation: skeleton-loading 1.5s ease-in-out infinite;
border-radius: 8px;
```

**CF Logo Loading**
```
Size: 96x96px
Background: #512D38 (Eggplant)
Text: "CF" white, Tahoma Bold
Outer ring: 3px, #EB5E55, rotating animation
Animation: 1.5s ease-in-out infinite
```

**Button Loading**
```
Button becomes disabled
Text becomes transparent
Spinner appears: 16x16px, 2px border
Animation: 0.6s linear infinite rotation
```

### Hover Effects

**Cards**
```css
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: #5C80BC;
}
```

**Buttons**
```css
transition: all 0.2s;
hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-primary);
}
```

### Animations

**Page Transitions**
```css
fade-in: opacity 0 to 1, 0.3s ease-out
slide-in: translateY(-10px) to 0, 0.3s ease-out
```

**Microinteractions**
```css
checkbox-check: scale 0 to 1, 0.2s spring
badge-pulse: box-shadow pulse, 2s infinite
task-complete: background fade + scale, 0.6s ease
```

---

## 📱 Page Structure & Requirements

### 1. Dashboard

**Layout:**
```
Header: "Dashboard" (h1) + Quick actions (Filter, New Project)
Stats Cards Grid: 4 columns (responsive to 2, then 1)
Recent Projects: Grid of 3 columns (responsive)
Empty State: Centered with CF logo outline
```

**Stats Cards:**
- 4 cards: Total Projects, Active Tasks, AI Tasks, HITL Points
- Each card: Icon (48x48 circle), Value (h3), Label (small)
- Icons colored per type (see color mapping)

**Project Cards:**
- Icon initial, Project name (h5), Status badge
- Progress bar with percentage
- Domain badge, task count, updated time
- Click to navigate to project detail

### 2. Projects List

**Toolbar:**
```
Background: #dcefde
Border: 1px solid #97ce9d
Padding: 16px
Border-radius: 12px
Contains: Search, Filters, View toggle (Grid/List)
```

**Grid View:**
- 3 columns (desktop), 2 (tablet), 1 (mobile)
- Gap: 24px
- Project cards with hover effects

### 3. Create Project (Multi-step Wizard)

**5 Steps:**
1. Project Details (Name, Description, Domain, Timeline)
2. Goals & Success Metrics (Repeatable goals, KPIs)
3. Team & Resources (Owner, Team members)
4. AI Task Generation (Show AI process, trigger generation)
5. Review Generated Tasks (Task list with edit/delete)

**Stepper:**
```
Horizontal steps with circles and connectors
Active: bg #5C80BC, text white
Completed: bg #C4D6B0, checkmark icon
Pending: bg white, border #97ce9d
Connector: 2px line, #dcefde (pending) or #C4D6B0 (completed)
```

**AI Generation State:**
```
CF logo with rotating ring (3s animation)
Progress steps showing:
- Analyzing context (completed, green check)
- Generating tasks (active, blue spinner)
- Calculating distribution (pending, grey)
- Estimating timeline (pending, grey)
```

### 4. Project Detail

**Tabs:**
- Overview (Description, Goals)
- Tasks (Kanban or List view)
- Flowchart (Visual task dependencies)
- Schedule (Timeline view)

**Flowchart Tab:**
```
SVG-based node graph
Nodes: Rounded rectangles (250x80px)
Colors: Human (Tea Green), AI (Glaucous), HITL (Orange Peel)
Arrows: Curved lines with arrowheads showing dependencies
Start/End nodes: Small circles
Legend showing color coding
Critical path analysis callout below
```

---

## 🎨 Design Principles

### 1. Generous Whitespace (Notion)
- Breathing room between elements
- Comfortable reading experience
- Don't crowd components

### 2. Subtle Interactions (Claude)
- Smooth 200-300ms transitions
- Gentle hover effects
- Micro-animations that delight

### 3. Intelligent Color Usage
- Glaucous (#5C80BC) = AI features
- Tea Green (#C4D6B0) = Human tasks/success
- Orange Peel (#FF9F1C) = Attention/HITL
- Bittersweet (#EB5E55) = Primary actions

### 4. Consistent Patterns
- Reuse components extensively
- Predictable interactions
- Familiar layouts

### 5. Accessible by Default
- WCAG AA contrast ratios minimum
- Keyboard navigation support
- Focus indicators on all interactive elements
- Screen reader friendly markup

---

## 🔧 Technical Requirements

### Icons
**Library:** Lucide Icons (https://lucide.dev)
**Usage:** Import as blade components or use via CDN
**Common Icons:**
- home, folder, check-square, calendar, bar-chart-2 (navigation)
- sparkles (AI), brain (intelligence), users (team)
- alert-circle (HITL), clock (pending), check-circle-2 (completed)
- plus, edit-2, trash-2, more-vertical (actions)

### Fonts
**Load from Google Fonts:**
```html
<link href="https://fonts.googleapis.com/css?family=Tahoma:700|Montserrat:400" rel="stylesheet">
```

### CSS Architecture
- Use Tailwind CSS for utilities
- Custom CSS for complex components
- CSS variables for theme colors
- Component-scoped styles in Livewire components

### JavaScript
- Minimal vanilla JS for interactions
- Alpine.js (included with Livewire) for simple reactivity
- No additional frameworks needed

---

## ✅ Quality Checklist

Before considering implementation complete, verify:

- [ ] All colors match the design system exactly
- [ ] Typography uses Tahoma for headings, Montserrat for body
- [ ] Spacing follows the defined scale (4px increments)
- [ ] Border radius is consistent (6px, 8px, 12px)
- [ ] Shadows are subtle and match specifications
- [ ] Hover effects are smooth (0.2-0.3s transitions)
- [ ] Loading states are implemented for async actions
- [ ] Empty states are friendly and actionable
- [ ] Error states are clear and helpful
- [ ] Success feedback is immediate and satisfying
- [ ] Mobile responsive (sidebar becomes bottom nav)
- [ ] Keyboard navigation works throughout
- [ ] Focus indicators are visible
- [ ] Color contrast meets WCAG AA standards

---

## 📝 Notes for Implementation

1. **Start with the layout**: Get header, sidebar, and main content structure right first
2. **Component library first**: Build reusable button, card, badge components
3. **Page by page**: Implement Dashboard → Projects List → Create Project → Project Detail
4. **Test responsiveness**: Verify mobile, tablet, desktop at each stage
5. **Accessibility pass**: Add ARIA labels, keyboard support, focus management
6. **Polish pass**: Animations, hover effects, loading states last

**Remember:** The design should feel like Notion (spacious, clean) meets Claude (conversational, intelligent). Prioritize usability and clarity over cleverness.