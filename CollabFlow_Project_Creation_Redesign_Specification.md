# CollabFlow Project Creation Feature Redesign
## Design Document & Implementation Specification

**Document Version:** 1.0  
**Date:** October 26, 2025  
**Project:** CollabFlow React Demo Application  
**Feature:** Multi-Step Project Creation Wizard - Step 2 Redesign  

---

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Design Philosophy & Requirements](#design-philosophy--requirements)
3. [User Experience Goals](#user-experience-goals)
4. [Existing Design System Constraints](#existing-design-system-constraints)
5. [Detailed Component Specifications](#detailed-component-specifications)
6. [Implementation Guidelines](#implementation-guidelines)
7. [Testing & Validation Criteria](#testing--validation-criteria)
8. [Appendix: Code Snippets](#appendix-code-snippets)

---

## 1. Executive Summary

### Problem Statement
The current Step 2 ("Goals & KPIs") in the project creation wizard creates unnecessary friction by:
- Requiring users to think abstractly about KPIs before experiencing the AI's value
- Using intimidating terminology ("KPIs") that may confuse non-technical users
- Combining two distinct cognitive tasks (defining goals + quantifying metrics) into one step

### Proposed Solution
Redesign Step 2 to prioritize user-friendly goal description with **progressive disclosure** of advanced KPI options:
- **Primary Interface**: Simple, freeform textarea for goal description
- **Secondary Interface**: Collapsible "Advanced Options" section for optional KPI definition
- **AI Enhancement**: System suggests KPIs in Step 3 based on user's goal description

### Expected Impact
- **Reduced friction**: Faster completion of Step 2
- **Lower abandonment**: Fewer users dropping out before experiencing AI value
- **Better data quality**: Natural language goals provide richer context for AI
- **Maintained flexibility**: Power users can still define detailed metrics upfront

---

## 2. Design Philosophy & Requirements

### 2.1 Human-Centered Design Principles

#### Empathy
- **User Mental Model**: Users think in outcomes ("I want 1000 users") not metrics ("User Acquisition KPI: Target=1000, CAC=$50")
- **Cognitive Load**: Minimize decision fatigue by asking for one thing at a time
- **Natural Language**: Allow users to express goals conversationally

#### Progressive Disclosure
- **Essential First**: Show only what's required for minimum viable input
- **Advanced Optional**: Hide complexity behind discoverable controls
- **Contextual Help**: Provide inline guidance without overwhelming

#### Trust & Transparency
- **AI Collaboration**: Set clear expectations that AI will help structure goals
- **Flexibility**: Allow skipping advanced features without penalty
- **Editability**: Communicate that everything can be refined later

### 2.2 Design Science Validation

#### Measurable Hypotheses
1. **H1**: Step 2 completion time will decrease by >30%
2. **H2**: Step 2 abandonment rate will decrease by >40%
3. **H3**: Overall wizard completion rate will increase by >25%
4. **H4**: 70%+ of users will skip advanced KPI section

#### Success Metrics
- Average time on Step 2: Target <60 seconds (down from ~120s)
- Field completion rate: >95% for goals, <30% for KPIs
- User satisfaction score: >4.0/5.0 for ease of use

---

## 3. User Experience Goals

### 3.1 Primary User Journey

```
User Flow:
┌─────────────────┐
│ Step 1: Details │
│  (Completed)    │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│ Step 2: What You Want to Achieve?      │
│                                         │
│ [User types natural language goals]    │
│ "Create a fitness app with 1000 users  │
│  in 3 months and social features..."   │
│                                         │
│ ✨ AI will help structure this         │
│                                         │
│ [Show advanced options] (collapsed)    │
└────────┬────────────────────────────────┘
         │
         ▼ [User clicks Continue]
         │
┌─────────────────────────────────────────┐
│ Step 3: AI Task Generation             │
│                                         │
│ AI extracts:                            │
│ ✓ Goals: [List of structured goals]    │
│ ✓ Suggested KPIs: [Relevant metrics]   │
│ ✓ Tasks: [Breakdown of work]           │
└─────────────────────────────────────────┘
```

### 3.2 Alternative User Journey (Power Users)

```
User Flow:
┌─────────────────┐
│ Step 2: Goals   │
│                 │
│ [User types]    │
│                 │
│ [Click "Show    │
│  advanced       │
│  options"]      │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│ Advanced Section Expands               │
│                                         │
│ Success Metrics (Optional)              │
│ ┌─────────────────────────────────┐   │
│ │ Metric    Current    Target     │   │
│ │ [Users]   [0]        [1000]     │   │
│ │ [Revenue] [$0]       [$50k]     │   │
│ └─────────────────────────────────┘   │
│                                         │
│ + Add another metric                    │
└─────────────────────────────────────────┘
```

---

## 4. Existing Design System Constraints

### 4.1 Theme System (CRITICAL - MUST ADHERE)

**Color Palette** (from `globals.css`)
```css
/* DO NOT DEVIATE FROM THESE COLORS */

/* Primary Colors */
--glaucous: #5C80BC;          /* AI features, primary actions */
--tea-green: #C4D6B0;         /* Success, human tasks, completion */
--orange-peel: #FF9F1C;       /* Attention, HITL tasks, warnings */
--bittersweet: #EB5E55;       /* Errors, danger actions, deletion */

/* Background Colors */
--background-50: #FAFBFC;     /* Lightest background */
--background-100: #F5F7F9;    /* Card backgrounds (light) */
--background-200: #E8EDF2;    /* Borders, dividers (light) */
--background-300: #D1DBE5;    /* Subtle borders (light) */

/* Dark Mode */
--background-900: #0F1419;    /* Base background (dark) */
--background-800: #1A1F26;    /* Card backgrounds (dark) */
--background-700: #2A3038;    /* Borders (dark) */

/* Text Colors */
--text-primary: #1A1F26;      /* Main text (light mode) */
--text-secondary: #4A5568;    /* Muted text (light mode) */
--text-primary-dark: #F5F7F9; /* Main text (dark mode) */
--text-secondary-dark: #A0AEC0; /* Muted text (dark mode) */
```

**Typography** (from design system)
```css
/* MUST USE THESE FONTS */
--font-heading: 'Tahoma', sans-serif;
--font-body: 'Montserrat', sans-serif;

/* Font Sizes */
h2.step-title: 24px, font-weight: 700, font-family: Tahoma
p.step-description: 14px, font-weight: 400, font-family: Montserrat
label.form-label: 14px, font-weight: 600, font-family: Montserrat
input/textarea: 14px, font-weight: 400, font-family: Montserrat
```

**Spacing** (Tailwind)
```
Section padding: p-6 (24px)
Element spacing: space-y-4 (16px between elements)
Card padding: p-4 or p-6
Gap between elements: gap-2 (8px), gap-3 (12px), gap-4 (16px)
```

**Border Radius**
```
Buttons: rounded-lg (8px)
Cards: rounded-xl (12px)
Inputs: rounded-md (6px)
Pills/Badges: rounded-full
```

**Shadows**
```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
--shadow-accent: 0 0 0 3px rgba(92, 128, 188, 0.1); /* Glaucous glow */
```

### 4.2 Component Patterns (Existing)

**Button Styles** (MUST USE)
```tsx
// Primary Button (main actions)
className="btn-primary"
// CSS: bg-bittersweet, hover:bg-bittersweet/90, text-white, px-6 py-2.5, rounded-lg

// Secondary Button (back, cancel)
className="btn-secondary"
// CSS: bg-background-100, hover:bg-background-200, text-text-primary, border

// Text Button (show/hide advanced)
className="text-glaucous hover:text-glaucous/80 transition-colors"
```

**Form Elements** (MUST USE)
```tsx
// Input Field
className="input-field"
// CSS: border border-background-300, rounded-md, px-3 py-2, focus:ring-glaucous

// Textarea
className="textarea-field"
// CSS: Same as input but min-h-[120px]

// Label
className="form-label"
// CSS: text-sm font-semibold text-text-primary mb-2
```

**Info Callouts** (MUST USE)
```tsx
// AI Hint/Info Box
className="flex items-start gap-2 text-sm text-muted bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg"
// Icon: Sparkles from lucide-react in text-glaucous
```

### 4.3 Animation Standards

**Transitions**
```css
/* All interactive elements */
transition: all 0.2s ease-in-out;

/* Hover states */
hover:scale-105 /* Subtle scale on buttons */
hover:bg-opacity-90 /* Color intensity */

/* Collapsible sections */
transition: max-height 0.3s ease-in-out;
```

**Icons** (Lucide React)
```tsx
// Standard icon size: w-4 h-4 (16px)
// Large icons: w-5 h-5 (20px)
// Icon in button: mr-2 (margin-right: 8px)

import { 
  ArrowRight, 
  ArrowLeft, 
  Sparkles, 
  ChevronRight,
  X,
  Plus
} from 'lucide-react';
```

---

## 5. Detailed Component Specifications

### 5.1 Component: GoalsStep (Revised)

**File Location**: `components/create-project/steps/goals-step.tsx`

**Component Hierarchy**
```
GoalsStep
├── StepHeader (h2 + description)
├── MainGoalsSection
│   ├── Label + Helper Text
│   ├── Textarea (goals description)
│   └── AIHintCallout
├── AdvancedSection (collapsible)
│   ├── ToggleButton
│   └── CollapsibleContent (when expanded)
│       ├── KPIInputSection
│       │   ├── Label + Optional Badge
│       │   ├── Description Text
│       │   ├── KPIInputList
│       │   └── AddMetricButton
│       └── TipCallout
└── NavigationButtons
    ├── BackButton (secondary)
    └── ContinueButton (primary)
```

**Props Interface**
```typescript
interface GoalsStepProps {
  formData: {
    goalsDescription: string;
    kpis?: KPI[];
  };
  updateFormData: (data: Partial<FormData>) => void;
  onNext: () => void;
  onBack: () => void;
}

interface KPI {
  id: string;
  name: string;
  current: string;
  target: string;
  unit?: string;
}
```

**State Management**
```typescript
const [showAdvanced, setShowAdvanced] = useState(false);
const [localKPIs, setLocalKPIs] = useState<KPI[]>(formData.kpis || []);
```

### 5.2 Component: KPIInputList

**File Location**: `components/create-project/kpi-input-list.tsx`

**Component Structure**
```
KPIInputList
├── KPIRow (repeating)
│   ├── MetricNameInput
│   ├── CurrentValueInput
│   ├── TargetValueInput
│   └── RemoveButton
└── (managed by parent)
```

**Props Interface**
```typescript
interface KPIInputListProps {
  kpis: KPI[];
  onChange: (kpis: KPI[]) => void;
}
```

### 5.3 Updated Stepper Labels

**File Location**: `components/create-project/step-indicator.tsx`

**Change Required**
```tsx
// Current:
const steps = [
  { number: 1, label: "Details" },
  { number: 2, label: "Goals & KPIs" },  // ❌ OLD
  { number: 3, label: "Generate Tasks" },
  { number: 4, label: "Review" }
];

// Updated:
const steps = [
  { number: 1, label: "Details" },
  { number: 2, label: "Goals" },  // ✅ NEW (simplified)
  { number: 3, label: "Generate Tasks" },
  { number: 4, label: "Review" }
];
```

---

## 6. Implementation Guidelines

### 6.1 Phase 1: Component Refactoring

**Tasks**
1. ✅ Rename `goals-kpis-step.tsx` to `goals-step.tsx`
2. ✅ Update step indicator labels
3. ✅ Refactor GoalsStep component structure
4. ✅ Extract KPIInputList to separate component
5. ✅ Update form data structure in wizard state

**Estimated Time**: 3-4 hours

### 6.2 Phase 2: UI Implementation

**Tasks**
1. ✅ Implement collapsible advanced section
2. ✅ Add AI hint callout with Sparkles icon
3. ✅ Style KPI input grid (3 columns: name, current, target)
4. ✅ Add remove button for KPI rows
5. ✅ Implement "+ Add another metric" button
6. ✅ Apply all theme colors and spacing

**Design Checklist**
- [ ] All colors match design system variables
- [ ] Typography uses Tahoma (headings) and Montserrat (body)
- [ ] Spacing follows 4px grid (p-2, p-3, p-4, p-6)
- [ ] Transitions are 200-300ms
- [ ] Icons are w-4 h-4 or w-5 h-5
- [ ] Border radius matches patterns (rounded-lg, rounded-xl, rounded-md)
- [ ] Dark mode colors are properly applied
- [ ] Hover states have proper feedback

**Estimated Time**: 4-5 hours

### 6.3 Phase 3: State Management Updates

**Tasks**
1. ✅ Update wizard context/state to handle new data structure
2. ✅ Update form validation (only goalsDescription required)
3. ✅ Add state persistence to localStorage
4. ✅ Handle KPI array manipulation (add, edit, remove)

**Data Structure Changes**
```typescript
// OLD structure
interface Step2Data {
  goals: string[];  // Array of separate goal strings
  kpis: KPI[];      // Required array
}

// NEW structure
interface Step2Data {
  goalsDescription: string;  // Single freeform text
  kpis?: KPI[];             // Optional array
}
```

**Estimated Time**: 2-3 hours

### 6.4 Phase 4: Backend Integration Prep

**Tasks**
1. ✅ Update API payload structure for project creation
2. ✅ Add goalsDescription field to backend processing
3. ✅ Make KPIs optional in database schema (if not already)
4. ✅ Update Step 3 to handle AI extraction of goals from description

**API Changes**
```typescript
// POST /api/projects/create
{
  step1: { /* details */ },
  step2: {
    goalsDescription: string;  // NEW: freeform text
    kpis?: KPI[];              // MODIFIED: now optional
  }
}

// Step 3 processing will extract:
{
  extractedGoals: string[];      // AI-parsed from description
  suggestedKPIs: KPI[];         // AI-generated or user-provided
  generatedTasks: Task[];
}
```

**Estimated Time**: 3-4 hours

### 6.5 Phase 5: Testing & Refinement

**Tasks**
1. ✅ Test complete wizard flow (all 4 steps)
2. ✅ Test advanced section collapse/expand
3. ✅ Test KPI add/remove functionality
4. ✅ Test form validation
5. ✅ Test theme switching (light/dark)
6. ✅ Test responsive design (mobile, tablet, desktop)
7. ✅ User testing with 5-10 people
8. ✅ Collect metrics (time on step, completion rate)

**Estimated Time**: 4-5 hours

### 6.6 Total Implementation Time
**Estimated**: 16-21 hours (2-3 days of focused work)

---

## 7. Testing & Validation Criteria

### 7.1 Functional Requirements

**FR-1: Goals Description Input**
- [ ] User can enter freeform text in goals textarea
- [ ] Textarea expands to fit content (min 5 rows)
- [ ] Character count indicator shows (optional)
- [ ] Text persists when navigating back/forward
- [ ] Validation error shows if empty and user tries to continue

**FR-2: Advanced Section Toggle**
- [ ] "Show advanced options" button displays correctly
- [ ] Clicking toggles section visibility with smooth animation
- [ ] Button text changes to "Hide advanced options" when expanded
- [ ] ChevronRight icon rotates 90° when expanded
- [ ] State persists during navigation within wizard

**FR-3: KPI Input Management**
- [ ] User can add new KPI rows (max 10)
- [ ] User can edit metric name, current value, target value
- [ ] User can remove KPI rows with X button
- [ ] Empty KPI rows are filtered out before submission
- [ ] KPI data persists when navigating back/forward

**FR-4: Navigation**
- [ ] Back button navigates to Step 1
- [ ] Continue button navigates to Step 3
- [ ] Continue button disabled when goals description is empty
- [ ] Form data saves to wizard state on navigation

### 7.2 UI/UX Requirements

**UX-1: Visual Design**
- [ ] All colors match design system (glaucous, tea-green, etc.)
- [ ] Typography uses correct fonts (Tahoma/Montserrat)
- [ ] Spacing follows 4px grid system
- [ ] Component alignment is consistent
- [ ] Visual hierarchy is clear (title > description > form)

**UX-2: Theme Support**
- [ ] Light mode displays correctly
- [ ] Dark mode displays correctly
- [ ] Theme toggle transitions smoothly
- [ ] All text remains readable in both themes
- [ ] Background colors adapt properly

**UX-3: Responsive Design**
- [ ] Desktop (>1024px): Full layout, 3-column KPI grid
- [ ] Tablet (768px-1024px): Adjusted layout, 3-column KPI grid
- [ ] Mobile (<768px): Stacked layout, 1-column KPI grid
- [ ] Touch targets are 44px minimum on mobile
- [ ] No horizontal scroll on any screen size

**UX-4: Accessibility**
- [ ] All form inputs have proper labels
- [ ] Tab order is logical
- [ ] Focus indicators are visible
- [ ] Screen reader announces section expansion
- [ ] Color contrast meets WCAG AA standards

**UX-5: Micro-interactions**
- [ ] Buttons show hover states
- [ ] Transitions are smooth (200-300ms)
- [ ] Sparkles icon draws attention to AI hint
- [ ] Chevron rotation provides feedback
- [ ] Remove button (X) shows destructive action on hover

### 7.3 Performance Requirements

**PERF-1: Rendering**
- [ ] Initial render completes in <100ms
- [ ] Advanced section toggle animates at 60fps
- [ ] No layout shift during collapse/expand
- [ ] Form inputs respond immediately (<50ms)

**PERF-2: State Management**
- [ ] Form state updates don't cause full re-renders
- [ ] LocalStorage saves are debounced (500ms)
- [ ] Navigation between steps is instant

### 7.4 Analytics & Metrics (Post-Launch)

**Metrics to Track**
```javascript
// Time measurements
trackTiming('step2_time_to_complete'); // Target: <60s
trackTiming('advanced_section_interaction_time');

// Interaction events
trackEvent('step2_advanced_opened'); // Expected: <30%
trackEvent('step2_advanced_closed');
trackEvent('step2_kpi_added'); // Expected: <20%
trackEvent('step2_kpi_removed');

// Completion tracking
trackEvent('step2_completed'); // Target: >95%
trackEvent('step2_abandoned'); // Target: <5%

// Validation errors
trackEvent('step2_validation_error'); // Track empty fields
```

**Success Criteria**
- Step 2 completion rate: >95% (up from ~85%)
- Average time on step: <60 seconds (down from ~120s)
- Advanced section usage: <30% of users
- KPI definition: <25% of users (showing it's truly optional)
- User satisfaction: >4.0/5.0

---

## 8. Appendix: Code Snippets

### 8.1 Complete GoalsStep Component

```tsx
// components/create-project/steps/goals-step.tsx

'use client';

import { useState } from 'react';
import { ArrowLeft, ArrowRight, Sparkles, ChevronRight } from 'lucide-react';
import { KPIInputList } from '../kpi-input-list';

interface KPI {
  id: string;
  name: string;
  current: string;
  target: string;
}

interface GoalsStepProps {
  formData: {
    goalsDescription: string;
    kpis?: KPI[];
  };
  updateFormData: (data: any) => void;
  onNext: () => void;
  onBack: () => void;
}

export function GoalsStep({ 
  formData, 
  updateFormData, 
  onNext, 
  onBack 
}: GoalsStepProps) {
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [localKPIs, setLocalKPIs] = useState<KPI[]>(formData.kpis || []);

  const handleKPIChange = (kpis: KPI[]) => {
    setLocalKPIs(kpis);
    updateFormData({ kpis });
  };

  const handleContinue = () => {
    // Filter out empty KPIs before saving
    const validKPIs = localKPIs.filter(
      kpi => kpi.name.trim() || kpi.current.trim() || kpi.target.trim()
    );
    updateFormData({ kpis: validKPIs });
    onNext();
  };

  const isValid = formData.goalsDescription?.trim().length > 0;

  return (
    <div className="form-container max-w-3xl mx-auto">
      {/* Step Header */}
      <div className="mb-8">
        <h2 className="text-2xl font-bold text-text-primary dark:text-text-primary-dark font-heading mb-2">
          What do you want to achieve?
        </h2>
        <p className="text-sm text-text-secondary dark:text-text-secondary-dark font-body">
          Describe your project goals in plain language. AI will help structure 
          everything for you.
        </p>
      </div>

      {/* Main Goals Section */}
      <div className="space-y-4">
        <label className="block">
          <span className="form-label text-sm font-semibold text-text-primary dark:text-text-primary-dark mb-2 block">
            Project Goals
            <span className="text-text-secondary dark:text-text-secondary-dark ml-2 text-sm font-normal">
              (What success looks like for this project)
            </span>
          </span>
          
          <textarea
            placeholder="Example: Create a user-friendly mobile app that helps people track their fitness goals, with at least 1000 active users in the first 3 months..."
            rows={5}
            value={formData.goalsDescription || ''}
            onChange={(e) => updateFormData({ goalsDescription: e.target.value })}
            className="w-full px-4 py-3 border border-background-300 dark:border-background-700 rounded-md 
                     bg-white dark:bg-background-800 
                     text-text-primary dark:text-text-primary-dark
                     focus:outline-none focus:ring-2 focus:ring-glaucous focus:border-transparent
                     transition-all duration-200 font-body text-sm
                     placeholder:text-text-secondary dark:placeholder:text-text-secondary-dark"
          />
        </label>

        {/* AI Hint Callout */}
        <div className="flex items-start gap-3 text-sm text-text-secondary dark:text-text-secondary-dark 
                      bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-900/30">
          <Sparkles className="w-5 h-5 text-glaucous flex-shrink-0 mt-0.5" />
          <p className="font-body">
            Don't worry about being too specific - AI will break this down into 
            actionable tasks and suggest measurements for you.
          </p>
        </div>
      </div>

      {/* Advanced Section */}
      <div className="mt-8 border-t border-background-300 dark:border-background-700 pt-6">
        <button
          type="button"
          onClick={() => setShowAdvanced(!showAdvanced)}
          className="flex items-center gap-2 text-sm font-semibold text-glaucous hover:text-glaucous/80 
                   transition-colors duration-200 font-body"
        >
          <ChevronRight 
            className={`w-4 h-4 transition-transform duration-200 ${
              showAdvanced ? 'rotate-90' : ''
            }`} 
          />
          {showAdvanced ? 'Hide' : 'Show'} advanced options
        </button>

        {/* Collapsible Content */}
        <div 
          className={`overflow-hidden transition-all duration-300 ${
            showAdvanced ? 'max-h-[1000px] opacity-100 mt-4' : 'max-h-0 opacity-0'
          }`}
        >
          <div className="pl-6 border-l-2 border-background-200 dark:border-background-700">
            <div className="bg-background-100 dark:bg-background-800 p-5 rounded-lg 
                          border border-background-200 dark:border-background-700">
              {/* KPI Section Header */}
              <div className="mb-4">
                <label className="form-label text-sm font-semibold text-text-primary dark:text-text-primary-dark 
                               flex items-center gap-2 mb-2">
                  Success Metrics
                  <span className="text-xs bg-background-200 dark:bg-background-700 
                                 text-text-secondary dark:text-text-secondary-dark 
                                 px-2 py-0.5 rounded-full font-normal">
                    Optional
                  </span>
                </label>
                <p className="text-sm text-text-secondary dark:text-text-secondary-dark font-body">
                  Define specific numbers you want to track (KPIs). You can also add 
                  these later or let AI suggest them.
                </p>
              </div>
              
              {/* KPI Input List */}
              <KPIInputList 
                kpis={localKPIs}
                onChange={handleKPIChange}
              />
              
              {/* Add Metric Button */}
              <button
                type="button"
                onClick={() => {
                  const newKPI: KPI = {
                    id: Date.now().toString(),
                    name: '',
                    current: '',
                    target: '',
                  };
                  handleKPIChange([...localKPIs, newKPI]);
                }}
                className="text-sm text-glaucous hover:text-glaucous/80 font-semibold 
                         transition-colors duration-200 mt-3 font-body"
              >
                + Add another metric
              </button>
            </div>

            {/* Tip Callout */}
            <div className="text-xs text-text-secondary dark:text-text-secondary-dark 
                          italic mt-3 font-body flex items-start gap-2">
              <span className="text-base">💡</span>
              <p>
                <strong>Tip:</strong> Most projects don't need detailed metrics upfront. 
                AI can suggest relevant ones after seeing your goals and tasks.
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Navigation Buttons */}
      <div className="flex justify-between items-center mt-10 pt-6 
                    border-t border-background-300 dark:border-background-700">
        <button 
          onClick={onBack} 
          className="flex items-center gap-2 px-6 py-2.5 rounded-lg
                   bg-background-100 dark:bg-background-800 
                   hover:bg-background-200 dark:hover:bg-background-700
                   text-text-primary dark:text-text-primary-dark
                   border border-background-300 dark:border-background-700
                   transition-all duration-200 font-semibold text-sm font-body"
        >
          <ArrowLeft className="w-4 h-4" />
          Back
        </button>
        
        <button 
          onClick={handleContinue}
          disabled={!isValid}
          className="flex items-center gap-2 px-6 py-2.5 rounded-lg
                   bg-bittersweet hover:bg-bittersweet/90
                   text-white font-semibold text-sm
                   disabled:opacity-50 disabled:cursor-not-allowed
                   transition-all duration-200 font-body
                   shadow-md hover:shadow-lg"
        >
          Continue
          <ArrowRight className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}
```

### 8.2 KPIInputList Component

```tsx
// components/create-project/kpi-input-list.tsx

'use client';

import { X } from 'lucide-react';

interface KPI {
  id: string;
  name: string;
  current: string;
  target: string;
}

interface KPIInputListProps {
  kpis: KPI[];
  onChange: (kpis: KPI[]) => void;
}

export function KPIInputList({ kpis, onChange }: KPIInputListProps) {
  const updateKPI = (id: string, field: keyof KPI, value: string) => {
    const updated = kpis.map(kpi => 
      kpi.id === id ? { ...kpi, [field]: value } : kpi
    );
    onChange(updated);
  };

  const removeKPI = (id: string) => {
    onChange(kpis.filter(kpi => kpi.id !== id));
  };

  if (kpis.length === 0) {
    return (
      <div className="text-sm text-text-secondary dark:text-text-secondary-dark italic py-4 text-center">
        No metrics added yet. Click "+ Add another metric" below to get started.
      </div>
    );
  }

  return (
    <div className="space-y-3">
      {kpis.map((kpi) => (
        <div key={kpi.id} className="flex gap-2 items-start">
          <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-2">
            {/* Metric Name */}
            <input
              type="text"
              placeholder="Metric name"
              value={kpi.name}
              onChange={(e) => updateKPI(kpi.id, 'name', e.target.value)}
              className="px-3 py-2 border border-background-300 dark:border-background-700 rounded-md 
                       bg-white dark:bg-background-900 
                       text-text-primary dark:text-text-primary-dark
                       text-sm font-body
                       focus:outline-none focus:ring-2 focus:ring-glaucous focus:border-transparent
                       transition-all duration-200
                       placeholder:text-text-secondary dark:placeholder:text-text-secondary-dark"
            />
            
            {/* Current Value */}
            <input
              type="text"
              placeholder="Current value"
              value={kpi.current}
              onChange={(e) => updateKPI(kpi.id, 'current', e.target.value)}
              className="px-3 py-2 border border-background-300 dark:border-background-700 rounded-md 
                       bg-white dark:bg-background-900 
                       text-text-primary dark:text-text-primary-dark
                       text-sm font-body
                       focus:outline-none focus:ring-2 focus:ring-glaucous focus:border-transparent
                       transition-all duration-200
                       placeholder:text-text-secondary dark:placeholder:text-text-secondary-dark"
            />
            
            {/* Target Value */}
            <input
              type="text"
              placeholder="Target value"
              value={kpi.target}
              onChange={(e) => updateKPI(kpi.id, 'target', e.target.value)}
              className="px-3 py-2 border border-background-300 dark:border-background-700 rounded-md 
                       bg-white dark:bg-background-900 
                       text-text-primary dark:text-text-primary-dark
                       text-sm font-body
                       focus:outline-none focus:ring-2 focus:ring-glaucous focus:border-transparent
                       transition-all duration-200
                       placeholder:text-text-secondary dark:placeholder:text-text-secondary-dark"
            />
          </div>
          
          {/* Remove Button */}
          <button
            type="button"
            onClick={() => removeKPI(kpi.id)}
            className="p-2 text-text-secondary hover:text-bittersweet 
                     transition-colors duration-200 rounded-md
                     hover:bg-red-50 dark:hover:bg-red-900/20"
            aria-label="Remove metric"
          >
            <X className="w-4 h-4" />
          </button>
        </div>
      ))}
    </div>
  );
}
```

### 8.3 Updated Wizard State Management

```typescript
// components/create-project/create-project-wizard.tsx

interface WizardFormData {
  // Step 1
  projectName: string;
  description: string;
  domain: string;
  timeline: string;
  
  // Step 2 - UPDATED
  goalsDescription: string;  // NEW: freeform text
  kpis?: KPI[];             // CHANGED: now optional
  
  // Step 3
  generatedTasks: Task[];
  
  // Step 4
  // ... review data
}

const [formData, setFormData] = useState<WizardFormData>({
  projectName: '',
  description: '',
  domain: '',
  timeline: '',
  goalsDescription: '',  // NEW
  kpis: [],              // OPTIONAL
  generatedTasks: [],
});

// Update function
const updateFormData = (data: Partial<WizardFormData>) => {
  setFormData(prev => ({ ...prev, ...data }));
  
  // Persist to localStorage
  localStorage.setItem('collabflow_wizard_draft', JSON.stringify({
    ...formData,
    ...data
  }));
};

// Validation for Step 2
const validateStep2 = (): boolean => {
  return formData.goalsDescription.trim().length > 0;
  // Note: KPIs are NOT required for validation
};
```

### 8.4 CSS Variables Reference (from globals.css)

```css
/* CRITICAL: These are the ONLY colors allowed in the implementation */

:root {
  /* Primary Brand Colors */
  --glaucous: #5C80BC;
  --tea-green: #C4D6B0;
  --orange-peel: #FF9F1C;
  --bittersweet: #EB5E55;

  /* Background Hierarchy (Light Mode) */
  --background-50: #FAFBFC;
  --background-100: #F5F7F9;
  --background-200: #E8EDF2;
  --background-300: #D1DBE5;

  /* Text (Light Mode) */
  --text-primary: #1A1F26;
  --text-secondary: #4A5568;

  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-accent: 0 0 0 3px rgba(92, 128, 188, 0.1);
}

.dark {
  /* Background Hierarchy (Dark Mode) */
  --background-900: #0F1419;
  --background-800: #1A1F26;
  --background-700: #2A3038;

  /* Text (Dark Mode) */
  --text-primary-dark: #F5F7F9;
  --text-secondary-dark: #A0AEC0;
}

/* Utility Classes (Pre-defined, must use these) */
.btn-primary {
  @apply bg-bittersweet hover:bg-bittersweet/90 text-white px-6 py-2.5 rounded-lg 
         font-semibold transition-all duration-200 shadow-md hover:shadow-lg;
}

.btn-secondary {
  @apply bg-background-100 dark:bg-background-800 
         hover:bg-background-200 dark:hover:bg-background-700
         text-text-primary dark:text-text-primary-dark
         border border-background-300 dark:border-background-700
         px-6 py-2.5 rounded-lg font-semibold transition-all duration-200;
}

.form-label {
  @apply text-sm font-semibold text-text-primary dark:text-text-primary-dark 
         mb-2 block font-body;
}

.input-field {
  @apply w-full px-4 py-3 border border-background-300 dark:border-background-700 
         rounded-md bg-white dark:bg-background-800 
         text-text-primary dark:text-text-primary-dark
         focus:outline-none focus:ring-2 focus:ring-glaucous focus:border-transparent
         transition-all duration-200 font-body text-sm;
}
```

### 8.5 Responsive Design Breakpoints

```tsx
// Tailwind breakpoints used in the design
const breakpoints = {
  sm: '640px',   // Mobile landscape
  md: '768px',   // Tablet
  lg: '1024px',  // Desktop
  xl: '1280px',  // Large desktop
};

// Example responsive classes for KPI grid:
className="grid grid-cols-1 md:grid-cols-3 gap-2"
// Mobile: 1 column (stacked)
// Tablet+: 3 columns (side by side)

// Container max-widths
className="max-w-3xl mx-auto"  // Step content container
className="max-w-7xl mx-auto"  // Full page container
```

---

## Document Approval

**Author**: CollabFlow Development Team  
**Reviewed By**: [To be filled]  
**Approved By**: [To be filled]  
**Date**: October 26, 2025  

**Change Log**:
- v1.0 (Oct 26, 2025): Initial specification document

---

## Next Steps

1. **Review & Approval**: Have design team review this specification
2. **Implementation**: Follow phased implementation plan (Sections 6.1-6.5)
3. **Testing**: Execute test plan (Section 7)
4. **User Validation**: Conduct user testing with 5-10 participants
5. **Metrics Collection**: Implement analytics tracking (Section 7.4)
6. **Iteration**: Refine based on data and feedback

---

**END OF DOCUMENT**
