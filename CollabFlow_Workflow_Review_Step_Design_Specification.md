# CollabFlow Workflow Review Step - Design Specification
## Interactive Workflow Planning in Project Creation Wizard

**Document Version:** 1.0  
**Date:** October 27, 2025  
**Project:** CollabFlow React Demo Application  
**Feature:** Step 4 - Workflow Review (New Step in 5-Step Wizard)  

---

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Problem Statement & Solution](#problem-statement--solution)
3. [User Experience Design](#user-experience-design)
4. [Design System Adherence](#design-system-adherence)
5. [Component Architecture](#component-architecture)
6. [Implementation Specification](#implementation-specification)
7. [Interaction Patterns](#interaction-patterns)
8. [Testing & Validation](#testing--validation)
9. [Appendix: Code Samples](#appendix-code-samples)

---

## 1. Executive Summary

### The New 5-Step Wizard Flow

```
Step 1: Project Details
   ↓
Step 2: Goals (Revised - Freeform + Optional KPIs)
   ↓
Step 3: AI Task Generation (Loading state)
   ↓
Step 4: WORKFLOW REVIEW (NEW) ← THIS DOCUMENT
   ↓
Step 5: Final Review & Create
```

### What Step 4 Does

**Purpose**: Interactive workflow planning checkpoint where users can:
- Review AI-generated task structure and dependencies
- Visually organize tasks in a flowchart
- Identify and expand vague/high-level tasks
- Adjust task sequence and dependencies
- Validate workflow logic before project creation

**Key Insight**: This is NOT just visualization - it's **collaborative planning** with AI where users can refine structure before committing.

---

## 2. Problem Statement & Solution

### 2.1 The Problem

**Current Flow Issues:**
1. Users don't see workflow structure until AFTER project is created
2. No opportunity to catch missing dependencies during setup
3. Vague tasks aren't identified early (causes rework later)
4. No visual confirmation that AI understood the project correctly
5. Workflow tab feels disconnected from planning process

**User Pain Points:**
- "I created the project but realized tasks are in the wrong order"
- "Some tasks AI generated are too vague - I need to break them down"
- "Task B depends on Task A, but AI didn't catch that"
- "I don't understand how these tasks fit together"

### 2.2 The Solution

**Step 4: Workflow Review** serves as an interactive checkpoint:

```
User Mental Model:
"Show me how this will work" → Review workflow
"This doesn't look right" → Adjust structure
"What does this task involve?" → Expand task
"These need to happen in order" → Add dependency
"Looks good!" → Proceed to create project
```

**Value Proposition:**
- ✅ Catch structural issues early (before creation)
- ✅ Build confidence in AI-generated plan
- ✅ Educational moment (users learn workflow thinking)
- ✅ Reduce post-creation rework
- ✅ Identify vague tasks that need breakdown

---

## 3. User Experience Design

### 3.1 Step 4 Entry Point

**Context:** User just completed Step 3 (AI Generation)

```
┌─────────────────────────────────────────────┐
│ Step 3: AI Task Generation                  │
│                                             │
│  [AI Loading Animation]                     │
│  ✓ Analyzing goals                          │
│  ✓ Generating tasks                         │
│  ✓ Calculating dependencies                 │
│  ⟳ Building workflow...                     │
└─────────────────────────────────────────────┘
                    ↓
         [Transition to Step 4]
                    ↓
┌─────────────────────────────────────────────┐
│ Step 4: Review Your Workflow                │
│                                             │
│ "AI organized your tasks into a workflow.   │
│  Review the structure and make adjustments  │
│  before creating your project."             │
└─────────────────────────────────────────────┘
```

### 3.2 Core Interface Layout

```
┌──────────────────────────────────────────────────────────────┐
│ Step 4: Review Your Workflow                          [4/5]  │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│ AI organized your 8 tasks into a workflow.                  │
│ Drag tasks to rearrange or click to edit.                   │
│                                                              │
│ ┌────────────────────────────────────────────────────────┐ │
│ │                  WORKFLOW CANVAS                       │ │
│ │                                                        │ │
│ │    ○ START                                             │ │
│ │      │                                                 │ │
│ │      ▼                                                 │ │
│ │  ┌─────────────┐                                      │ │
│ │  │ Task 1      │ (Human)                              │ │
│ │  │ Setup repo  │                                      │ │
│ │  └──────┬──────┘                                      │ │
│ │         │                                             │ │
│ │         ▼                                             │ │
│ │  ┌─────────────┐    ┌─────────────┐                 │ │
│ │  │ Task 2      │───▶│ Task 3      │ (Parallel)      │ │
│ │  │ Design DB   │    │ Create API  │                 │ │
│ │  └──────┬──────┘    └──────┬──────┘                 │ │
│ │         │                   │                        │ │
│ │         └─────────┬─────────┘                        │ │
│ │                   ▼                                  │ │
│ │           ┌─────────────┐                           │ │
│ │           │ Task 4      │ (HITL)                    │ │
│ │           │ Review code │ ⚠ Needs breakdown        │ │
│ │           └──────┬──────┘                           │ │
│ │                  │                                  │ │
│ │                  ▼                                  │ │
│ │                ○ END                                │ │
│ │                                                     │ │
│ │  [Zoom: -] [+]  [Reset View]  [Auto-Layout]       │ │
│ └────────────────────────────────────────────────────┘ │
│                                                         │
│ ⚠ Issues Detected:                                      │
│ ┌────────────────────────────────────────────────────┐ │
│ │ • "Setup repo" is vague - consider breaking down   │ │
│ │ • Task 4 may need human oversight                  │ │
│ └────────────────────────────────────────────────────┘ │
│                                                         │
│ [Back]  [Regenerate Workflow]         [Looks Good →]  │
└─────────────────────────────────────────────────────────┘
```

### 3.3 User Interaction Flows

#### Flow A: Quick Review (Happy Path)
```
User sees workflow → Looks good → Clicks "Looks Good" → Step 5
Time: ~15 seconds
```

#### Flow B: Minor Adjustments
```
User sees workflow → Drags Task 3 before Task 2 → Dependencies update
→ Clicks "Looks Good" → Step 5
Time: ~30-60 seconds
```

#### Flow C: Deep Engagement
```
User sees workflow → Clicks "Setup repo" (vague task)
→ Expands to 3 subtasks → Rearranges dependencies
→ Adds connection between Task 2 and Task 5
→ Clicks "Looks Good" → Step 5
Time: ~2-4 minutes
```

#### Flow D: Regeneration
```
User sees workflow → "This doesn't look right"
→ Clicks "Regenerate Workflow"
→ AI generates new structure
→ User reviews → Clicks "Looks Good" → Step 5
Time: ~45 seconds
```

### 3.4 Task Node States

**Visual States:**
```
┌─────────────────┐
│ Task Title      │  ← Normal state
│ [Type Badge]    │
└─────────────────┘

┌─────────────────┐
│ Task Title      │  ← Hover state
│ [Type Badge]    │     (slight elevation, border glow)
└─────────────────┘

┌─────────────────┐
│ Task Title      │  ← Selected state
│ [Type Badge]    │     (highlighted border, actions visible)
│ [Edit] [Delete] │
└─────────────────┘

┌─────────────────┐
│ Task Title   ⚠ │  ← Warning state
│ [Type Badge]    │     (vague/needs attention)
└─────────────────┘
```

### 3.5 Empty/Error States

**No Tasks Generated:**
```
┌──────────────────────────────────┐
│  No workflow yet                 │
│                                  │
│  AI couldn't generate tasks      │
│  from your goals. Try:           │
│                                  │
│  • Adding more detail to goals   │
│  • Going back to Step 2          │
│  • Manually adding tasks         │
│                                  │
│  [Go Back] [Add Task Manually]   │
└──────────────────────────────────┘
```

**Generation Error:**
```
┌──────────────────────────────────┐
│  ⚠ Generation Error              │
│                                  │
│  Something went wrong while      │
│  creating your workflow.         │
│                                  │
│  [Try Again] [Go Back]           │
└──────────────────────────────────┘
```

---

## 4. Design System Adherence

### 4.1 Color Usage (MUST FOLLOW)

**Task Node Colors** (by type):
```css
/* AI Tasks */
.task-node-ai {
  background: var(--glaucous);     /* #5C80BC */
  border: 2px solid var(--glaucous);
  color: white;
}

/* Human Tasks */
.task-node-human {
  background: var(--tea-green);    /* #C4D6B0 */
  border: 2px solid var(--tea-green);
  color: var(--text-primary);
}

/* HITL Tasks */
.task-node-hitl {
  background: var(--orange-peel);  /* #FF9F1C */
  border: 2px solid var(--orange-peel);
  color: white;
}

/* Warning/Vague Task Indicator */
.task-warning-badge {
  background: var(--bittersweet);  /* #EB5E55 */
  color: white;
}
```

**Connection Lines:**
```css
/* Dependency arrows */
.workflow-connection {
  stroke: var(--background-300);     /* Light mode */
  stroke: var(--background-700);     /* Dark mode */
  stroke-width: 2px;
}

/* Critical path (optional highlight) */
.workflow-connection-critical {
  stroke: var(--bittersweet);
  stroke-width: 3px;
}
```

**Canvas Background:**
```css
/* Canvas area */
.workflow-canvas {
  background: var(--background-50);  /* Light mode */
  background: var(--background-900); /* Dark mode */
  border: 1px solid var(--background-300);
  border-radius: 12px;
}
```

### 4.2 Typography (MUST FOLLOW)

```css
/* Step title */
h2.step-title {
  font-family: 'Tahoma', sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
}

/* Step description */
p.step-description {
  font-family: 'Montserrat', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: var(--text-secondary);
}

/* Task node title */
.task-node-title {
  font-family: 'Montserrat', sans-serif;
  font-size: 14px;
  font-weight: 600;
}

/* Task type badge */
.task-type-badge {
  font-family: 'Montserrat', sans-serif;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
```

### 4.3 Spacing & Layout (MUST FOLLOW)

```css
/* Canvas dimensions */
.workflow-canvas {
  min-height: 500px;
  max-height: 600px;
  padding: 24px; /* p-6 */
}

/* Task node dimensions */
.task-node {
  width: 200px;
  min-height: 80px;
  padding: 12px; /* p-3 */
  border-radius: 8px; /* rounded-lg */
}

/* Spacing between warnings and canvas */
.issues-section {
  margin-top: 24px; /* mt-6 */
}

/* Button spacing */
.action-buttons {
  display: flex;
  justify-content: space-between;
  gap: 16px; /* gap-4 */
  margin-top: 32px; /* mt-8 */
}
```

### 4.4 Animations & Transitions (MUST FOLLOW)

```css
/* Task node hover */
.task-node {
  transition: all 0.2s ease-in-out;
}

.task-node:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

/* Connection line animation */
.workflow-connection {
  transition: stroke 0.2s ease-in-out;
}

/* Drag state */
.task-node-dragging {
  opacity: 0.7;
  transform: scale(1.05);
  cursor: grabbing;
}

/* Canvas zoom/pan */
.workflow-canvas-transform {
  transition: transform 0.3s ease-out;
}
```

---

## 5. Component Architecture

### 5.1 Component Hierarchy

```
WorkflowReviewStep
├── StepHeader (h2 + description)
├── WorkflowCanvas
│   ├── CanvasToolbar
│   │   ├── ZoomControls
│   │   ├── ViewResetButton
│   │   └── AutoLayoutButton
│   ├── SVGCanvas
│   │   ├── ConnectionLayer (SVG paths)
│   │   └── NodesLayer
│   │       ├── StartNode
│   │       ├── TaskNode (multiple, draggable)
│   │       │   ├── TaskTitle
│   │       │   ├── TaskTypeBadge
│   │       │   ├── WarningIndicator (conditional)
│   │       │   └── NodeActions (on select)
│   │       └── EndNode
│   └── MiniMap (optional, corner)
├── IssuesPanel (conditional)
│   └── IssueItem (repeating)
└── NavigationButtons
    ├── BackButton
    ├── RegenerateButton
    └── ContinueButton
```

### 5.2 Key Component Props

```typescript
interface WorkflowReviewStepProps {
  formData: {
    projectName: string;
    goalsDescription: string;
    generatedTasks: Task[];
    workflow?: WorkflowStructure;
  };
  updateFormData: (data: Partial<FormData>) => void;
  onNext: () => void;
  onBack: () => void;
  onRegenerate: () => Promise<void>;
}

interface Task {
  id: string;
  name: string;
  description: string;
  type: 'ai' | 'human' | 'hitl';
  estimated_hours?: number;
  isVague?: boolean; // AI-detected vagueness
  dependencies?: string[]; // Task IDs
  position?: { x: number; y: number }; // Canvas position
}

interface WorkflowStructure {
  nodes: WorkflowNode[];
  connections: Connection[];
  layout: 'vertical' | 'horizontal' | 'custom';
}

interface WorkflowNode {
  id: string;
  taskId: string;
  position: { x: number; y: number };
  type: 'start' | 'task' | 'end';
}

interface Connection {
  id: string;
  from: string; // Node ID
  to: string;   // Node ID
  type: 'sequential' | 'dependency' | 'parallel';
}
```

### 5.3 State Management

```typescript
// Local state for workflow editing
const [workflow, setWorkflow] = useState<WorkflowStructure>(
  formData.workflow || generateDefaultWorkflow(formData.generatedTasks)
);

const [selectedNode, setSelectedNode] = useState<string | null>(null);
const [isDragging, setIsDragging] = useState(false);
const [draggedNode, setDraggedNode] = useState<string | null>(null);
const [zoom, setZoom] = useState(1.0);
const [panOffset, setPanOffset] = useState({ x: 0, y: 0 });
const [issues, setIssues] = useState<WorkflowIssue[]>(
  detectWorkflowIssues(workflow, formData.generatedTasks)
);

// Update parent form data when workflow changes
useEffect(() => {
  updateFormData({ workflow });
}, [workflow]);
```

---

## 6. Implementation Specification

### 6.1 Phase 1: Basic Workflow Display (Week 1)

**Tasks:**
1. ✅ Create WorkflowReviewStep component
2. ✅ Implement static SVG canvas
3. ✅ Render task nodes from AI-generated tasks
4. ✅ Draw connection lines between nodes
5. ✅ Apply color coding (AI/Human/HITL)
6. ✅ Add Start/End nodes
7. ✅ Implement auto-layout algorithm (basic vertical)

**Deliverable:** Static workflow visualization that displays tasks in logical order

**Time Estimate:** 12-16 hours

### 6.2 Phase 2: Interactivity (Week 2)

**Tasks:**
1. ✅ Implement drag-and-drop for task nodes
2. ✅ Update connections when nodes move
3. ✅ Add node selection (click)
4. ✅ Show node actions panel on selection
5. ✅ Implement zoom controls (in/out/reset)
6. ✅ Add pan functionality (drag canvas)
7. ✅ Implement "Auto-Layout" button (re-organize)

**Deliverable:** Interactive canvas where users can rearrange tasks

**Time Estimate:** 16-20 hours

### 6.3 Phase 3: Issue Detection & Warnings (Week 3)

**Tasks:**
1. ✅ Implement vague task detection (AI integration)
2. ✅ Display warning badges on problematic nodes
3. ✅ Create IssuesPanel component
4. ✅ List detected issues with descriptions
5. ✅ Link issues to specific nodes (click to highlight)
6. ✅ Add "Expand Task" action for vague tasks

**Deliverable:** System identifies and surfaces workflow issues

**Time Estimate:** 10-14 hours

### 6.4 Phase 4: Advanced Features (Week 4)

**Tasks:**
1. ✅ Implement "Regenerate Workflow" with AI
2. ✅ Add manual task creation from canvas
3. ✅ Implement dependency editing (add/remove connections)
4. ✅ Add keyboard shortcuts (arrow keys for navigation)
5. ✅ Implement undo/redo for canvas actions
6. ✅ Add MiniMap for large workflows
7. ✅ Save workflow draft to localStorage

**Deliverable:** Full-featured workflow editing experience

**Time Estimate:** 20-24 hours

### 6.5 Total Implementation Time

**Estimated Total:** 58-74 hours (7-9 days of focused work)

---

## 7. Interaction Patterns

### 7.1 Drag-and-Drop Behavior

**Mouse Events:**
```javascript
// Dragging a task node
onMouseDown(node) {
  setIsDragging(true);
  setDraggedNode(node.id);
  setCursor('grabbing');
}

onMouseMove(event) {
  if (!isDragging) return;
  
  const newPosition = {
    x: event.clientX - canvasOffset.x,
    y: event.clientY - canvasOffset.y
  };
  
  updateNodePosition(draggedNode, newPosition);
  updateConnections(draggedNode); // Redraw lines
}

onMouseUp() {
  setIsDragging(false);
  setDraggedNode(null);
  setCursor('grab');
  saveWorkflowState(); // For undo/redo
}
```

**Touch Support:**
```javascript
// Mobile drag support
onTouchStart(node) {
  setIsDragging(true);
  setDraggedNode(node.id);
}

onTouchMove(event) {
  if (!isDragging) return;
  const touch = event.touches[0];
  updateNodePosition(draggedNode, {
    x: touch.clientX,
    y: touch.clientY
  });
}

onTouchEnd() {
  setIsDragging(false);
  setDraggedNode(null);
}
```

### 7.2 Connection Drawing

**Algorithm for Curved Bezier Connections:**
```javascript
function drawConnection(from, to) {
  const fromPos = getNodePosition(from);
  const toPos = getNodePosition(to);
  
  // Calculate control points for smooth curve
  const controlPoint1 = {
    x: fromPos.x,
    y: fromPos.y + (toPos.y - fromPos.y) / 2
  };
  
  const controlPoint2 = {
    x: toPos.x,
    y: fromPos.y + (toPos.y - fromPos.y) / 2
  };
  
  // Create SVG path
  return `M ${fromPos.x},${fromPos.y} 
          C ${controlPoint1.x},${controlPoint1.y} 
            ${controlPoint2.x},${controlPoint2.y} 
            ${toPos.x},${toPos.y}`;
}
```

### 7.3 Auto-Layout Algorithm

**Simple Vertical Layout:**
```javascript
function autoLayoutVertical(nodes) {
  const horizontalSpacing = 250;
  const verticalSpacing = 120;
  const startX = 400; // Center of canvas
  let currentY = 100;
  
  // Start node
  placeNode('start', { x: startX, y: currentY });
  currentY += verticalSpacing;
  
  // Task nodes (grouped by level)
  const levels = calculateDependencyLevels(nodes);
  
  levels.forEach(level => {
    const levelWidth = level.length * horizontalSpacing;
    const startX = (canvasWidth - levelWidth) / 2;
    
    level.forEach((node, index) => {
      placeNode(node.id, {
        x: startX + (index * horizontalSpacing),
        y: currentY
      });
    });
    
    currentY += verticalSpacing;
  });
  
  // End node
  placeNode('end', { x: startX, y: currentY });
}
```

### 7.4 Issue Detection Logic

**Vague Task Detection:**
```python
# Backend AI service
def detect_vague_tasks(tasks):
    vague_tasks = []
    
    for task in tasks:
        # Check for vague indicators
        vague_words = ['setup', 'configure', 'implement', 'build', 'create']
        is_short = len(task.name.split()) <= 3
        lacks_specifics = not any(
            keyword in task.description.lower() 
            for keyword in ['specific', 'exactly', 'using']
        )
        
        if any(word in task.name.lower() for word in vague_words) and \
           is_short and lacks_specifics:
            vague_tasks.append({
                'task_id': task.id,
                'reason': 'Task description lacks specific details',
                'suggestion': 'Consider breaking this into smaller, more specific tasks'
            })
    
    return vague_tasks
```

**Dependency Conflict Detection:**
```javascript
function detectDependencyConflicts(workflow) {
  const conflicts = [];
  
  // Check for circular dependencies
  const graph = buildDependencyGraph(workflow);
  const cycles = detectCycles(graph);
  
  if (cycles.length > 0) {
    conflicts.push({
      type: 'circular_dependency',
      nodes: cycles,
      message: 'Circular dependency detected - tasks depend on each other in a loop'
    });
  }
  
  // Check for missing dependencies
  workflow.connections.forEach(conn => {
    if (!nodeExists(conn.from) || !nodeExists(conn.to)) {
      conflicts.push({
        type: 'missing_dependency',
        connection: conn,
        message: 'Connection references non-existent task'
      });
    }
  });
  
  return conflicts;
}
```

---

## 8. Testing & Validation

### 8.1 Functional Requirements

**FR-W1: Workflow Display**
- [ ] All AI-generated tasks appear as nodes on canvas
- [ ] Task type colors are correct (AI=Blue, Human=Green, HITL=Orange)
- [ ] Connections between tasks show dependencies
- [ ] Start and End nodes are present
- [ ] Canvas fits within viewport (with scroll if needed)

**FR-W2: Drag and Drop**
- [ ] User can drag task nodes to new positions
- [ ] Connection lines update when nodes move
- [ ] Dragging works on both mouse and touch devices
- [ ] Node snaps back if dropped outside canvas
- [ ] Multiple nodes can be moved independently

**FR-W3: Node Interaction**
- [ ] Clicking node selects it (highlighted border)
- [ ] Selected node shows action buttons
- [ ] Clicking canvas background deselects node
- [ ] Hover shows subtle elevation effect
- [ ] Double-click on node opens edit modal (optional)

**FR-W4: Canvas Controls**
- [ ] Zoom in/out buttons work correctly
- [ ] Reset view returns to default zoom and position
- [ ] Auto-layout reorganizes nodes logically
- [ ] Pan (drag canvas) works smoothly
- [ ] Controls are accessible on mobile

**FR-W5: Issue Detection**
- [ ] Vague tasks are marked with warning badge
- [ ] Issues panel lists all detected problems
- [ ] Clicking issue highlights relevant node
- [ ] Issues update when workflow changes
- [ ] User can dismiss false positive warnings

**FR-W6: Regeneration**
- [ ] "Regenerate Workflow" button triggers AI
- [ ] Loading state shows during regeneration
- [ ] New workflow replaces old one
- [ ] User can undo regeneration
- [ ] Preserves manual edits where possible

### 8.2 UI/UX Requirements

**UX-W1: Visual Consistency**
- [ ] All colors match design system
- [ ] Typography uses Tahoma and Montserrat
- [ ] Spacing follows 4px grid
- [ ] Component alignment is consistent
- [ ] Dark mode works correctly

**UX-W2: Responsive Design**
- [ ] Desktop (>1024px): Full canvas view
- [ ] Tablet (768-1024px): Adjusted layout
- [ ] Mobile (<768px): Simplified view with scroll
- [ ] Touch targets are 44px minimum
- [ ] No horizontal overflow

**UX-W3: Performance**
- [ ] Canvas renders in <500ms for 20 tasks
- [ ] Drag operations run at 60fps
- [ ] Zoom/pan is smooth
- [ ] No jank during interactions
- [ ] Works with up to 50 tasks

**UX-W4: Accessibility**
- [ ] Keyboard navigation works (Tab, Arrow keys)
- [ ] Screen reader announces node selection
- [ ] Focus indicators are visible
- [ ] Color is not sole indicator (icons/labels)
- [ ] ARIA labels on interactive elements

### 8.3 User Testing Scenarios

**Scenario 1: Happy Path**
```
Given: User completes Step 3 (AI generates 8 tasks)
When: User reaches Step 4
Then: 
  - Workflow displays automatically
  - Tasks are logically arranged
  - No issues detected
  - User clicks "Looks Good" and proceeds
Expected Time: <20 seconds
```

**Scenario 2: Minor Rearrangement**
```
Given: User sees workflow but Task 3 should come before Task 2
When: User drags Task 3 above Task 2
Then:
  - Node moves smoothly
  - Connections update
  - No errors occur
  - User clicks "Looks Good" and proceeds
Expected Time: 30-60 seconds
```

**Scenario 3: Expanding Vague Task**
```
Given: AI marks "Setup infrastructure" as vague
When: User clicks the warning badge
Then:
  - Modal/panel opens
  - User sees suggestion to break down task
  - User can accept AI breakdown or manually edit
  - Task expands to 3 subtasks
  - Workflow updates
Expected Time: 1-2 minutes
```

**Scenario 4: Complete Regeneration**
```
Given: User doesn't like current workflow
When: User clicks "Regenerate Workflow"
Then:
  - Loading state appears
  - AI generates new structure
  - Previous workflow saved (can undo)
  - User reviews new workflow
Expected Time: 45 seconds
```

### 8.4 Analytics Tracking

```javascript
// Key metrics to track
trackEvent('workflow_review_entered');
trackEvent('workflow_review_exited');
trackTiming('workflow_review_duration'); // Target: <2 minutes

// Interaction tracking
trackEvent('workflow_node_dragged');
trackEvent('workflow_auto_layout_used');
trackEvent('workflow_zoom_changed');
trackEvent('workflow_regenerated');

// Issue tracking
trackEvent('workflow_issue_detected', { issue_type: 'vague_task' });
trackEvent('workflow_issue_resolved');
trackEvent('workflow_issue_ignored');

// Completion tracking
trackEvent('workflow_approved'); // "Looks Good" clicked
trackEvent('workflow_abandoned'); // User went back
```

**Success Criteria:**
- >90% of users click "Looks Good" (don't abandon)
- <2 minutes average time on step
- <20% use "Regenerate Workflow" (indicates good initial generation)
- >60% make zero edits (trusts AI completely)
- <5% go back to Step 2 (indicates workflow issues)

---

## 9. Appendix: Code Samples

### 9.1 WorkflowReviewStep Component

```tsx
// components/create-project/steps/workflow-review-step.tsx

'use client';

import { useState, useEffect } from 'react';
import { ArrowLeft, ArrowRight, RefreshCw, AlertTriangle } from 'lucide-react';
import { WorkflowCanvas } from '../workflow-canvas';
import { IssuesPanel } from '../issues-panel';

interface WorkflowReviewStepProps {
  formData: {
    projectName: string;
    goalsDescription: string;
    generatedTasks: Task[];
    workflow?: WorkflowStructure;
  };
  updateFormData: (data: any) => void;
  onNext: () => void;
  onBack: () => void;
}

export function WorkflowReviewStep({
  formData,
  updateFormData,
  onNext,
  onBack
}: WorkflowReviewStepProps) {
  const [workflow, setWorkflow] = useState<WorkflowStructure>(
    formData.workflow || generateDefaultWorkflow(formData.generatedTasks)
  );
  
  const [issues, setIssues] = useState<WorkflowIssue[]>([]);
  const [isRegenerating, setIsRegenerating] = useState(false);

  // Detect issues when workflow changes
  useEffect(() => {
    const detectedIssues = detectWorkflowIssues(workflow, formData.generatedTasks);
    setIssues(detectedIssues);
  }, [workflow, formData.generatedTasks]);

  // Update parent state when workflow changes
  useEffect(() => {
    updateFormData({ workflow });
  }, [workflow]);

  const handleRegenerateWorkflow = async () => {
    setIsRegenerating(true);
    
    try {
      // Call AI API to regenerate workflow
      const response = await fetch('/api/workflows/regenerate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          goals: formData.goalsDescription,
          tasks: formData.generatedTasks
        })
      });
      
      const newWorkflow = await response.json();
      setWorkflow(newWorkflow);
    } catch (error) {
      console.error('Failed to regenerate workflow:', error);
      // Show error toast
    } finally {
      setIsRegenerating(false);
    }
  };

  const taskCount = formData.generatedTasks.length;

  return (
    <div className="form-container max-w-6xl mx-auto">
      {/* Step Header */}
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-text-primary dark:text-text-primary-dark font-heading mb-2">
          Review Your Workflow
        </h2>
        <p className="text-sm text-text-secondary dark:text-text-secondary-dark font-body">
          AI organized your {taskCount} {taskCount === 1 ? 'task' : 'tasks'} into a workflow. 
          Review the structure and make adjustments before creating your project.
        </p>
      </div>

      {/* Workflow Canvas */}
      <WorkflowCanvas
        workflow={workflow}
        tasks={formData.generatedTasks}
        onWorkflowChange={setWorkflow}
        className="mb-6"
      />

      {/* Issues Panel (conditional) */}
      {issues.length > 0 && (
        <div className="mb-6">
          <div className="flex items-center gap-2 mb-3">
            <AlertTriangle className="w-5 h-5 text-orange-peel" />
            <h3 className="text-sm font-semibold text-text-primary dark:text-text-primary-dark font-body">
              Issues Detected
            </h3>
          </div>
          
          <IssuesPanel
            issues={issues}
            onIssueClick={(issue) => {
              // Highlight the problematic node
              // Scroll to node if needed
            }}
            onIssueResolve={(issueId) => {
              setIssues(prev => prev.filter(i => i.id !== issueId));
            }}
          />
        </div>
      )}

      {/* Helpful Tips */}
      <div className="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-900/30 mb-6">
        <p className="text-sm text-text-secondary dark:text-text-secondary-dark font-body">
          <strong>💡 Tip:</strong> Drag tasks to rearrange them or click a task to edit details. 
          The workflow can be refined later in your project dashboard.
        </p>
      </div>

      {/* Navigation Buttons */}
      <div className="flex justify-between items-center pt-6 border-t border-background-300 dark:border-background-700">
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

        <div className="flex gap-3">
          <button
            onClick={handleRegenerateWorkflow}
            disabled={isRegenerating}
            className="flex items-center gap-2 px-6 py-2.5 rounded-lg
                     bg-glaucous hover:bg-glaucous/90
                     text-white font-semibold text-sm
                     disabled:opacity-50 disabled:cursor-not-allowed
                     transition-all duration-200 font-body"
          >
            <RefreshCw className={`w-4 h-4 ${isRegenerating ? 'animate-spin' : ''}`} />
            {isRegenerating ? 'Regenerating...' : 'Regenerate Workflow'}
          </button>

          <button
            onClick={onNext}
            className="flex items-center gap-2 px-6 py-2.5 rounded-lg
                     bg-bittersweet hover:bg-bittersweet/90
                     text-white font-semibold text-sm
                     transition-all duration-200 font-body
                     shadow-md hover:shadow-lg"
          >
            Looks Good
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  );
}

// Helper function to generate default workflow from tasks
function generateDefaultWorkflow(tasks: Task[]): WorkflowStructure {
  // Simple vertical layout with sequential dependencies
  const nodes: WorkflowNode[] = [];
  const connections: Connection[] = [];
  
  // Start node
  nodes.push({
    id: 'start',
    taskId: 'start',
    position: { x: 400, y: 50 },
    type: 'start'
  });
  
  // Task nodes
  tasks.forEach((task, index) => {
    const nodeId = `node-${task.id}`;
    nodes.push({
      id: nodeId,
      taskId: task.id,
      position: { x: 400, y: 150 + (index * 120) },
      type: 'task'
    });
    
    // Connect to previous node
    const previousNodeId = index === 0 ? 'start' : `node-${tasks[index - 1].id}`;
    connections.push({
      id: `conn-${previousNodeId}-${nodeId}`,
      from: previousNodeId,
      to: nodeId,
      type: 'sequential'
    });
  });
  
  // End node
  const lastTaskNode = `node-${tasks[tasks.length - 1].id}`;
  nodes.push({
    id: 'end',
    taskId: 'end',
    position: { x: 400, y: 150 + (tasks.length * 120) },
    type: 'end'
  });
  
  connections.push({
    id: `conn-${lastTaskNode}-end`,
    from: lastTaskNode,
    to: 'end',
    type: 'sequential'
  });
  
  return {
    nodes,
    connections,
    layout: 'vertical'
  };
}

// Helper function to detect workflow issues
function detectWorkflowIssues(
  workflow: WorkflowStructure, 
  tasks: Task[]
): WorkflowIssue[] {
  const issues: WorkflowIssue[] = [];
  
  // Check for vague tasks
  tasks.forEach(task => {
    if (task.isVague) {
      issues.push({
        id: `vague-${task.id}`,
        type: 'vague_task',
        taskId: task.id,
        severity: 'warning',
        message: `"${task.name}" may be too vague - consider breaking it down`,
        suggestion: 'Click to expand this task into subtasks'
      });
    }
  });
  
  // Check for orphaned nodes (no connections)
  workflow.nodes.forEach(node => {
    if (node.type === 'task') {
      const hasIncoming = workflow.connections.some(c => c.to === node.id);
      const hasOutgoing = workflow.connections.some(c => c.from === node.id);
      
      if (!hasIncoming && !hasOutgoing) {
        issues.push({
          id: `orphan-${node.id}`,
          type: 'orphaned_task',
          taskId: node.taskId,
          severity: 'error',
          message: 'This task is not connected to the workflow',
          suggestion: 'Add dependencies or remove this task'
        });
      }
    }
  });
  
  return issues;
}
```

### 9.2 WorkflowCanvas Component (Simplified)

```tsx
// components/create-project/workflow-canvas.tsx

'use client';

import { useState, useRef, useEffect } from 'react';
import { ZoomIn, ZoomOut, Maximize2, Layout } from 'lucide-react';

interface WorkflowCanvasProps {
  workflow: WorkflowStructure;
  tasks: Task[];
  onWorkflowChange: (workflow: WorkflowStructure) => void;
  className?: string;
}

export function WorkflowCanvas({
  workflow,
  tasks,
  onWorkflowChange,
  className
}: WorkflowCanvasProps) {
  const canvasRef = useRef<SVGSVGElement>(null);
  const [zoom, setZoom] = useState(1.0);
  const [selectedNode, setSelectedNode] = useState<string | null>(null);
  const [draggingNode, setDraggingNode] = useState<string | null>(null);

  const handleNodeDragStart = (nodeId: string) => {
    setDraggingNode(nodeId);
  };

  const handleNodeDrag = (nodeId: string, newPosition: { x: number; y: number }) => {
    const updatedNodes = workflow.nodes.map(node =>
      node.id === nodeId ? { ...node, position: newPosition } : node
    );
    
    onWorkflowChange({
      ...workflow,
      nodes: updatedNodes
    });
  };

  const handleNodeDragEnd = () => {
    setDraggingNode(null);
  };

  const handleAutoLayout = () => {
    // Implement auto-layout algorithm
    const layoutedWorkflow = autoLayoutVertical(workflow, tasks);
    onWorkflowChange(layoutedWorkflow);
  };

  return (
    <div className={`relative ${className}`}>
      {/* Canvas Toolbar */}
      <div className="absolute top-4 right-4 z-10 flex gap-2 bg-white dark:bg-background-800 
                    rounded-lg shadow-md border border-background-300 dark:border-background-700 p-2">
        <button
          onClick={() => setZoom(prev => Math.min(prev + 0.1, 2.0))}
          className="p-2 hover:bg-background-100 dark:hover:bg-background-700 rounded transition-colors"
          title="Zoom In"
        >
          <ZoomIn className="w-4 h-4 text-text-primary dark:text-text-primary-dark" />
        </button>
        
        <button
          onClick={() => setZoom(prev => Math.max(prev - 0.1, 0.5))}
          className="p-2 hover:bg-background-100 dark:hover:bg-background-700 rounded transition-colors"
          title="Zoom Out"
        >
          <ZoomOut className="w-4 h-4 text-text-primary dark:text-text-primary-dark" />
        </button>
        
        <button
          onClick={() => setZoom(1.0)}
          className="p-2 hover:bg-background-100 dark:hover:bg-background-700 rounded transition-colors"
          title="Reset View"
        >
          <Maximize2 className="w-4 h-4 text-text-primary dark:text-text-primary-dark" />
        </button>
        
        <div className="w-px bg-background-300 dark:bg-background-700" />
        
        <button
          onClick={handleAutoLayout}
          className="p-2 hover:bg-background-100 dark:hover:bg-background-700 rounded transition-colors"
          title="Auto Layout"
        >
          <Layout className="w-4 h-4 text-text-primary dark:text-text-primary-dark" />
        </button>
      </div>

      {/* SVG Canvas */}
      <div className="bg-background-50 dark:bg-background-900 border border-background-300 
                    dark:border-background-700 rounded-xl overflow-hidden"
           style={{ height: '600px' }}>
        <svg
          ref={canvasRef}
          width="100%"
          height="100%"
          className="workflow-canvas"
        >
          <g transform={`scale(${zoom})`}>
            {/* Connection Lines Layer */}
            <g className="connections-layer">
              {workflow.connections.map(connection => (
                <ConnectionLine
                  key={connection.id}
                  connection={connection}
                  nodes={workflow.nodes}
                />
              ))}
            </g>
            
            {/* Task Nodes Layer */}
            <g className="nodes-layer">
              {workflow.nodes.map(node => (
                <TaskNode
                  key={node.id}
                  node={node}
                  task={tasks.find(t => t.id === node.taskId)}
                  isSelected={selectedNode === node.id}
                  isDragging={draggingNode === node.id}
                  onSelect={() => setSelectedNode(node.id)}
                  onDragStart={() => handleNodeDragStart(node.id)}
                  onDrag={(pos) => handleNodeDrag(node.id, pos)}
                  onDragEnd={handleNodeDragEnd}
                />
              ))}
            </g>
          </g>
        </svg>
      </div>
    </div>
  );
}

// Connection Line Component
function ConnectionLine({ connection, nodes }: any) {
  const fromNode = nodes.find((n: any) => n.id === connection.from);
  const toNode = nodes.find((n: any) => n.id === connection.to);
  
  if (!fromNode || !toNode) return null;
  
  const path = createCurvedPath(fromNode.position, toNode.position);
  
  return (
    <g>
      <path
        d={path}
        fill="none"
        stroke="var(--background-300)"
        strokeWidth="2"
        markerEnd="url(#arrowhead)"
        className="transition-all duration-200"
      />
      <defs>
        <marker
          id="arrowhead"
          markerWidth="10"
          markerHeight="10"
          refX="9"
          refY="3"
          orient="auto"
        >
          <polygon
            points="0 0, 10 3, 0 6"
            fill="var(--background-300)"
          />
        </marker>
      </defs>
    </g>
  );
}

// Task Node Component
function TaskNode({
  node,
  task,
  isSelected,
  isDragging,
  onSelect,
  onDragStart,
  onDrag,
  onDragEnd
}: any) {
  if (!task) return null;
  
  const getTaskColor = () => {
    switch (task.type) {
      case 'ai': return 'var(--glaucous)';
      case 'human': return 'var(--tea-green)';
      case 'hitl': return 'var(--orange-peel)';
      default: return 'var(--background-300)';
    }
  };
  
  return (
    <g
      transform={`translate(${node.position.x}, ${node.position.y})`}
      className={`task-node cursor-grab ${isDragging ? 'dragging' : ''}`}
      onClick={onSelect}
      onMouseDown={onDragStart}
      onMouseUp={onDragEnd}
    >
      <rect
        width="200"
        height="80"
        rx="8"
        fill={getTaskColor()}
        stroke={isSelected ? 'var(--bittersweet)' : 'transparent'}
        strokeWidth={isSelected ? '3' : '0'}
        className="transition-all duration-200"
      />
      
      <foreignObject width="200" height="80">
        <div className="p-3 h-full flex flex-col justify-between">
          <div className="text-white font-semibold text-sm truncate">
            {task.name}
          </div>
          
          <div className="flex justify-between items-center">
            <span className="text-xs text-white/80 uppercase tracking-wide">
              {task.type}
            </span>
            
            {task.isVague && (
              <span className="text-xs bg-bittersweet text-white px-2 py-1 rounded-full">
                ⚠️
              </span>
            )}
          </div>
        </div>
      </foreignObject>
    </g>
  );
}

function createCurvedPath(from: { x: number; y: number }, to: { x: number; y: number }) {
  const controlPoint1 = { x: from.x, y: from.y + (to.y - from.y) / 2 };
  const controlPoint2 = { x: to.x, y: from.y + (to.y - from.y) / 2 };
  
  return `M ${from.x},${from.y + 80} 
          C ${controlPoint1.x},${controlPoint1.y} 
            ${controlPoint2.x},${controlPoint2.y} 
            ${to.x},${to.y}`;
}

function autoLayoutVertical(workflow: WorkflowStructure, tasks: Task[]): WorkflowStructure {
  // Implement auto-layout algorithm here
  // This is a placeholder
  return workflow;
}
```

---

## Document Approval

**Author**: CollabFlow Development Team  
**Reviewed By**: [To be filled]  
**Approved By**: [To be filled]  
**Date**: October 27, 2025  

**Change Log**:
- v1.0 (Oct 27, 2025): Initial workflow review step specification

---

## Next Steps

1. **Review & Approval**: Stakeholder review of this specification
2. **Phased Implementation**: Begin with Phase 1 (basic display)
3. **User Testing**: Conduct usability testing after Phase 2
4. **Iteration**: Refine based on feedback
5. **Integration**: Connect with backend AI workflow generation
6. **Launch**: Deploy as part of updated project creation flow

---

**END OF DOCUMENT**
