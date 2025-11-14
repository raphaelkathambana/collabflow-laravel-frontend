# CollabFlow Flowchart Feature - Complete Design Specification

**Document Version:** 2.0  
**Date:** October 28, 2025  
**Project:** CollabFlow Adaptive Human-AI Workflow System  
**Feature:** Interactive Workflow Flowchart with Nested Subtasks  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Feature Overview](#2-feature-overview)
3. [Design Philosophy](#3-design-philosophy)
4. [User Experience Flow](#4-user-experience-flow)
5. [Technical Architecture](#5-technical-architecture)
6. [Smart Auto-Layout Algorithm](#6-smart-auto-layout-algorithm)
7. [Task & Subtask System](#7-task--subtask-system)
8. [Validation System](#8-validation-system)
9. [Theme Integration](#9-theme-integration)
10. [Component Specifications](#10-component-specifications)
11. [Interaction Patterns](#11-interaction-patterns)
12. [State Management](#12-state-management)
13. [Implementation Phases](#13-implementation-phases)
14. [Testing & Validation Criteria](#14-testing--validation-criteria)
15. [Appendix: Code Examples](#15-appendix-code-examples)

---

## 1. Executive Summary

### What This Feature Does

The CollabFlow Flowchart is an **interactive visual workflow editor** that allows users to:
- **Visualize** task dependencies and workflow structure
- **Validate** task definitions and workflow logic
- **Organize** tasks with intelligent auto-layout
- **Explore** nested subtask flowcharts for detailed planning
- **Edit** workflows through drag-and-drop and context menus
- **Preserve** custom layouts across sessions

### Key Innovation: Hierarchical Flowcharts

Unlike traditional flowcharts, CollabFlow supports **nested subtask flowcharts**:
```
Main Workflow (Project Level)
├─ Task 1: Research Phase [click to expand]
│  └─ Subtask Flowchart:
│     ├─ Market Analysis
│     ├─ Competitor Review
│     └─ User Interviews
├─ Task 2: Planning Phase [click to expand]
│  └─ Subtask Flowchart:
│     ├─ Define Requirements
│     └─ Create Timeline
└─ Task 3: Implementation Phase
```

### When & Where It Appears

**Project Creation Wizard (Step 4):**
```
Step 1: Details → Step 2: Goals → Step 3: AI Generation 
   ↓
Step 4: WORKFLOW REVIEW (Interactive Flowchart)
   ↓
Step 5: Final Review & Create
```

**Project View (Post-Creation):**
- **Workflow Tab**: Read-only flowchart showing saved layout
- **Click task node** → Opens subtask modal with nested flowchart

---

## 2. Feature Overview

### 2.1 Core Capabilities

| Capability | Description | User Benefit |
|------------|-------------|--------------|
| **Smart Auto-Layout** | Intelligent node positioning with branching support | Saves time, prevents overlaps |
| **Drag & Drop** | Manual node repositioning | Full control over layout |
| **Task Validation** | Real-time quality scoring (0-100) | Catches vague tasks early |
| **Workflow Validation** | Detects orphaned tasks, circular deps | Ensures logical flow |
| **Nested Flowcharts** | Subtask visualization within tasks | Deep planning without clutter |
| **Theme Adaptive** | Light/dark mode with muted colors | Consistent branding |
| **Layout Modes** | Horizontal or vertical flow | Adapts to content |
| **Non-Overlapping Lines** | Smart edge routing | Clear dependency paths |
| **Context Actions** | Right-click menus for editing | Power user shortcuts |
| **State Persistence** | Saves custom layouts | Respects manual edits |

### 2.2 User Personas & Use Cases

**Persona 1: Casual Creator (Sarah)**
- **Goal**: Quick project setup, trust AI
- **Usage**: Views flowchart (15 seconds), clicks "Looks Good"
- **Needs**: Auto-layout that works perfectly out-of-the-box

**Persona 2: Meticulous Planner (James)**
- **Goal**: Perfect workflow before starting
- **Usage**: Rearranges nodes, expands subtasks, adds dependencies
- **Needs**: Full editing power, validation feedback

**Persona 3: Team Lead (Maria)**
- **Goal**: Clear communication of project structure
- **Usage**: Shares flowchart with team, adds detailed subtasks
- **Needs**: Export, read-only view, nested detail

---

## 3. Design Philosophy

### 3.1 Core Principles

**1. Progressive Disclosure**
```
Level 1: See the flow (auto-generated)
   ↓
Level 2: Adjust the flow (drag nodes)
   ↓
Level 3: Detail the flow (expand subtasks)
   ↓
Level 4: Perfect the flow (validate & refine)
```

**2. Visual Hierarchy**
- **Primary**: Task nodes with type colors
- **Secondary**: Connection lines showing dependencies
- **Tertiary**: Validation badges and warnings
- **Hidden**: Subtask details (until clicked)

**3. Non-Intrusive Intelligence**
- AI auto-layout is a **starting point**, not a constraint
- Validation warnings are **suggestions**, not blockers
- Manual edits are **preserved** during regeneration

**4. Responsive Interaction**
- Drag operations: 60fps minimum
- Node selection: Instant feedback
- Layout recalculation: <500ms
- Theme transitions: Smooth (200ms)

### 3.2 Visual Language

**Node Shapes:**
```
┌─────────────────┐
│  Start/End      │  ← Circles/Ovals (special nodes)
└─────────────────┘

┌─────────────────┐
│ AI Task         │  ← Rounded rectangles (regular tasks)
│ Type: AI        │     200px × 100px
│ 8h | 3 subtasks │
└─────────────────┘
```

**Connection Lines:**
- **Sequential**: Solid lines with arrows
- **Parallel branches**: Multiple arrows from one node
- **Curved paths**: Bezier curves for smooth flow
- **Smart routing**: Never overlap nodes

**Color Semantics:**
- **Glaucous (Blue)**: AI-generated tasks
- **Tea Green**: Human-assigned tasks
- **Orange Peel**: Human-in-the-Loop checkpoints
- **Bittersweet (Red)**: Validation errors
- **Yellow**: Warnings

---

## 4. User Experience Flow

### 4.1 Project Creation Flow (Step 4)

```
┌─────────────────────────────────────────────────────────┐
│ Step 4: Review Your Workflow                   [4/5]    │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ AI organized your 8 tasks into a workflow.              │
│ Review the structure and make adjustments.              │
│                                                          │
│ ┌─────────────────────────────────────────────────┐    │
│ │  [Zoom Controls] [Layout: Horizontal ▼]         │    │
│ │  [Auto-Layout] [Regenerate Workflow]            │    │
│ └─────────────────────────────────────────────────┘    │
│                                                          │
│ ┌─────────────────────────────────────────────────┐    │
│ │                                                   │    │
│ │         [Interactive Flowchart Canvas]           │    │
│ │                                                   │    │
│ │  Start → Task 1 → Task 2 ──┬→ Task 3 → End      │    │
│ │                             └→ Task 4 ─┘         │    │
│ │                                                   │    │
│ └─────────────────────────────────────────────────┘    │
│                                                          │
│ ⚠️ 2 tasks need attention                               │
│    • "Setup infrastructure" is vague                    │
│    • Task 5 has no dependencies                         │
│                                                          │
│ [← Back]    [Looks Good →]                              │
└─────────────────────────────────────────────────────────┘
```

**User Scenarios:**

**Scenario A: Happy Path (15 seconds)**
1. User sees auto-generated flowchart
2. Scans the structure visually
3. Clicks "Looks Good →"
4. Proceeds to Step 5

**Scenario B: Minor Adjustment (60 seconds)**
1. User sees flowchart
2. Notices Task 3 should come before Task 2
3. Drags Task 3 to new position
4. Connections update automatically
5. Clicks "Looks Good →"

**Scenario C: Deep Engagement (3-5 minutes)**
1. User sees flowchart
2. Sees warning: "Setup infrastructure is vague"
3. Clicks task node → Details panel opens
4. Clicks "View Subtasks" → Modal opens
5. Sees/edits subtask flowchart
6. Adds 3 detailed subtasks
7. Closes modal, returns to main flowchart
8. Validation badge updates to ✓
9. Clicks "Looks Good →"

**Scenario D: Complete Regeneration (45 seconds)**
1. User sees flowchart
2. Doesn't like the structure
3. Clicks "Regenerate Workflow"
4. Confirms in modal: "Preserve manual edits?"
5. AI generates new structure
6. Reviews new flowchart
7. Clicks "Looks Good →"

### 4.2 Project View Flow (Post-Creation)

```
┌─────────────────────────────────────────────────────────┐
│ Project: Website Redesign                                │
│ [Overview] [Tasks] [Workflow] [Timeline] [Team]         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ ┌─────────────────────────────────────────────────┐    │
│ │         📊 Workflow Visualization                │    │
│ │                                                   │    │
│ │  [Zoom: 100%] [Export as PNG]                   │    │
│ │                                                   │    │
│ │  Start → Research → Planning ──┬→ Dev → End     │    │
│ │                                 └→ Design ─┘     │    │
│ │                                                   │    │
│ │  💡 Click any task to see subtasks              │    │
│ └─────────────────────────────────────────────────┘    │
│                                                          │
│ ⚡ This is a saved snapshot from project creation       │
│    Layout: Horizontal | Last edited: Oct 27, 2025      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

**Interaction:**
- **Read-only**: No dragging or editing
- **Click task node** → Opens subtask modal (read-only)
- **Export options**: PNG, PDF, TXT
- **Zoom/pan** still work for exploration

---

## 5. Technical Architecture

### 5.1 Technology Stack

**Core Libraries:**
```json
{
  "@xyflow/react": "^12.0.0",  // Professional flowchart rendering
  "react": "^18.3.0",
  "dagre": "^0.8.5",            // Graph layout algorithm
  "elkjs": "^0.9.0"             // Alternative layout engine
}
```

**Why React Flow?**
- ✅ Production-ready flowchart library
- ✅ Built-in drag-and-drop
- ✅ Pan, zoom, minimap
- ✅ Custom node types
- ✅ Smart edge routing
- ✅ Performance optimized (handles 100+ nodes)
- ✅ TypeScript support

### 5.2 Component Architecture

```
FlowchartContainer (Main)
├── FlowchartToolbar
│   ├── ZoomControls
│   ├── LayoutToggle (Horizontal/Vertical)
│   ├── AutoLayoutButton
│   └── RegenerateButton
├── ReactFlowCanvas
│   ├── CustomNodeTypes
│   │   ├── StartNode
│   │   ├── TaskNode
│   │   └── EndNode
│   ├── EdgeTypes (Custom arrows)
│   ├── Background (Grid/Dots)
│   ├── Controls (Zoom buttons)
│   └── MiniMap (Optional)
├── TaskDetailsPanel (Slide-in)
│   ├── TaskHeader
│   ├── TaskDescription
│   ├── ValidationSection
│   ├── SubtasksPreview
│   └── DependenciesDisplay
├── SubtaskModal (Full-screen)
│   ├── ModalHeader
│   ├── SubtaskFlowchart (Nested ReactFlow)
│   ├── AddSubtaskForm
│   └── SubtaskList (Edit/Delete)
├── ContextMenu (Right-click)
│   └── MenuItems (Edit, Delete, etc.)
├── ValidationPanel (Bottom)
│   └── IssuesList
└── WorkflowHealthIndicator
```

### 5.3 Data Model

**Task Interface:**
```typescript
interface Task {
  id: string;
  name: string;
  description: string;
  type: 'ai' | 'human' | 'hitl';
  estimated_hours: number;
  
  // Subtasks (recursive)
  subtasks?: Subtask[];
  
  // Dependencies
  dependencies?: string[]; // Array of task IDs
  
  // Validation
  validation?: {
    score: number; // 0-100
    issues: ValidationIssue[];
    lastChecked: Date;
  };
  
  // Position (for custom layouts)
  position?: { x: number; y: number };
  
  // Metadata
  created_at: Date;
  updated_at: Date;
  created_by: 'ai' | 'user';
}

interface Subtask {
  id: string;
  title: string;
  estimated_hours?: number;
  completed: boolean;
  order: number;
  
  // Recursive subtasks (for deep nesting)
  subtasks?: Subtask[];
  
  // Position in subtask flowchart
  position?: { x: number; y: number };
}

interface ValidationIssue {
  type: 'vague_name' | 'missing_description' | 
        'non_actionable' | 'missing_estimate' | 'too_large';
  severity: 'low' | 'medium' | 'high' | 'critical';
  message: string;
  suggestion?: string;
}
```

**Workflow State:**
```typescript
interface WorkflowState {
  // Layout configuration
  layout: 'vertical' | 'horizontal' | 'custom';
  direction: 'TB' | 'LR'; // Top-to-Bottom or Left-to-Right
  
  // Node positions
  nodePositions: Record<string, { x: number; y: number }>;
  
  // Subtask positions (nested)
  subtaskPositions: Record<string, Record<string, { x: number; y: number }>>;
  
  // View state
  zoom: number;
  panOffset: { x: number; y: number };
  
  // Flags
  isManualLayout: boolean; // True if user dragged nodes
  lastModified: Date;
  
  // Validation
  workflowValidation?: WorkflowValidation;
}

interface WorkflowValidation {
  score: number; // 0-100
  issues: WorkflowIssue[];
  isValid: boolean;
  lastChecked: Date;
}

interface WorkflowIssue {
  type: 'orphaned_tasks' | 'circular_dependency' | 
        'missing_hitl' | 'poorly_defined_tasks';
  severity: 'low' | 'medium' | 'high';
  count?: number;
  message: string;
  affectedTasks?: string[];
}
```

**React Flow Node:**
```typescript
import { Node as ReactFlowNode } from '@xyflow/react';

type FlowchartNode = ReactFlowNode<{
  task?: Task;
  label?: string; // For Start/End nodes
  type: 'start' | 'end' | 'task';
}>;
```

---

## 6. Smart Auto-Layout Algorithm

### 6.1 The Problem: Branching & Non-Overlapping

**Challenge:**
```
❌ Bad Layout (Overlapping):
Task 1 → Task 2 → Task 3
         Task 2 → Task 4 (overlaps Task 3)

✅ Good Layout (Branching):
                ┌→ Task 3
Task 1 → Task 2─┤
                └→ Task 4
```

**Requirements:**
1. **Detect branching**: When one task has multiple dependents
2. **Calculate levels**: Group tasks by dependency depth
3. **Space vertically**: Distribute branches without overlap
4. **Smart spacing**: More branches = more vertical space
5. **Center alignment**: Parent node centered between children

### 6.2 Algorithm Overview

**Step 1: Build Dependency Graph**
```typescript
function buildDependencyGraph(tasks: Task[]): DependencyGraph {
  const graph = new Map<string, string[]>();
  
  tasks.forEach(task => {
    graph.set(task.id, task.dependencies || []);
  });
  
  return graph;
}
```

**Step 2: Calculate Levels (Topological Sort)**
```typescript
function calculateLevels(tasks: Task[]): Map<number, Task[]> {
  const levels = new Map<number, Task[]>();
  const visited = new Set<string>();
  const inProgress = new Set<string>();
  
  function visit(task: Task, level: number) {
    if (visited.has(task.id)) return;
    if (inProgress.has(task.id)) {
      throw new Error('Circular dependency detected');
    }
    
    inProgress.add(task.id);
    
    // Visit dependencies first
    const deps = tasks.filter(t => task.dependencies?.includes(t.id));
    const maxDepLevel = deps.length > 0 
      ? Math.max(...deps.map(d => getLevelOf(d.id)))
      : -1;
    
    const taskLevel = maxDepLevel + 1;
    
    if (!levels.has(taskLevel)) {
      levels.set(taskLevel, []);
    }
    levels.get(taskLevel)!.push(task);
    
    visited.add(task.id);
    inProgress.delete(task.id);
  }
  
  tasks.forEach(task => visit(task, 0));
  
  return levels;
}
```

**Step 3: Detect Branching**
```typescript
function detectBranches(tasks: Task[]): Map<string, string[]> {
  const branches = new Map<string, string[]>();
  
  tasks.forEach(task => {
    const dependents = tasks.filter(t => 
      t.dependencies?.includes(task.id)
    );
    
    if (dependents.length > 1) {
      branches.set(task.id, dependents.map(d => d.id));
    }
  });
  
  return branches;
}
```

**Step 4: Calculate Positions**
```typescript
interface LayoutConfig {
  nodeWidth: number;
  nodeHeight: number;
  horizontalSpacing: number;
  verticalSpacing: number;
  branchSpacing: number;
}

function calculatePositions(
  tasks: Task[],
  direction: 'TB' | 'LR',
  config: LayoutConfig
): Map<string, { x: number; y: number }> {
  const positions = new Map();
  const levels = calculateLevels(tasks);
  const branches = detectBranches(tasks);
  
  if (direction === 'TB') {
    // Vertical layout (Top to Bottom)
    return calculateVerticalLayout(tasks, levels, branches, config);
  } else {
    // Horizontal layout (Left to Right)
    return calculateHorizontalLayout(tasks, levels, branches, config);
  }
}

function calculateHorizontalLayout(
  tasks: Task[],
  levels: Map<number, Task[]>,
  branches: Map<string, string[]>,
  config: LayoutConfig
): Map<string, { x: number; y: number }> {
  const positions = new Map();
  const { nodeWidth, nodeHeight, horizontalSpacing, verticalSpacing, branchSpacing } = config;
  
  let maxLevel = Math.max(...levels.keys());
  
  for (let level = 0; level <= maxLevel; level++) {
    const levelTasks = levels.get(level) || [];
    const x = level * (nodeWidth + horizontalSpacing);
    
    // Calculate total height needed for this level
    let totalHeight = (levelTasks.length - 1) * (nodeHeight + verticalSpacing);
    
    // Adjust for branches
    levelTasks.forEach(task => {
      if (branches.has(task.id)) {
        const branchCount = branches.get(task.id)!.length;
        totalHeight += (branchCount - 1) * branchSpacing;
      }
    });
    
    // Center the level vertically
    let currentY = -totalHeight / 2;
    
    levelTasks.forEach((task, index) => {
      positions.set(task.id, { x, y: currentY });
      
      const nextSpacing = branches.has(task.id) 
        ? branchSpacing 
        : verticalSpacing;
      
      currentY += nodeHeight + nextSpacing;
    });
  }
  
  return positions;
}

function calculateVerticalLayout(
  tasks: Task[],
  levels: Map<number, Task[]>,
  branches: Map<string, string[]>,
  config: LayoutConfig
): Map<string, { x: number; y: number }> {
  const positions = new Map();
  const { nodeWidth, nodeHeight, horizontalSpacing, verticalSpacing, branchSpacing } = config;
  
  let maxLevel = Math.max(...levels.keys());
  
  for (let level = 0; level <= maxLevel; level++) {
    const levelTasks = levels.get(level) || [];
    const y = level * (nodeHeight + verticalSpacing);
    
    // Calculate total width needed for this level
    let totalWidth = (levelTasks.length - 1) * (nodeWidth + horizontalSpacing);
    
    // Adjust for branches
    levelTasks.forEach(task => {
      if (branches.has(task.id)) {
        const branchCount = branches.get(task.id)!.length;
        totalWidth += (branchCount - 1) * branchSpacing;
      }
    });
    
    // Center the level horizontally
    let currentX = -totalWidth / 2;
    
    levelTasks.forEach((task, index) => {
      positions.set(task.id, { x: currentX, y });
      
      const nextSpacing = branches.has(task.id) 
        ? branchSpacing 
        : horizontalSpacing;
      
      currentX += nodeWidth + nextSpacing;
    });
  }
  
  return positions;
}
```

### 6.3 Using Dagre for Advanced Layout

For more complex layouts, integrate **Dagre** (directed graph layout library):

```typescript
import dagre from 'dagre';

function layoutWithDagre(
  tasks: Task[],
  direction: 'TB' | 'LR'
): Map<string, { x: number; y: number }> {
  const dagreGraph = new dagre.graphlib.Graph();
  dagreGraph.setDefaultEdgeLabel(() => ({}));
  
  dagreGraph.setGraph({
    rankdir: direction,
    nodesep: 150,
    ranksep: 100,
    edgesep: 50,
  });
  
  // Add nodes
  tasks.forEach(task => {
    dagreGraph.setNode(task.id, {
      width: 200,
      height: 100,
    });
  });
  
  // Add edges
  tasks.forEach(task => {
    task.dependencies?.forEach(depId => {
      dagreGraph.setEdge(depId, task.id);
    });
  });
  
  // Calculate layout
  dagre.layout(dagreGraph);
  
  // Extract positions
  const positions = new Map();
  dagreGraph.nodes().forEach(nodeId => {
    const node = dagreGraph.node(nodeId);
    positions.set(nodeId, {
      x: node.x,
      y: node.y,
    });
  });
  
  return positions;
}
```

### 6.4 Non-Overlapping Edge Routing

**React Flow** automatically handles edge routing, but for custom control:

```typescript
import { Position } from '@xyflow/react';

function getEdgeParams(source: Node, target: Node) {
  const sourceX = source.position.x + source.width / 2;
  const sourceY = source.position.y + source.height;
  const targetX = target.position.x + target.width / 2;
  const targetY = target.position.y;
  
  return {
    sourceX,
    sourceY,
    targetX,
    targetY,
    sourcePosition: Position.Bottom,
    targetPosition: Position.Top,
  };
}

// Custom edge with smart routing
function SmartEdge({ source, target, ...props }) {
  const sourceNode = useNode(source);
  const targetNode = useNode(target);
  
  if (!sourceNode || !targetNode) return null;
  
  const params = getEdgeParams(sourceNode, targetNode);
  
  // Use smooth step or bezier curve
  return (
    <BezierEdge
      {...props}
      sourceX={params.sourceX}
      sourceY={params.sourceY}
      targetX={params.targetX}
      targetY={params.targetY}
      sourcePosition={params.sourcePosition}
      targetPosition={params.targetPosition}
    />
  );
}
```

---

## 7. Task & Subtask System

### 7.1 Subtask Hierarchy

**Concept:**
Every task can have subtasks. Each subtask can have its own subtasks (recursive).

```
Task: Design Homepage
├─ Subtask: Create Wireframe
│  ├─ Sub-subtask: Sketch layout
│  └─ Sub-subtask: Get feedback
├─ Subtask: Design Hero Section
└─ Subtask: Design Footer
```

**UI Representation:**
- **Main Flowchart**: Shows only top-level tasks
- **Task Node**: Shows subtask count badge (e.g., "3 subtasks")
- **Subtask Modal**: Full flowchart of subtasks (nested React Flow)

### 7.2 Subtask Modal Design

**When Opened:**
- Click task node in main flowchart
- Or click "View Subtasks" in details panel

**Modal Layout:**
```
┌────────────────────────────────────────────────────────┐
│ Task: Design Homepage                           [Close] │
├────────────────────────────────────────────────────────┤
│                                                         │
│ ┌─────────────────────────────────────────────────┐   │
│ │  Subtask Flowchart (Nested React Flow)          │   │
│ │                                                   │   │
│ │  Start → Wireframe → Hero → Footer → End        │   │
│ │                                                   │   │
│ └─────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─────────────────────────────────────────────────┐   │
│ │ Add New Subtask:                                 │   │
│ │ [Text Input________________] [Add Subtask]       │   │
│ └─────────────────────────────────────────────────┘   │
│                                                         │
│ Existing Subtasks:                                     │
│ ┌──────────────────────────────────────────┐          │
│ │ 1. Create Wireframe         [Edit] [Del]  │          │
│ │ 2. Design Hero Section      [Edit] [Del]  │          │
│ │ 3. Design Footer            [Edit] [Del]  │          │
│ └──────────────────────────────────────────┘          │
│                                                         │
│                                    [Save & Close]       │
└────────────────────────────────────────────────────────┘
```

**Features:**
1. **Nested Flowchart**: Visual representation of subtask flow
2. **Add Subtask**: Quick inline form
3. **Edit/Delete**: Manage existing subtasks
4. **Drag in Flowchart**: Rearrange subtask order
5. **Auto-Save**: Positions saved when closing

### 7.3 Subtask Data Flow

**Component Structure:**
```tsx
<SubtaskModal
  task={selectedTask}
  onClose={() => setIsModalOpen(false)}
  onUpdateSubtasks={(taskId, subtasks) => {
    updateTask(taskId, { subtasks });
  }}
  onUpdateSubtaskPositions={(taskId, positions) => {
    updateWorkflowState({
      subtaskPositions: {
        ...workflowState.subtaskPositions,
        [taskId]: positions
      }
    });
  }}
/>
```

**State Management:**
```typescript
// In parent component
const [subtaskPositions, setSubtaskPositions] = useState<
  Record<string, Record<string, { x: number; y: number }>>
>({});

// When modal closes
const handleSaveSubtaskPositions = (
  taskId: string, 
  positions: Record<string, { x: number; y: number }>
) => {
  setSubtaskPositions(prev => ({
    ...prev,
    [taskId]: positions
  }));
  
  // Persist to backend
  saveWorkflowState({
    ...workflowState,
    subtaskPositions: {
      ...workflowState.subtaskPositions,
      [taskId]: positions
    }
  });
};
```

### 7.4 Subtask Validation

Subtasks inherit validation from parent task:

```typescript
function validateSubtasks(task: Task): SubtaskValidation {
  if (!task.subtasks || task.subtasks.length === 0) {
    return { score: 100, issues: [] };
  }
  
  const issues: ValidationIssue[] = [];
  let score = 100;
  
  // Check if subtasks are well-defined
  task.subtasks.forEach(subtask => {
    if (subtask.title.length < 3) {
      issues.push({
        type: 'vague_name',
        severity: 'medium',
        message: `Subtask "${subtask.title}" is too short`
      });
      score -= 10;
    }
    
    if (!subtask.estimated_hours) {
      issues.push({
        type: 'missing_estimate',
        severity: 'low',
        message: `Subtask "${subtask.title}" has no time estimate`
      });
      score -= 5;
    }
  });
  
  // Check if subtask total exceeds parent estimate
  const subtaskTotal = task.subtasks.reduce(
    (sum, st) => sum + (st.estimated_hours || 0), 
    0
  );
  
  if (subtaskTotal > task.estimated_hours * 1.2) {
    issues.push({
      type: 'time_mismatch',
      severity: 'high',
      message: 'Subtasks exceed parent task estimate by >20%'
    });
    score -= 20;
  }
  
  return { score: Math.max(0, score), issues };
}
```

---

## 8. Validation System

### 8.1 Task Validation Criteria

**Logical Criteria for Well-Defined Tasks:**

| Criterion | Weight | Pass Condition | Fail Impact |
|-----------|--------|----------------|-------------|
| **Name Clarity** | 30% | ≥3 words, descriptive | Vague, generic |
| **Description** | 25% | ≥20 characters, detailed | Missing or too short |
| **Actionable Verb** | 20% | Starts with action verb | Noun phrase only |
| **Time Estimate** | 15% | Has realistic hours | Missing or 0 |
| **Scope** | 10% | ≤40 hours | Too large (epic) |

**Validation Scoring:**
```
Score Ranges:
- 90-100: Excellent (✓ Green)
- 70-89:  Good (✓ Light Green)
- 50-69:  Needs Work (⚠️ Yellow)
- 0-49:   Poor (✗ Red)
```

**Action Verb List:**
```typescript
const ACTION_VERBS = [
  'create', 'design', 'implement', 'develop', 'build',
  'test', 'deploy', 'configure', 'setup', 'install',
  'review', 'analyze', 'research', 'document', 'write',
  'refactor', 'optimize', 'debug', 'fix', 'update',
  'migrate', 'integrate', 'validate', 'verify', 'monitor'
];
```

### 8.2 Task Validation Code

```typescript
interface TaskValidation {
  score: number; // 0-100
  issues: ValidationIssue[];
  suggestions: string[];
}

function validateTask(task: Task): TaskValidation {
  const issues: ValidationIssue[] = [];
  const suggestions: string[] = [];
  let score = 100;
  
  // 1. Name Clarity (30 points)
  const nameWords = task.name.trim().split(/\s+/);
  if (nameWords.length < 3) {
    issues.push({
      type: 'vague_name',
      severity: 'high',
      message: 'Task name is too short or vague',
    });
    suggestions.push(
      `Instead of "${task.name}", try adding more detail like "Design homepage hero section layout"`
    );
    score -= 30;
  }
  
  // 2. Description Completeness (25 points)
  if (!task.description || task.description.length < 20) {
    issues.push({
      type: 'missing_description',
      severity: 'medium',
      message: 'Task lacks detailed description',
    });
    suggestions.push(
      'Add a description explaining what this task involves and its expected outcome'
    );
    score -= 25;
  }
  
  // 3. Actionable Verb (20 points)
  const hasActionVerb = ACTION_VERBS.some(verb =>
    task.name.toLowerCase().startsWith(verb)
  );
  
  if (!hasActionVerb) {
    issues.push({
      type: 'non_actionable',
      severity: 'medium',
      message: 'Task name should start with an action verb',
    });
    suggestions.push(
      `Start with an action verb like: "Create ${task.name}" or "Implement ${task.name}"`
    );
    score -= 20;
  }
  
  // 4. Time Estimate (15 points)
  if (!task.estimated_hours || task.estimated_hours <= 0) {
    issues.push({
      type: 'missing_estimate',
      severity: 'low',
      message: 'Task needs a time estimate',
    });
    suggestions.push('Add estimated hours to help with planning');
    score -= 15;
  } else if (task.estimated_hours < 0.5) {
    issues.push({
      type: 'too_small',
      severity: 'low',
      message: 'Task estimate is very small - is this necessary?',
    });
    score -= 5;
  }
  
  // 5. Appropriate Scope (10 points)
  if (task.estimated_hours && task.estimated_hours > 40) {
    issues.push({
      type: 'too_large',
      severity: 'high',
      message: 'Task scope is too large (>40 hours)',
      suggestion: 'Consider breaking this into smaller subtasks',
    });
    suggestions.push(
      'Tasks over 40 hours should be split into 3-5 subtasks of 8-12 hours each'
    );
    score -= 10;
  }
  
  // Bonus: Check for subtasks on large tasks
  if (
    task.estimated_hours > 20 &&
    (!task.subtasks || task.subtasks.length === 0)
  ) {
    suggestions.push(
      'This is a large task. Consider adding subtasks for better tracking.'
    );
  }
  
  return {
    score: Math.max(0, score),
    issues,
    suggestions,
  };
}
```

### 8.3 Workflow Validation Criteria

**Logical Criteria for Valid Workflows:**

| Criterion | Severity | Description |
|-----------|----------|-------------|
| **No Orphaned Tasks** | Medium | All tasks must have dependencies (except first task) |
| **No Circular Dependencies** | Critical | Tasks cannot depend on each other in a loop |
| **HITL Checkpoints** | Low | AI tasks should have human review points |
| **Well-Defined Tasks** | High | Tasks must meet minimum quality score (50+) |
| **Reasonable Task Count** | Low | 5-20 tasks ideal, <50 maximum |
| **Balanced Distribution** | Low | Mix of AI/Human/HITL tasks |

### 8.4 Workflow Validation Code

```typescript
interface WorkflowValidation {
  score: number; // 0-100
  issues: WorkflowIssue[];
  isValid: boolean;
}

function validateWorkflow(tasks: Task[]): WorkflowValidation {
  const issues: WorkflowIssue[] = [];
  let score = 100;
  
  // 1. Check for orphaned tasks (15 points)
  const tasksWithDeps = new Set<string>();
  tasks.forEach(task => {
    task.dependencies?.forEach(dep => tasksWithDeps.add(dep));
  });
  
  const orphanedTasks = tasks.filter(
    task => !tasksWithDeps.has(task.id) && 
           task.dependencies?.length === 0
  );
  
  if (orphanedTasks.length > 0) {
    issues.push({
      type: 'orphaned_tasks',
      severity: 'medium',
      count: orphanedTasks.length,
      message: `${orphanedTasks.length} task(s) have no dependencies`,
      affectedTasks: orphanedTasks.map(t => t.id),
    });
    score -= 15;
  }
  
  // 2. Check for circular dependencies (CRITICAL)
  const cycles = detectCircularDependencies(tasks);
  if (cycles.length > 0) {
    issues.push({
      type: 'circular_dependency',
      severity: 'high',
      message: 'Circular dependency detected',
      affectedTasks: cycles[0], // First cycle
    });
    score -= 40; // Heavy penalty
  }
  
  // 3. Check for HITL checkpoints (10 points)
  const aiTasks = tasks.filter(t => t.type === 'ai');
  const hitlTasks = tasks.filter(t => t.type === 'hitl');
  
  if (aiTasks.length > 0 && hitlTasks.length === 0) {
    issues.push({
      type: 'missing_hitl',
      severity: 'low',
      message: 'Consider adding human review points for AI work',
    });
    score -= 10;
  }
  
  // 4. Check task quality (20 points)
  const poorlyDefinedTasks = tasks.filter(t => {
    const validation = validateTask(t);
    return validation.score < 50;
  });
  
  if (poorlyDefinedTasks.length > 0) {
    issues.push({
      type: 'poorly_defined_tasks',
      severity: 'high',
      count: poorlyDefinedTasks.length,
      message: `${poorlyDefinedTasks.length} task(s) need better definition`,
      affectedTasks: poorlyDefinedTasks.map(t => t.id),
    });
    score -= 20;
  }
  
  // 5. Check task count (5 points)
  if (tasks.length < 3) {
    issues.push({
      type: 'too_few_tasks',
      severity: 'low',
      message: 'Very few tasks - consider more detailed breakdown',
    });
    score -= 5;
  } else if (tasks.length > 50) {
    issues.push({
      type: 'too_many_tasks',
      severity: 'medium',
      message: 'Too many tasks - consider grouping or milestones',
    });
    score -= 10;
  }
  
  // 6. Check task distribution (10 points)
  const humanTasks = tasks.filter(t => t.type === 'human');
  if (aiTasks.length === 0 && humanTasks.length > 0) {
    issues.push({
      type: 'no_automation',
      severity: 'low',
      message: 'Consider automating some tasks with AI',
    });
    score -= 5;
  }
  
  return {
    score: Math.max(0, score),
    issues,
    isValid: score >= 50 && cycles.length === 0,
  };
}

function detectCircularDependencies(tasks: Task[]): string[][] {
  const cycles: string[][] = [];
  const visited = new Set<string>();
  const recursionStack = new Set<string>();
  
  function dfs(taskId: string, path: string[]): boolean {
    visited.add(taskId);
    recursionStack.add(taskId);
    path.push(taskId);
    
    const task = tasks.find(t => t.id === taskId);
    if (!task) return false;
    
    for (const depId of task.dependencies || []) {
      if (!visited.has(depId)) {
        if (dfs(depId, path)) return true;
      } else if (recursionStack.has(depId)) {
        // Cycle detected
        const cycleStart = path.indexOf(depId);
        cycles.push(path.slice(cycleStart));
        return true;
      }
    }
    
    recursionStack.delete(taskId);
    path.pop();
    return false;
  }
  
  tasks.forEach(task => {
    if (!visited.has(task.id)) {
      dfs(task.id, []);
    }
  });
  
  return cycles;
}
```

---

## 9. Theme Integration

### 9.1 Color System

**CollabFlow Theme Colors (with Muted Variants):**

```typescript
const THEME_COLORS = {
  light: {
    ai: {
      bg: 'rgba(92, 128, 188, 0.12)',      // Glaucous 12% opacity
      bgHover: 'rgba(92, 128, 188, 0.20)',  // 20% on hover
      border: 'rgba(92, 128, 188, 0.85)',   // 85% opacity
      text: '#3b5b91',                      // Darker shade
      icon: '#5C80BC'                       // Full color
    },
    human: {
      bg: 'rgba(196, 214, 176, 0.12)',
      bgHover: 'rgba(196, 214, 176, 0.20)',
      border: 'rgba(196, 214, 176, 0.85)',
      text: '#316837',
      icon: '#C4D6B0'
    },
    hitl: {
      bg: 'rgba(255, 159, 28, 0.12)',
      bgHover: 'rgba(255, 159, 28, 0.20)',
      border: 'rgba(255, 159, 28, 0.85)',
      text: '#995900',
      icon: '#FF9F1C'
    },
    canvas: {
      bg: '#FAFBFC',
      grid: '#E8EDF2',
      border: '#D1DBE5'
    },
    text: {
      primary: '#1A1F26',
      secondary: '#4A5568',
      muted: '#A0AEC0'
    },
    validation: {
      excellent: '#10B981',  // Green
      good: '#84CC16',       // Light green
      warning: '#F59E0B',    // Yellow
      error: '#EF4444'       // Red
    }
  },
  dark: {
    ai: {
      bg: 'rgba(92, 128, 188, 0.15)',
      bgHover: 'rgba(92, 128, 188, 0.25)',
      border: 'rgba(92, 128, 188, 0.90)',
      text: '#92aad3',
      icon: '#7BA3F5'
    },
    human: {
      bg: 'rgba(196, 214, 176, 0.15)',
      bgHover: 'rgba(196, 214, 176, 0.25)',
      border: 'rgba(196, 214, 176, 0.90)',
      text: '#99cc99',
      icon: '#A8E890'
    },
    hitl: {
      bg: 'rgba(255, 159, 28, 0.15)',
      bgHover: 'rgba(255, 159, 28, 0.25)',
      border: 'rgba(255, 159, 28, 0.90)',
      text: '#ffbf66',
      icon: '#FFB84D'
    },
    canvas: {
      bg: '#0F1419',
      grid: '#1A1F26',
      border: '#2A3038'
    },
    text: {
      primary: '#F5F7F9',
      secondary: '#A0AEC0',
      muted: '#6B7280'
    },
    validation: {
      excellent: '#34D399',
      good: '#A3E635',
      warning: '#FBBF24',
      error: '#F87171'
    }
  }
};
```

### 9.2 Custom Node Components

**Task Node with Theme:**

```tsx
import { Handle, Position, NodeProps } from '@xyflow/react';
import { useTheme } from '../contexts/ThemeContext';
import { CheckCircle, AlertCircle, XCircle, MoreHorizontal } from 'lucide-react';

interface TaskNodeData {
  task: Task;
}

export function TaskNode({ data, selected }: NodeProps<TaskNodeData>) {
  const { task } = data;
  const { theme } = useTheme();
  const colors = THEME_COLORS[theme];
  const taskColors = colors[task.type];
  const validation = validateTask(task);
  
  const getValidationIcon = () => {
    if (validation.score >= 80) return <CheckCircle className="w-4 h-4" style={{ color: colors.validation.excellent }} />;
    if (validation.score >= 50) return <AlertCircle className="w-4 h-4" style={{ color: colors.validation.warning }} />;
    return <XCircle className="w-4 h-4" style={{ color: colors.validation.error }} />;
  };
  
  return (
    <div
      className="task-node"
      style={{
        width: '200px',
        minHeight: '100px',
        backgroundColor: selected ? taskColors.bgHover : taskColors.bg,
        border: `2px solid ${selected ? '#EB5E55' : taskColors.border}`,
        borderRadius: '8px',
        padding: '12px',
        cursor: 'pointer',
        transition: 'all 0.2s ease-in-out',
      }}
    >
      <Handle type="target" position={Position.Top} />
      
      <div className="flex items-start justify-between mb-2">
        <span
          className="px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider"
          style={{
            backgroundColor: taskColors.icon,
            color: 'white'
          }}
        >
          {task.type}
        </span>
        {getValidationIcon()}
      </div>
      
      <p
        className="text-sm font-semibold leading-tight mb-2"
        style={{ color: theme === 'dark' ? colors.text.primary : taskColors.text }}
      >
        {task.name}
      </p>
      
      <div className="flex items-center justify-between text-xs" style={{ color: colors.text.secondary }}>
        <span>{task.estimated_hours}h</span>
        {task.subtasks && task.subtasks.length > 0 && (
          <span className="flex items-center gap-1">
            <MoreHorizontal className="w-3 h-3" />
            {task.subtasks.length}
          </span>
        )}
      </div>
      
      <Handle type="source" position={Position.Bottom} />
    </div>
  );
}
```

**Start/End Nodes:**

```tsx
export function StartNode() {
  const { theme } = useTheme();
  const colors = THEME_COLORS[theme];
  
  return (
    <div
      className="flex items-center justify-center"
      style={{
        width: '100px',
        height: '50px',
        backgroundColor: colors.canvas.bg,
        border: `2px solid ${colors.canvas.border}`,
        borderRadius: '25px',
        color: colors.text.primary,
        fontWeight: '600',
        fontSize: '14px',
      }}
    >
      Start
      <Handle type="source" position={Position.Bottom} />
    </div>
  );
}

export function EndNode() {
  const { theme } = useTheme();
  const colors = THEME_COLORS[theme];
  
  return (
    <div
      className="flex items-center justify-center"
      style={{
        width: '100px',
        height: '50px',
        backgroundColor: colors.canvas.bg,
        border: `2px solid ${colors.canvas.border}`,
        borderRadius: '25px',
        color: colors.text.primary,
        fontWeight: '600',
        fontSize: '14px',
      }}
    >
      End
      <Handle type="target" position={Position.Top} />
    </div>
  );
}
```

### 9.3 Canvas Styling

```tsx
import { ReactFlow, Background, Controls, MiniMap } from '@xyflow/react';

function FlowchartCanvas() {
  const { theme } = useTheme();
  const colors = THEME_COLORS[theme];
  
  return (
    <div
      className="flowchart-container"
      style={{
        height: '600px',
        backgroundColor: colors.canvas.bg,
        border: `1px solid ${colors.canvas.border}`,
        borderRadius: '12px',
        overflow: 'hidden',
      }}
    >
      <ReactFlow
        nodes={nodes}
        edges={edges}
        nodeTypes={nodeTypes}
        onNodesChange={onNodesChange}
        onEdgesChange={onEdgesChange}
        onConnect={onConnect}
        fitView
      >
        <Background
          color={colors.canvas.grid}
          gap={20}
          size={1}
          variant="dots"
        />
        <Controls
          style={{
            backgroundColor: theme === 'dark' ? '#1A1F26' : '#FFFFFF',
            border: `1px solid ${colors.canvas.border}`,
          }}
        />
        <MiniMap
          nodeColor={(node) => {
            if (node.type === 'task') {
              const task = (node.data as TaskNodeData).task;
              return colors[task.type].icon;
            }
            return colors.canvas.border;
          }}
          style={{
            backgroundColor: theme === 'dark' ? '#1A1F26' : '#FFFFFF',
            border: `1px solid ${colors.canvas.border}`,
          }}
        />
      </ReactFlow>
    </div>
  );
}
```

---

## 10. Component Specifications

### 10.1 Main Flowchart Container

```tsx
interface FlowchartContainerProps {
  tasks: Task[];
  workflowState: WorkflowState;
  onUpdateTasks: (tasks: Task[]) => void;
  onUpdateWorkflowState: (state: WorkflowState) => void;
  onContinue: () => void;
  onBack: () => void;
  onRegenerate: () => Promise<void>;
  isCreationMode: boolean; // True in wizard, false in project view
}

export function FlowchartContainer({
  tasks,
  workflowState,
  onUpdateTasks,
  onUpdateWorkflowState,
  onContinue,
  onBack,
  onRegenerate,
  isCreationMode,
}: FlowchartContainerProps) {
  const [selectedTask, setSelectedTask] = useState<Task | null>(null);
  const [showSubtaskModal, setShowSubtaskModal] = useState(false);
  const [contextMenu, setContextMenu] = useState<ContextMenuState | null>(null);
  
  // React Flow state
  const [nodes, setNodes] = useState<Node[]>([]);
  const [edges, setEdges] = useState<Edge[]>([]);
  
  // Initialize from tasks
  useEffect(() => {
    const { flowNodes, flowEdges } = convertTasksToFlow(
      tasks,
      workflowState
    );
    setNodes(flowNodes);
    setEdges(flowEdges);
  }, [tasks, workflowState]);
  
  // Handle node changes (drag, select, etc.)
  const onNodesChange = useCallback(
    (changes: NodeChange[]) => {
      setNodes((nds) => applyNodeChanges(changes, nds));
      
      // Update workflow state with new positions
      const positionChanges = changes.filter(
        (c) => c.type === 'position' && c.position
      );
      if (positionChanges.length > 0) {
        const newPositions = { ...workflowState.nodePositions };
        positionChanges.forEach((change) => {
          if (change.type === 'position' && change.position) {
            newPositions[change.id] = change.position;
          }
        });
        onUpdateWorkflowState({
          ...workflowState,
          nodePositions: newPositions,
          isManualLayout: true,
        });
      }
    },
    [workflowState, onUpdateWorkflowState]
  );
  
  const onEdgesChange = useCallback(
    (changes: EdgeChange[]) => {
      setEdges((eds) => applyEdgeChanges(changes, eds));
    },
    []
  );
  
  const onConnect = useCallback(
    (connection: Connection) => {
      // User manually connected two nodes
      const newEdge = {
        id: `${connection.source}-${connection.target}`,
        source: connection.source!,
        target: connection.target!,
        animated: true,
      };
      setEdges((eds) => [...eds, newEdge]);
      
      // Update task dependencies
      const updatedTasks = tasks.map((task) => {
        if (task.id === connection.target) {
          return {
            ...task,
            dependencies: [
              ...(task.dependencies || []),
              connection.source!,
            ],
          };
        }
        return task;
      });
      onUpdateTasks(updatedTasks);
    },
    [tasks, onUpdateTasks]
  );
  
  // Handle node click
  const handleNodeClick = useCallback(
    (event: React.MouseEvent, node: Node) => {
      if (node.type === 'task') {
        const task = tasks.find((t) => t.id === node.id);
        if (task) {
          setSelectedTask(task);
        }
      }
    },
    [tasks]
  );
  
  // Handle auto-layout
  const handleAutoLayout = useCallback(() => {
    const newPositions = layoutWithDagre(
      tasks,
      workflowState.direction
    );
    
    onUpdateWorkflowState({
      ...workflowState,
      nodePositions: Object.fromEntries(newPositions),
      isManualLayout: false,
    });
  }, [tasks, workflowState, onUpdateWorkflowState]);
  
  return (
    <div className="flowchart-container">
      <FlowchartToolbar
        zoom={workflowState.zoom}
        layout={workflowState.layout}
        onZoomIn={() => {/* zoom in */}}
        onZoomOut={() => {/* zoom out */}}
        onZoomReset={() => {/* reset */}}
        onLayoutToggle={() => {/* toggle */}}
        onAutoLayout={handleAutoLayout}
        onRegenerate={onRegenerate}
        isCreationMode={isCreationMode}
      />
      
      <ReactFlowProvider>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          nodeTypes={{
            start: StartNode,
            end: EndNode,
            task: TaskNode,
          }}
          onNodesChange={onNodesChange}
          onEdgesChange={onEdgesChange}
          onConnect={onConnect}
          onNodeClick={handleNodeClick}
          onNodeContextMenu={(e, node) => {
            e.preventDefault();
            if (node.type === 'task') {
              setContextMenu({
                x: e.clientX,
                y: e.clientY,
                taskId: node.id,
              });
            }
          }}
          fitView
          defaultViewport={{ x: 0, y: 0, zoom: 1 }}
        >
          <Background />
          <Controls />
          <MiniMap />
        </ReactFlow>
      </ReactFlowProvider>
      
      {selectedTask && (
        <TaskDetailsPanel
          task={selectedTask}
          onClose={() => setSelectedTask(null)}
          onViewSubtasks={() => setShowSubtaskModal(true)}
        />
      )}
      
      {showSubtaskModal && selectedTask && (
        <SubtaskModal
          task={selectedTask}
          onClose={() => setShowSubtaskModal(false)}
          onUpdateSubtasks={(subtasks) => {
            onUpdateTasks(
              tasks.map((t) =>
                t.id === selectedTask.id ? { ...t, subtasks } : t
              )
            );
          }}
          onUpdateSubtaskPositions={(positions) => {
            onUpdateWorkflowState({
              ...workflowState,
              subtaskPositions: {
                ...workflowState.subtaskPositions,
                [selectedTask.id]: positions,
              },
            });
          }}
        />
      )}
      
      {contextMenu && (
        <ContextMenu
          x={contextMenu.x}
          y={contextMenu.y}
          task={tasks.find((t) => t.id === contextMenu.taskId)!}
          onClose={() => setContextMenu(null)}
          onAction={(action) => {
            // Handle context menu actions
            console.log('Action:', action);
            setContextMenu(null);
          }}
        />
      )}
      
      {isCreationMode && (
        <div className="flex justify-between mt-6">
          <button onClick={onBack} className="btn-secondary">
            ← Back
          </button>
          <button onClick={onContinue} className="btn-primary">
            Looks Good →
          </button>
        </div>
      )}
    </div>
  );
}
```

### 10.2 Subtask Modal Component

```tsx
interface SubtaskModalProps {
  task: Task;
  onClose: () => void;
  onUpdateSubtasks: (subtasks: Subtask[]) => void;
  onUpdateSubtaskPositions: (positions: Record<string, { x: number; y: number }>) => void;
}

export function SubtaskModal({
  task,
  onClose,
  onUpdateSubtasks,
  onUpdateSubtaskPositions,
}: SubtaskModalProps) {
  const [localSubtasks, setLocalSubtasks] = useState<Subtask[]>(task.subtasks || []);
  const [nodes, setNodes] = useState<Node[]>([]);
  const [edges, setEdges] = useState<Edge[]>([]);
  const [newSubtaskTitle, setNewSubtaskTitle] = useState('');
  const [nodePositions, setNodePositions] = useState<Record<string, { x: number; y: number }>>({});
  
  // Initialize flowchart from subtasks
  useEffect(() => {
    const { flowNodes, flowEdges } = convertSubtasksToFlow(localSubtasks);
    setNodes(flowNodes);
    setEdges(flowEdges);
    
    // Initialize positions
    const positions: Record<string, { x: number; y: number }> = {};
    flowNodes.forEach((node) => {
      positions[node.id] = node.position;
    });
    setNodePositions(positions);
  }, [localSubtasks]);
  
  const handleAddSubtask = () => {
    if (!newSubtaskTitle.trim()) return;
    
    const newSubtask: Subtask = {
      id: `subtask-${Date.now()}`,
      title: newSubtaskTitle,
      completed: false,
      order: localSubtasks.length,
    };
    
    setLocalSubtasks([...localSubtasks, newSubtask]);
    setNewSubtaskTitle('');
  };
  
  const handleDeleteSubtask = (subtaskId: string) => {
    setLocalSubtasks(localSubtasks.filter((st) => st.id !== subtaskId));
  };
  
  const handleSaveAndClose = () => {
    onUpdateSubtasks(localSubtasks);
    onUpdateSubtaskPositions(nodePositions);
    onClose();
  };
  
  const onNodesChange = useCallback(
    (changes: NodeChange[]) => {
      setNodes((nds) => applyNodeChanges(changes, nds));
      
      // Track position changes
      const positionChanges = changes.filter(
        (c) => c.type === 'position' && c.position
      );
      if (positionChanges.length > 0) {
        const newPositions = { ...nodePositions };
        positionChanges.forEach((change) => {
          if (change.type === 'position' && change.position) {
            newPositions[change.id] = change.position;
          }
        });
        setNodePositions(newPositions);
      }
    },
    [nodePositions]
  );
  
  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-background rounded-xl w-[900px] max-w-[90vw] max-h-[90vh] flex flex-col shadow-2xl">
        {/* Header */}
        <div className="p-6 border-b flex justify-between items-center">
          <div>
            <h2 className="text-2xl font-bold">{task.name}</h2>
            <p className="text-sm text-muted-foreground mt-1">
              Subtask Planning & Visualization
            </p>
          </div>
          <button
            onClick={handleSaveAndClose}
            className="text-muted-foreground hover:text-foreground text-2xl"
          >
            ✕
          </button>
        </div>
        
        {/* Subtask Flowchart */}
        <div className="p-6 flex-1 overflow-auto">
          <h3 className="text-lg font-semibold mb-3">Subtask Flow</h3>
          <div className="h-[400px] border rounded-lg overflow-hidden">
            <ReactFlowProvider>
              <ReactFlow
                nodes={nodes}
                edges={edges}
                nodeTypes={{
                  start: StartNode,
                  end: EndNode,
                  task: SubtaskNode,
                }}
                onNodesChange={onNodesChange}
                fitView
                attributionPosition="bottom-right"
              >
                <Background />
                <Controls />
                <MiniMap />
              </ReactFlow>
            </ReactFlowProvider>
          </div>
          
          {/* Add Subtask Form */}
          <div className="mt-6">
            <h4 className="text-md font-semibold mb-3">Add New Subtask</h4>
            <div className="flex gap-2">
              <input
                type="text"
                value={newSubtaskTitle}
                onChange={(e) => setNewSubtaskTitle(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && handleAddSubtask()}
                placeholder="Subtask title..."
                className="flex-1 px-4 py-2 border rounded-lg"
              />
              <button
                onClick={handleAddSubtask}
                className="btn-primary"
              >
                Add Subtask
              </button>
            </div>
          </div>
          
          {/* Subtask List */}
          {localSubtasks.length > 0 && (
            <div className="mt-6">
              <h4 className="text-md font-semibold mb-3">
                Manage Subtasks ({localSubtasks.length})
              </h4>
              <div className="space-y-2 max-h-[200px] overflow-y-auto">
                {localSubtasks.map((subtask, index) => (
                  <div
                    key={subtask.id}
                    className="flex items-center justify-between p-3 border rounded-lg bg-card hover:bg-muted transition-colors"
                  >
                    <div className="flex-1">
                      <span className="text-sm text-muted-foreground mr-2">
                        {index + 1}.
                      </span>
                      <span className="font-medium">{subtask.title}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <button className="btn-secondary btn-sm">
                        Edit
                      </button>
                      <button
                        onClick={() => handleDeleteSubtask(subtask.id)}
                        className="btn-destructive btn-sm"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
        
        {/* Footer */}
        <div className="p-6 border-t flex justify-end gap-3">
          <button onClick={onClose} className="btn-secondary">
            Cancel
          </button>
          <button onClick={handleSaveAndClose} className="btn-primary">
            Save & Close
          </button>
        </div>
      </div>
    </div>
  );
}
```

---

## 11. Interaction Patterns

### 11.1 Mouse Interactions

| Action | Trigger | Result |
|--------|---------|--------|
| **Left-click node** | Click task node | Opens details panel |
| **Right-click node** | Right-click task node | Opens context menu |
| **Drag node** | Click + drag node | Repositions node, updates connections |
| **Drag canvas** | Click empty space + drag | Pans viewport |
| **Scroll** | Mouse wheel | Zooms in/out |
| **Click "View Subtasks"** | In details panel | Opens subtask modal |
| **Double-click node** | Double-click task node | Opens edit form (optional) |

### 11.2 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + Z` | Undo last action |
| `Ctrl/Cmd + Y` | Redo action |
| `Delete` | Delete selected node |
| `Ctrl/Cmd + D` | Duplicate selected node |
| `Arrow Keys` | Navigate between nodes |
| `+` / `-` | Zoom in/out |
| `0` | Reset zoom to 100% |
| `Escape` | Close details panel/modal |

### 11.3 Touch Interactions (Mobile/Tablet)

| Action | Trigger | Result |
|--------|---------|--------|
| **Tap node** | Single tap | Opens details panel |
| **Long press** | Hold node | Opens context menu |
| **Drag node** | Touch + drag | Repositions node |
| **Pinch** | Two-finger pinch | Zooms in/out |
| **Two-finger drag** | Pan viewport | Moves canvas |

---

## 12. State Management

### 12.1 Local State (Component Level)

```typescript
// In FlowchartContainer
const [nodes, setNodes] = useState<Node[]>([]);
const [edges, setEdges] = useState<Edge[]>([]);
const [selectedTask, setSelectedTask] = useState<Task | null>(null);
const [contextMenu, setContextMenu] = useState<ContextMenuState | null>(null);
const [showSubtaskModal, setShowSubtaskModal] = useState(false);
```

### 12.2 Global State (Context/Redux)

```typescript
interface FlowchartState {
  tasks: Task[];
  workflowState: WorkflowState;
  validationResults: {
    taskValidations: Map<string, TaskValidation>;
    workflowValidation: WorkflowValidation;
  };
}

// Context
const FlowchartContext = createContext<{
  state: FlowchartState;
  actions: {
    updateTask: (taskId: string, updates: Partial<Task>) => void;
    deleteTask: (taskId: string) => void;
    addTask: (task: Task) => void;
    updateWorkflowState: (updates: Partial<WorkflowState>) => void;
    regenerateWorkflow: () => Promise<void>;
    validateWorkflow: () => void;
  };
}>(null!);
```

### 12.3 Persistence Strategy

**During Project Creation:**
- Store in wizard state (not persisted yet)
- Save to backend only on "Create Project"

**After Project Creation:**
- Auto-save on every change (debounced)
- Save to backend via API
- Store locally in IndexedDB for offline access

```typescript
// Debounced auto-save
const debouncedSave = useMemo(
  () =>
    debounce(async (workflowState: WorkflowState) => {
      await api.updateProjectWorkflow(projectId, workflowState);
    }, 1000),
  [projectId]
);

useEffect(() => {
  if (workflowState.isManualLayout) {
    debouncedSave(workflowState);
  }
}, [workflowState, debouncedSave]);
```

---

## 13. Implementation Phases

### Phase 1: Basic Flowchart Display (Week 1)

**Goal**: Show read-only flowchart

**Tasks:**
1. Set up React Flow integration
2. Create custom node components (Start, End, Task)
3. Implement theme colors
4. Convert tasks to flow nodes
5. Draw edges based on dependencies
6. Add zoom/pan controls
7. Basic auto-layout (vertical)

**Deliverable**: Static flowchart that displays tasks

**Time**: 12-16 hours

---

### Phase 2: Interaction & Editing (Week 2)

**Goal**: Make flowchart interactive

**Tasks:**
1. Implement drag-and-drop for nodes
2. Update edges when nodes move
3. Add node selection (click)
4. Create task details panel
5. Implement context menu (right-click)
6. Add layout toggle (horizontal/vertical)
7. Implement "Auto-Layout" button

**Deliverable**: Interactive flowchart with basic editing

**Time**: 16-20 hours

---

### Phase 3: Validation & Intelligence (Week 3)

**Goal**: Add AI-powered validation

**Tasks:**
1. Implement task validation logic
2. Implement workflow validation logic
3. Display validation badges on nodes
4. Create validation issues panel
5. Add "Vague Task" detection
6. Implement AI suggestions
7. Add workflow health indicator

**Deliverable**: Smart flowchart with validation

**Time**: 12-16 hours

---

### Phase 4: Subtasks System (Week 4)

**Goal**: Nested flowchart support

**Tasks:**
1. Design subtask data model
2. Create subtask modal component
3. Implement nested React Flow for subtasks
4. Add/edit/delete subtask functionality
5. Persist subtask positions
6. Show subtask count badge on main nodes
7. Connect subtask validation to parent

**Deliverable**: Full hierarchical flowchart system

**Time**: 16-20 hours

---

### Phase 5: Advanced Features (Week 5)

**Goal**: Polish and production-ready

**Tasks:**
1. Implement "Regenerate Workflow" with smart merge
2. Add undo/redo functionality
3. Implement keyboard shortcuts
4. Add export options (PNG, PDF, TXT)
5. Mobile/responsive optimization
6. Performance optimization (large workflows)
7. Add minimap for navigation
8. Implement workflow diff viewer (show changes)

**Deliverable**: Production-ready flowchart feature

**Time**: 20-24 hours

---

**Total Implementation Time**: 76-96 hours (10-12 weeks at part-time pace)

---

## 14. Testing & Validation Criteria

### 14.1 Functional Tests

**FT-1: Flowchart Display**
- [ ] All tasks appear as nodes
- [ ] Task types show correct colors
- [ ] Dependencies shown as arrows
- [ ] Start/End nodes present
- [ ] Auto-layout positions nodes correctly
- [ ] No overlapping nodes or edges

**FT-2: Interaction**
- [ ] Drag nodes to reposition
- [ ] Click node opens details panel
- [ ] Right-click shows context menu
- [ ] Zoom in/out works
- [ ] Pan canvas works
- [ ] Layout toggle works

**FT-3: Validation**
- [ ] Task validation scores displayed
- [ ] Workflow health indicator accurate
- [ ] Issues panel shows detected problems
- [ ] Validation updates in real-time
- [ ] Suggestions are helpful

**FT-4: Subtasks**
- [ ] Click node opens subtask modal
- [ ] Subtask flowchart renders correctly
- [ ] Add/edit/delete subtasks works
- [ ] Subtask positions persist
- [ ] Subtask count badge shows

**FT-5: State Persistence**
- [ ] Manual positions saved
- [ ] Subtask positions saved
- [ ] Workflow state persists across sessions
- [ ] Read-only mode in project view
- [ ] Export functions work

### 14.2 Performance Tests

**PT-1: Rendering Performance**
- [ ] <500ms load time for 20 tasks
- [ ] 60fps during drag operations
- [ ] <1s layout calculation
- [ ] Smooth zoom transitions
- [ ] No lag with 50+ tasks

**PT-2: Memory Usage**
- [ ] <50MB memory for typical workflow
- [ ] No memory leaks after extended use
- [ ] Efficient subtask flowchart rendering

### 14.3 Usability Tests

**UT-1: First-Time User**
- [ ] Can understand flowchart without instruction
- [ ] Knows how to interact with nodes
- [ ] Finds validation warnings helpful
- [ ] Successfully creates valid workflow

**UT-2: Power User**
- [ ] Can quickly rearrange complex workflows
- [ ] Efficiently manages subtasks
- [ ] Uses keyboard shortcuts effectively
- [ ] Satisfied with customization options

**UT-3: Mobile User**
- [ ] Can view flowchart on tablet
- [ ] Touch interactions work
- [ ] Pinch-to-zoom smooth
- [ ] Fallback view on phone works

---

## 15. Appendix: Code Examples

### 15.1 Converting Tasks to Flow Nodes

```typescript
function convertTasksToFlow(
  tasks: Task[],
  workflowState: WorkflowState
): { flowNodes: Node[]; flowEdges: Edge[] } {
  // Calculate positions if not manual layout
  let positions = workflowState.nodePositions;
  if (!workflowState.isManualLayout || Object.keys(positions).length === 0) {
    positions = Object.fromEntries(
      layoutWithDagre(tasks, workflowState.direction)
    );
  }
  
  // Create nodes
  const flowNodes: Node[] = [
    {
      id: 'start',
      type: 'start',
      position: { x: 0, y: 0 }, // Will be repositioned by layout
      data: { label: 'Start' },
    },
    ...tasks.map((task) => ({
      id: task.id,
      type: 'task' as const,
      position: positions[task.id] || { x: 0, y: 0 },
      data: { task },
    })),
    {
      id: 'end',
      type: 'end',
      position: { x: 0, y: 0 }, // Will be repositioned by layout
      data: { label: 'End' },
    },
  ];
  
  // Create edges
  const flowEdges: Edge[] = [];
  
  // Start node connections
  const firstTasks = tasks.filter(
    (t) => !t.dependencies || t.dependencies.length === 0
  );
  firstTasks.forEach((task) => {
    flowEdges.push({
      id: `start-${task.id}`,
      source: 'start',
      target: task.id,
      animated: true,
    });
  });
  
  // Task dependencies
  tasks.forEach((task) => {
    task.dependencies?.forEach((depId) => {
      flowEdges.push({
        id: `${depId}-${task.id}`,
        source: depId,
        target: task.id,
        animated: true,
      });
    });
  });
  
  // End node connections
  const lastTasks = tasks.filter((task) => {
    const isDependedOn = tasks.some((t) =>
      t.dependencies?.includes(task.id)
    );
    return !isDependedOn;
  });
  lastTasks.forEach((task) => {
    flowEdges.push({
      id: `${task.id}-end`,
      source: task.id,
      target: 'end',
      animated: true,
    });
  });
  
  return { flowNodes, flowEdges };
}
```

### 15.2 Converting Subtasks to Flow

```typescript
function convertSubtasksToFlow(
  subtasks: Subtask[]
): { flowNodes: Node[]; flowEdges: Edge[] } {
  const flowNodes: Node[] = [
    {
      id: 'subtask-start',
      type: 'start',
      position: { x: 100, y: 50 },
      data: { label: 'Start' },
    },
    ...subtasks.map((subtask, index) => ({
      id: subtask.id,
      type: 'task' as const,
      position: subtask.position || {
        x: 100 + index * 220,
        y: 150,
      },
      data: {
        task: {
          id: subtask.id,
          name: subtask.title,
          description: '',
          type: 'human' as const,
          estimated_hours: subtask.estimated_hours || 0,
        },
      },
    })),
    {
      id: 'subtask-end',
      type: 'end',
      position: {
        x: 100 + subtasks.length * 220,
        y: 150,
      },
      data: { label: 'End' },
    },
  ];
  
  const flowEdges: Edge[] = [];
  
  // Sequential connections
  if (subtasks.length > 0) {
    flowEdges.push({
      id: 'subtask-start-edge',
      source: 'subtask-start',
      target: subtasks[0].id,
      animated: true,
    });
    
    for (let i = 0; i < subtasks.length - 1; i++) {
      flowEdges.push({
        id: `${subtasks[i].id}-${subtasks[i + 1].id}`,
        source: subtasks[i].id,
        target: subtasks[i + 1].id,
        animated: true,
      });
    }
    
    flowEdges.push({
      id: 'subtask-end-edge',
      source: subtasks[subtasks.length - 1].id,
      target: 'subtask-end',
      animated: true,
    });
  }
  
  return { flowNodes, flowEdges };
}
```

### 15.3 Export to PNG

```typescript
import { toPng } from 'html-to-image';

async function exportFlowchartToPng(
  projectName: string,
  flowchartRef: React.RefObject<HTMLDivElement>
) {
  if (!flowchartRef.current) return;
  
  try {
    const dataUrl = await toPng(flowchartRef.current, {
      backgroundColor: '#FAFBFC',
      pixelRatio: 2, // Higher quality
    });
    
    // Download
    const link = document.createElement('a');
    link.download = `${projectName}_workflow.png`;
    link.href = dataUrl;
    link.click();
  } catch (error) {
    console.error('Error exporting flowchart:', error);
  }
}
```

---

## Document Approval

**Author**: CollabFlow Development Team  
**Contributors**: AI Design Specialist, UX Researcher  
**Reviewed By**: [To be filled]  
**Approved By**: [To be filled]  
**Date**: October 28, 2025  

**Version History:**
- v1.0 (Oct 27, 2025): Initial specification
- v2.0 (Oct 28, 2025): Complete overhaul with React Flow, subtasks, validation

---

## Next Steps

1. **Review & Approval**: Stakeholder review of this comprehensive spec
2. **Phase 1 Implementation**: Begin with basic flowchart display
3. **User Testing**: Conduct usability testing after Phase 2
4. **Iteration**: Refine based on feedback
5. **Backend Integration**: Connect validation logic to AI service
6. **Production Deployment**: Launch as part of Project Creation Wizard

---

**END OF DOCUMENT**
