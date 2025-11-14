# CollabFlow Flowchart Implementation Guide
## Agent Instructions: React Flow Integration in Laravel (Islands Architecture)

**Version**: 1.0  
**Date**: October 30, 2025  
**Target**: Agent implementing flowchart feature  
**Context**: CollabFlow Laravel 12 + Livewire + Flux UI project  
**Reference**: React demo shows desired functionality  

---

## 📋 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Prerequisites & Context](#prerequisites--context)
3. [Phase 1: Package Installation & Setup](#phase-1-package-installation--setup)
4. [Phase 2: Understanding Islands Architecture](#phase-2-understanding-islands-architecture)
5. [Phase 3: Building the Bridge Layer](#phase-3-building-the-bridge-layer)
6. [Phase 4: Creating the React Flowchart Component](#phase-4-creating-the-react-flowchart-component)
7. [Phase 5: Livewire Component Integration](#phase-5-livewire-component-integration)
8. [Phase 6: Data Flow Implementation](#phase-6-data-flow-implementation)
9. [Phase 7: Flux UI Integration](#phase-7-flux-ui-integration)
10. [Phase 8: Testing & Validation](#phase-8-testing--validation)
11. [Troubleshooting Guide](#troubleshooting-guide)

---

## 1. Architecture Overview

### 🎯 What You're Building

You are implementing an **interactive workflow flowchart** feature in the CollabFlow Laravel application. The flowchart will:
- Display project tasks as draggable nodes
- Show task dependencies as connecting edges
- Support auto-layout algorithms
- Enable nested subtask flowcharts
- Validate workflow structure
- Persist custom node positions

### 🏗️ The Islands Architecture

**Concept**: Your Laravel app is primarily server-rendered (Livewire + Blade templates), but the flowchart is a **React "island"**—a self-contained interactive component embedded within the Laravel application.

```
┌─────────────────────────────────────────────────────────┐
│  Laravel Application (Server-Rendered)                  │
│  ┌─────────────────────────────────────────────────┐   │
│  │  Livewire Component (Data Layer)                │   │
│  │  - Fetch tasks from database                    │   │
│  │  - Handle save operations                       │   │
│  │  - Manage state persistence                     │   │
│  │  ┌───────────────────────────────────────────┐  │   │
│  │  │  Alpine.js Bridge (Communication Layer)   │  │   │
│  │  │  - Pass data to React                     │  │   │
│  │  │  - Receive events from React              │  │   │
│  │  │  ┌─────────────────────────────────────┐  │  │   │
│  │  │  │  React Flow Island (UI Layer)       │  │  │   │
│  │  │  │  - Render interactive flowchart     │  │  │   │
│  │  │  │  - Handle drag-and-drop             │  │  │   │
│  │  │  │  - Auto-layout algorithms           │  │  │   │
│  │  │  │  - User interactions                │  │  │   │
│  │  │  └─────────────────────────────────────┘  │  │   │
│  │  └───────────────────────────────────────────┘  │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  Flux UI Components wrap around the flowchart          │
│  - Modals for task details                             │
│  - Buttons for actions                                 │
│  - Panels for validation                               │
└─────────────────────────────────────────────────────────┘
```

### 🔄 Data Flow Pattern

```
1. Page Load:
   Laravel → Livewire (fetch tasks) → Alpine (pass JSON) → React Flow (render)

2. User Interaction:
   React Flow (drag node) → Alpine (bridge event) → Livewire ($wire) → Laravel (save) → Database

3. State Update:
   Database → Livewire (notify) → Alpine (detect change) → React Flow (re-render)
```

**Key Principle**: React Flow is **ONLY** responsible for rendering and interacting with the flowchart canvas. All business logic, data management, and persistence happens in Laravel/Livewire.

---

## 2. Prerequisites & Context

### ✅ What You Have

- **Laravel 12** application with authentication
- **Livewire 3/4** installed and configured
- **Flux UI** component library installed
- **PostgreSQL** database with projects and tasks tables
- **Vite** as the build tool (comes with Laravel 12)
- **Alpine.js** available (bundled with Livewire)

### 📁 Database Schema (Relevant Tables)

```sql
-- projects table
id (uuid)
user_id (uuid)
name (varchar)
description (text)
workflow_state (jsonb)  ← Stores flowchart layout
created_at, updated_at

-- tasks table
id (uuid)
project_id (uuid)
name (varchar)
description (text)
type (enum: 'ai', 'human', 'hitl')
dependencies (jsonb)     ← Array of task IDs
position (jsonb)         ← {x: number, y: number}
validation (jsonb)       ← Validation results
created_at, updated_at
```

### 📄 Reference Materials

You have access to:
1. **React demo application** - Shows the desired flowchart functionality
2. **CollabFlow Design System** - Color palette, typography, spacing rules
3. **Flowchart Design Specification** - Detailed requirements document

### 🎨 Design System Quick Reference

**Colors** (use these CSS variables):
- `--glaucous` (#5B8DEE) - AI tasks
- `--tea-green` (#C4D6B0) - Human tasks  
- `--orange-peel` (#FF9500) - HITL tasks
- `--bittersweet` (#E74C3C) - Errors/urgent
- `--eggplant` (#4A235A) - Dark text/headers

**Typography**:
- Headings: Tahoma, bold
- Body: Montserrat, regular

---

## 3. Phase 1: Package Installation & Setup

### Step 1.1: Install React and React Flow

**In your Laravel project root**, run:

```bash
# Install React core libraries
npm install react@^18.3.0 react-dom@^18.3.0

# Install React Flow for flowchart functionality
npm install @xyflow/react@^12.0.0

# Install layout algorithms
npm install dagre@^0.8.5

# Install React dev tools (if not present)
npm install --save-dev @vitejs/plugin-react
```

**Verify installation**:
```bash
# Check package.json to confirm versions
cat package.json | grep -A 2 '"react"'
```

Expected output:
```json
"react": "^18.3.0",
"react-dom": "^18.3.0",
"@xyflow/react": "^12.0.0",
```

### Step 1.2: Configure Vite for React

**Edit `vite.config.js`** in your Laravel root:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/flowchart/index.jsx',  // ← React entry point
            ],
            refresh: true,
        }),
        react(),  // ← Enable React support
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
```

**Why this matters**: This tells Vite to process JSX files and bundle React code alongside your Laravel assets.

### Step 1.3: Create Directory Structure

Create the following folders:

```bash
mkdir -p resources/js/flowchart
mkdir -p resources/js/flowchart/components
mkdir -p resources/js/flowchart/nodes
mkdir -p resources/js/flowchart/utils
```

**Final structure**:
```
resources/
├── js/
│   ├── app.js                          # Laravel + Alpine entry point
│   └── flowchart/
│       ├── index.jsx                   # React entry point
│       ├── FlowchartContainer.jsx      # Main React component
│       ├── components/
│       │   ├── CustomControls.jsx      # Zoom, layout buttons
│       │   └── TaskDetailsPanel.jsx    # Side panel component
│       ├── nodes/
│       │   ├── StartNode.jsx           # Start node type
│       │   ├── EndNode.jsx             # End node type
│       │   └── TaskNode.jsx            # Task node type
│       └── utils/
│           ├── layoutAlgorithm.js      # Auto-layout logic
│           ├── dataTransformers.js     # Laravel ↔ React Flow
│           └── flowchartConfig.js      # Constants, colors
```

### Step 1.4: Verify Setup

**Test React compilation**:

1. Create a test file: `resources/js/flowchart/index.jsx`

```jsx
import React from 'react';
import { createRoot } from 'react-dom/client';

console.log('React Flow module loaded successfully');

export function initializeFlowchart() {
    console.log('Flowchart initialization function ready');
}
```

2. Import in `resources/js/app.js`:

```javascript
// Add at the bottom of app.js
import { initializeFlowchart } from './flowchart/index.jsx';

// Make available globally for Alpine
window.initializeFlowchart = initializeFlowchart;
```

3. Run Vite:

```bash
npm run dev
```

**Expected output**: No errors, should see "VITE ready in X ms"

---

## 4. Phase 2: Understanding Islands Architecture

### 🏝️ The Island Concept

Think of your Laravel application as a **continent of server-rendered pages**. The flowchart is an **island of client-side interactivity** within that continent.

**Most of your app**:
- Rendered by Laravel/Blade on the server
- Uses Livewire for dynamic updates
- Flux components for UI elements

**The flowchart island**:
- Rendered by React in the browser
- Uses React Flow for advanced interactions
- Controlled by Alpine.js bridge
- Data provided by Livewire

### 🌉 The Bridge Pattern

**Alpine.js acts as the bridge** between Livewire (Laravel) and React (island).

**Why not direct Livewire → React?**
- Livewire is server-side
- React needs client-side initialization
- Alpine provides the perfect middle layer

**Communication Pattern**:

```javascript
// Alpine component (the bridge)
Alpine.data('flowchartBridge', (initialData) => ({
    // Data from Livewire
    tasks: initialData.tasks,
    workflowState: initialData.workflowState,
    
    // React instance reference
    reactInstance: null,
    
    // Initialize React island
    init() {
        this.reactInstance = this.mountReactFlowchart();
    },
    
    // Pass data to React
    mountReactFlowchart() {
        const container = this.$refs.flowchartRoot;
        const root = ReactDOM.createRoot(container);
        
        root.render(
            React.createElement(FlowchartContainer, {
                tasks: this.tasks,
                workflowState: this.workflowState,
                onUpdate: (data) => this.handleReactUpdate(data)
            })
        );
        
        return root;
    },
    
    // Receive updates from React
    handleReactUpdate(data) {
        // Send to Livewire
        this.$wire.updateWorkflowState(data);
    },
    
    // Listen for Livewire updates
    onTasksUpdated(newTasks) {
        this.tasks = newTasks;
        // Tell React to re-render
        this.reactInstance.render(/*...*/);
    }
}));
```

**This pattern gives you**:
- ✅ React handles complex UI interactions
- ✅ Livewire handles data persistence
- ✅ Alpine coordinates between them
- ✅ Clear separation of concerns

### 📊 State Management Hierarchy

```
┌─────────────────────────────────────────────┐
│  SOURCE OF TRUTH: Laravel Database          │
│  (PostgreSQL tasks table + workflow_state)  │
└────────────────┬────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────┐
│  LIVEWIRE STATE (Server-side)               │
│  - Fetches from database                    │
│  - Validates data                           │
│  - Saves changes                            │
└────────────────┬────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────┐
│  ALPINE.JS STATE (Bridge)                   │
│  - Receives data from Livewire              │
│  - Passes to React                          │
│  - Relays updates back                      │
└────────────────┬────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────┐
│  REACT FLOW STATE (Client-side)             │
│  - Manages canvas interactions              │
│  - Handles drag-and-drop                    │
│  - Renders visualization                    │
└─────────────────────────────────────────────┘
```

**Key Rules**:
1. **Never** persist data from React directly - always go through Livewire
2. **Never** query the database from React - always use Livewire data
3. **Never** mix React state with Livewire state - keep them separate

---

## 5. Phase 3: Building the Bridge Layer

### Step 3.1: Create the Alpine Bridge Component

**File**: `resources/js/flowchart/alpine-bridge.js`

```javascript
import { createRoot } from 'react-dom/client';
import React from 'react';
import { FlowchartContainer } from './FlowchartContainer';

/**
 * Alpine.js component that bridges Livewire and React Flow
 * 
 * This component:
 * 1. Receives data from Livewire
 * 2. Initializes React Flow island
 * 3. Relays user interactions back to Livewire
 */
export function createFlowchartBridge() {
    return {
        // Livewire props (passed in via wire:model or @entangle)
        tasks: [],
        workflowState: null,
        projectId: null,
        
        // React instance management
        reactRoot: null,
        isReactMounted: false,
        
        /**
         * Alpine init hook - called when component mounts
         */
        init() {
            console.log('🌉 Bridge initialized with data:', {
                taskCount: this.tasks.length,
                hasWorkflowState: !!this.workflowState
            });
            
            // Wait for DOM to be ready
            this.$nextTick(() => {
                this.mountReact();
            });
            
            // Listen for Livewire updates
            this.watchLivewireUpdates();
        },
        
        /**
         * Mount React Flow component
         */
        mountReact() {
            const container = this.$refs.reactContainer;
            
            if (!container) {
                console.error('❌ React container not found');
                return;
            }
            
            console.log('⚛️ Mounting React Flow...');
            
            this.reactRoot = createRoot(container);
            this.renderReact();
            this.isReactMounted = true;
        },
        
        /**
         * Render/re-render React component with current data
         */
        renderReact() {
            if (!this.reactRoot) return;
            
            console.log('🔄 Rendering React with:', this.tasks.length, 'tasks');
            
            this.reactRoot.render(
                React.createElement(FlowchartContainer, {
                    // Pass data from Livewire
                    tasks: this.tasks,
                    workflowState: this.workflowState,
                    projectId: this.projectId,
                    
                    // Callbacks for React to communicate back
                    onNodePositionChange: (nodeId, position) => {
                        this.handleNodeMove(nodeId, position);
                    },
                    
                    onLayoutChange: (layout) => {
                        this.handleLayoutChange(layout);
                    },
                    
                    onTaskClick: (taskId) => {
                        this.handleTaskClick(taskId);
                    },
                    
                    onWorkflowUpdate: (workflowData) => {
                        this.handleWorkflowUpdate(workflowData);
                    }
                })
            );
        },
        
        /**
         * Watch for changes from Livewire
         */
        watchLivewireUpdates() {
            // Alpine's $watch for reactive updates
            this.$watch('tasks', (newTasks) => {
                console.log('📥 Tasks updated from Livewire:', newTasks.length);
                if (this.isReactMounted) {
                    this.renderReact();
                }
            });
            
            this.$watch('workflowState', (newState) => {
                console.log('📥 Workflow state updated from Livewire');
                if (this.isReactMounted) {
                    this.renderReact();
                }
            });
        },
        
        /**
         * Handle node position changes from React
         */
        handleNodeMove(nodeId, position) {
            console.log('📍 Node moved:', nodeId, position);
            
            // Call Livewire method to persist
            this.$wire.updateNodePosition(nodeId, position);
        },
        
        /**
         * Handle layout changes (manual → auto or vice versa)
         */
        handleLayoutChange(layout) {
            console.log('🔀 Layout changed:', layout);
            
            this.$wire.updateLayout(layout);
        },
        
        /**
         * Handle task click - open details panel
         */
        handleTaskClick(taskId) {
            console.log('👆 Task clicked:', taskId);
            
            // Dispatch event for Flux modal to open
            this.$dispatch('open-task-details', { taskId });
            
            // Or call Livewire method directly
            // this.$wire.showTaskDetails(taskId);
        },
        
        /**
         * Handle complete workflow state update
         */
        handleWorkflowUpdate(workflowData) {
            console.log('💾 Saving workflow state...');
            
            // Debounce this to avoid excessive saves
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }
            
            this.saveTimeout = setTimeout(() => {
                this.$wire.saveWorkflowState(workflowData);
            }, 1000); // Save after 1 second of no changes
        },
        
        /**
         * Cleanup when component unmounts
         */
        destroy() {
            if (this.reactRoot) {
                this.reactRoot.unmount();
            }
        }
    };
}

// Make available globally for Alpine
window.createFlowchartBridge = createFlowchartBridge;
```

### Step 3.2: Register Bridge in Alpine

**In `resources/js/app.js`**, add:

```javascript
import Alpine from 'alpinejs';
import { createFlowchartBridge } from './flowchart/alpine-bridge';

// Register the bridge as an Alpine component
Alpine.data('flowchartBridge', createFlowchartBridge);

// Start Alpine
Alpine.start();
```

### Step 3.3: Understanding the Bridge's Role

**The bridge does THREE things**:

1. **Initialization**: When the Blade component loads, Alpine calls `init()` which mounts the React island

2. **Data Flow Down**: When Livewire state changes, Alpine's `$watch` detects it and re-renders React with new data

3. **Events Flow Up**: When users interact with React Flow (drag nodes, click tasks), React calls the callbacks, which call Livewire methods via `$wire`

**Analogy**: Think of Alpine as a **translator** at a conference. Livewire speaks PHP, React speaks JavaScript. Alpine translates between them.

---

## 6. Phase 4: Creating the React Flowchart Component

### Step 4.1: Data Transformation Utilities

**File**: `resources/js/flowchart/utils/dataTransformers.js`

```javascript
/**
 * Transform Laravel task data into React Flow node format
 * 
 * Laravel format:
 * {
 *   id: "uuid",
 *   name: "Task name",
 *   type: "ai" | "human" | "hitl",
 *   position: {x: 100, y: 200},
 *   dependencies: ["uuid1", "uuid2"]
 * }
 * 
 * React Flow format:
 * {
 *   id: "uuid",
 *   type: "taskNode",
 *   position: {x: 100, y: 200},
 *   data: { task: {...} }
 * }
 */

export function tasksToNodes(tasks) {
    const nodes = [];
    
    // Add START node
    nodes.push({
        id: 'start',
        type: 'startNode',
        position: { x: 250, y: 50 },
        data: { label: 'START' }
    });
    
    // Transform tasks to nodes
    tasks.forEach((task, index) => {
        nodes.push({
            id: task.id,
            type: 'taskNode',
            position: task.position || calculateDefaultPosition(index),
            data: {
                task: task,
                label: task.name,
                type: task.type,
                hasIssues: task.validation?.issues?.length > 0
            },
            draggable: true
        });
    });
    
    // Add END node
    const lastY = tasks.length * 150 + 50;
    nodes.push({
        id: 'end',
        type: 'endNode',
        position: { x: 250, y: lastY },
        data: { label: 'END' }
    });
    
    return nodes;
}

/**
 * Create edges (connections) from task dependencies
 */
export function tasksToEdges(tasks) {
    const edges = [];
    
    // Find tasks with no dependencies (connect to START)
    const tasksWithoutDeps = tasks.filter(
        t => !t.dependencies || t.dependencies.length === 0
    );
    
    tasksWithoutDeps.forEach(task => {
        edges.push({
            id: `start-${task.id}`,
            source: 'start',
            target: task.id,
            type: 'smoothstep',
            animated: true
        });
    });
    
    // Create edges from dependencies
    tasks.forEach(task => {
        if (task.dependencies && task.dependencies.length > 0) {
            task.dependencies.forEach(depId => {
                edges.push({
                    id: `${depId}-${task.id}`,
                    source: depId,
                    target: task.id,
                    type: 'smoothstep',
                    animated: true
                });
            });
        }
    });
    
    // Connect tasks with no dependents to END
    const taskIdsWithDependents = new Set(
        tasks.flatMap(t => t.dependencies || [])
    );
    
    tasks.forEach(task => {
        if (!taskIdsWithDependents.has(task.id)) {
            edges.push({
                id: `${task.id}-end`,
                source: task.id,
                target: 'end',
                type: 'smoothstep'
            });
        }
    });
    
    return edges;
}

/**
 * Calculate default position if task doesn't have saved position
 */
function calculateDefaultPosition(index) {
    return {
        x: 250,
        y: 150 + (index * 150)
    };
}

/**
 * Transform React Flow nodes back to Laravel format for saving
 */
export function nodesToTaskPositions(nodes) {
    return nodes
        .filter(node => node.type === 'taskNode') // Exclude START/END
        .map(node => ({
            id: node.id,
            position: node.position
        }));
}
```

### Step 4.2: Custom Node Components

**File**: `resources/js/flowchart/nodes/TaskNode.jsx`

```jsx
import React from 'react';
import { Handle, Position } from '@xyflow/react';

/**
 * Custom task node component
 * 
 * This renders each task in the flowchart with:
 * - Type-based color coding
 * - Validation issue indicator
 * - Connection handles
 */
export function TaskNode({ data, selected }) {
    const { task, hasIssues } = data;
    
    // Color based on task type
    const getNodeColor = () => {
        switch (task.type) {
            case 'ai':
                return 'bg-[var(--glaucous)]';
            case 'human':
                return 'bg-[var(--tea-green)]';
            case 'hitl':
                return 'bg-[var(--orange-peel)]';
            default:
                return 'bg-gray-400';
        }
    };
    
    return (
        <div
            className={`
                relative px-4 py-3 rounded-lg border-2 shadow-md
                min-w-[250px] max-w-[250px]
                ${getNodeColor()}
                ${selected ? 'border-[var(--eggplant)] border-4' : 'border-transparent'}
                transition-all duration-200
            `}
        >
            {/* Input handle (top) */}
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3 !bg-white border-2 border-gray-400"
            />
            
            {/* Task name */}
            <div className="font-semibold text-white text-sm mb-1">
                {task.name}
            </div>
            
            {/* Task type badge */}
            <div className="text-xs text-white/80 uppercase tracking-wide">
                {task.type}
            </div>
            
            {/* Validation issue indicator */}
            {hasIssues && (
                <div className="absolute -top-2 -right-2 w-6 h-6 bg-[var(--bittersweet)] rounded-full flex items-center justify-center">
                    <span className="text-white text-xs font-bold">!</span>
                </div>
            )}
            
            {/* Output handle (bottom) */}
            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3 !bg-white border-2 border-gray-400"
            />
        </div>
    );
}
```

**File**: `resources/js/flowchart/nodes/StartNode.jsx`

```jsx
import React from 'react';
import { Handle, Position } from '@xyflow/react';

export function StartNode() {
    return (
        <div className="px-6 py-4 rounded-full bg-green-500 border-4 border-green-700 shadow-lg">
            <div className="font-bold text-white text-lg">START</div>
            
            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3 !bg-white border-2 border-gray-400"
            />
        </div>
    );
}
```

**File**: `resources/js/flowchart/nodes/EndNode.jsx`

```jsx
import React from 'react';
import { Handle, Position } from '@xyflow/react';

export function EndNode() {
    return (
        <div className="px-6 py-4 rounded-full bg-red-500 border-4 border-red-700 shadow-lg">
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3 !bg-white border-2 border-gray-400"
            />
            
            <div className="font-bold text-white text-lg">END</div>
        </div>
    );
}
```

### Step 4.3: Main Flowchart Container

**File**: `resources/js/flowchart/FlowchartContainer.jsx`

```jsx
import React, { useState, useCallback, useEffect } from 'react';
import {
    ReactFlow,
    Background,
    Controls,
    MiniMap,
    useNodesState,
    useEdgesState,
    addEdge
} from '@xyflow/react';

// Import styles
import '@xyflow/react/dist/style.css';

// Import custom nodes
import { TaskNode } from './nodes/TaskNode';
import { StartNode } from './nodes/StartNode';
import { EndNode } from './nodes/EndNode';

// Import utilities
import { tasksToNodes, tasksToEdges, nodesToTaskPositions } from './utils/dataTransformers';

/**
 * Main React Flow container component
 * 
 * This is the React "island" that renders the interactive flowchart
 */
export function FlowchartContainer({
    tasks,
    workflowState,
    projectId,
    onNodePositionChange,
    onWorkflowUpdate,
    onTaskClick
}) {
    console.log('⚛️ FlowchartContainer rendering with', tasks.length, 'tasks');
    
    // Define custom node types
    const nodeTypes = {
        taskNode: TaskNode,
        startNode: StartNode,
        endNode: EndNode
    };
    
    // Convert Laravel data to React Flow format
    const initialNodes = tasksToNodes(tasks);
    const initialEdges = tasksToEdges(tasks);
    
    // React Flow state management
    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);
    
    // Update nodes when tasks change from Livewire
    useEffect(() => {
        console.log('📥 Tasks updated, refreshing nodes');
        const newNodes = tasksToNodes(tasks);
        const newEdges = tasksToEdges(tasks);
        setNodes(newNodes);
        setEdges(newEdges);
    }, [tasks]);
    
    /**
     * Handle node drag end - save new position
     */
    const handleNodeDragStop = useCallback((event, node) => {
        if (node.type !== 'taskNode') return; // Don't save START/END positions
        
        console.log('🖱️ Node dragged:', node.id, node.position);
        
        // Notify parent (Alpine bridge)
        if (onNodePositionChange) {
            onNodePositionChange(node.id, node.position);
        }
    }, [onNodePositionChange]);
    
    /**
     * Handle node click - show task details
     */
    const handleNodeClick = useCallback((event, node) => {
        if (node.type !== 'taskNode') return;
        
        console.log('👆 Task node clicked:', node.id);
        
        if (onTaskClick) {
            onTaskClick(node.id);
        }
    }, [onTaskClick]);
    
    /**
     * Handle connection creation (if you want users to add dependencies)
     */
    const handleConnect = useCallback((params) => {
        console.log('🔗 Connection created:', params);
        setEdges((eds) => addEdge(params, eds));
        
        // TODO: Notify Livewire about new dependency
    }, [setEdges]);
    
    return (
        <div className="w-full h-full">
            <ReactFlow
                nodes={nodes}
                edges={edges}
                nodeTypes={nodeTypes}
                onNodesChange={onNodesChange}
                onEdgesChange={onEdgesChange}
                onNodeDragStop={handleNodeDragStop}
                onNodeClick={handleNodeClick}
                onConnect={handleConnect}
                fitView
                attributionPosition="bottom-right"
            >
                {/* Grid background */}
                <Background 
                    variant="dots" 
                    gap={20} 
                    size={1}
                    color="#94a3b8"
                />
                
                {/* Zoom and pan controls */}
                <Controls 
                    showInteractive={false}
                    position="top-right"
                />
                
                {/* Minimap for navigation */}
                <MiniMap
                    nodeColor={(node) => {
                        if (node.type === 'startNode') return '#22c55e';
                        if (node.type === 'endNode') return '#ef4444';
                        return '#5B8DEE';
                    }}
                    position="bottom-left"
                    style={{
                        backgroundColor: '#f8fafc',
                        border: '2px solid #cbd5e1'
                    }}
                />
            </ReactFlow>
        </div>
    );
}
```

### Step 4.4: Export Entry Point

**File**: `resources/js/flowchart/index.jsx`

```jsx
// Entry point for the React Flow flowchart feature

export { FlowchartContainer } from './FlowchartContainer';
export { TaskNode } from './nodes/TaskNode';
export { StartNode } from './nodes/StartNode';
export { EndNode } from './nodes/EndNode';
export * from './utils/dataTransformers';

console.log('✅ React Flow flowchart module loaded');
```

---

## 7. Phase 5: Livewire Component Integration

### Step 5.1: Create Livewire Component

```bash
php artisan make:livewire ProjectWorkflowFlowchart
```

**File**: `app/Livewire/ProjectWorkflowFlowchart.php`

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

/**
 * Livewire component for managing the project workflow flowchart
 * 
 * Responsibilities:
 * - Load tasks from database
 * - Persist node positions
 * - Handle workflow state updates
 * - Provide data to React Flow via Alpine bridge
 */
class ProjectWorkflowFlowchart extends Component
{
    // Public properties (available to Alpine/Blade)
    public $projectId;
    public $tasks = [];
    public $workflowState = null;
    
    // Protected properties
    protected $project;
    
    /**
     * Mount the component with project ID
     */
    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadProject();
        $this->loadTasks();
    }
    
    /**
     * Load project and its workflow state
     */
    protected function loadProject()
    {
        $this->project = Project::findOrFail($this->projectId);
        $this->workflowState = $this->project->workflow_state ?? [
            'layout' => 'vertical',
            'isManualLayout' => false
        ];
    }
    
    /**
     * Load tasks with their positions and dependencies
     */
    public function loadTasks()
    {
        $this->tasks = Task::where('project_id', $this->projectId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'type' => $task->type,
                    'position' => $task->position ?? null,
                    'dependencies' => $task->dependencies ?? [],
                    'validation' => $task->validation ?? null,
                    'estimated_hours' => $task->estimated_hours,
                ];
            })
            ->toArray();
        
        Log::info('Loaded tasks for flowchart', [
            'project_id' => $this->projectId,
            'task_count' => count($this->tasks)
        ]);
    }
    
    /**
     * Update a single node's position (called from React via Alpine)
     * 
     * @param string $nodeId Task UUID
     * @param array $position {x: number, y: number}
     */
    public function updateNodePosition($nodeId, $position)
    {
        try {
            $task = Task::findOrFail($nodeId);
            $task->position = $position;
            $task->save();
            
            // Mark workflow as manually laid out
            $this->workflowState['isManualLayout'] = true;
            $this->saveWorkflowState($this->workflowState);
            
            Log::info('Node position updated', [
                'task_id' => $nodeId,
                'position' => $position
            ]);
            
            // Notify user
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Position saved'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update node position', [
                'error' => $e->getMessage(),
                'task_id' => $nodeId
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to save position'
            ]);
        }
    }
    
    /**
     * Save complete workflow state
     * 
     * @param array $workflowData Complete workflow configuration
     */
    public function saveWorkflowState($workflowData)
    {
        try {
            $this->project->workflow_state = $workflowData;
            $this->project->save();
            
            $this->workflowState = $workflowData;
            
            Log::info('Workflow state saved', [
                'project_id' => $this->projectId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to save workflow state', [
                'error' => $e->getMessage(),
                'project_id' => $this->projectId
            ]);
        }
    }
    
    /**
     * Update layout type (vertical/horizontal/custom)
     */
    public function updateLayout($layout)
    {
        $this->workflowState['layout'] = $layout;
        $this->saveWorkflowState($this->workflowState);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Layout updated to ' . $layout
        ]);
    }
    
    /**
     * Open task details panel (Flux modal)
     */
    public function showTaskDetails($taskId)
    {
        $this->dispatch('open-task-modal', ['taskId' => $taskId]);
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.project-workflow-flowchart');
    }
}
```

### Step 5.2: Create Blade View

**File**: `resources/views/livewire/project-workflow-flowchart.blade.php`

```blade
<div 
    class="w-full h-full relative"
    x-data="flowchartBridge()"
    x-init="
        tasks = @js($tasks);
        workflowState = @js($workflowState);
        projectId = @js($projectId);
    "
>
    {{-- Loading State --}}
    <div 
        x-show="!isReactMounted"
        class="absolute inset-0 flex items-center justify-center bg-gray-50"
    >
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-glaucous mx-auto mb-4"></div>
            <p class="text-gray-600">Loading flowchart...</p>
        </div>
    </div>
    
    {{-- React Flow Container --}}
    <div 
        x-ref="reactContainer"
        class="w-full h-full"
        x-show="isReactMounted"
    ></div>
    
    {{-- Toolbar (Flux components) --}}
    <div class="absolute top-4 left-4 flex gap-2">
        <flux:button 
            size="sm" 
            icon="refresh-cw"
            @click="$wire.loadTasks()"
        >
            Refresh
        </flux:button>
        
        <flux:button 
            size="sm" 
            icon="layout"
            variant="outline"
        >
            Auto Layout
        </flux:button>
    </div>
    
    {{-- Debug Info (remove in production) --}}
    @if(app()->environment('local'))
    <div class="absolute bottom-4 right-4 bg-white p-3 rounded shadow text-xs">
        <div><strong>Project:</strong> {{ $projectId }}</div>
        <div><strong>Tasks:</strong> {{ count($tasks) }}</div>
        <div><strong>Layout:</strong> {{ $workflowState['layout'] ?? 'default' }}</div>
    </div>
    @endif
</div>

@script
<script>
    // This script block has access to Livewire's $wire
    console.log('Flowchart Livewire component loaded');
    
    // Listen for task updates from Livewire
    $wire.on('tasks-updated', () => {
        console.log('📥 Tasks updated event received');
    });
</script>
@endscript
```

### Step 5.3: Add to Project View

**File**: `resources/views/projects/show.blade.php` (or wherever you want the flowchart)

```blade
<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">{{ $project->name }}</h1>
        
        {{-- Tab Navigation (if you have tabs) --}}
        <div x-data="{ activeTab: 'workflow' }" class="mb-6">
            <nav class="flex space-x-4 border-b">
                <button 
                    @click="activeTab = 'tasks'"
                    :class="activeTab === 'tasks' ? 'border-b-2 border-glaucous' : ''"
                    class="px-4 py-2"
                >
                    Tasks
                </button>
                <button 
                    @click="activeTab = 'workflow'"
                    :class="activeTab === 'workflow' ? 'border-b-2 border-glaucous' : ''"
                    class="px-4 py-2"
                >
                    Workflow
                </button>
            </nav>
            
            {{-- Workflow Tab Content --}}
            <div x-show="activeTab === 'workflow'" class="mt-6">
                <div class="bg-white rounded-lg shadow-lg p-6 h-[600px]">
                    @livewire('project-workflow-flowchart', ['projectId' => $project->id])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## 8. Phase 6: Data Flow Implementation

### Understanding the Complete Flow

Let me trace a complete user interaction through the system:

**Scenario**: User drags a task node to a new position

```
1. USER ACTION
   User drags "Task 3" node from position (250, 300) to (400, 450)

2. REACT FLOW (Client-side)
   - Detects drag end event
   - Calls onNodeDragStop callback
   - Passes: nodeId="uuid-123", position={x: 400, y: 450}

3. ALPINE BRIDGE (Client-side)
   handleNodeMove(nodeId, position) {
       this.$wire.updateNodePosition(nodeId, position)
   }

4. LIVEWIRE (Server-side)
   updateNodePosition($nodeId, $position) {
       $task = Task::find($nodeId);
       $task->position = $position;
       $task->save();
   }

5. DATABASE (PostgreSQL)
   UPDATE tasks 
   SET position = '{"x": 400, "y": 450}'
   WHERE id = 'uuid-123'

6. RESPONSE (back up the chain)
   Database → Livewire → Alpine → React → User sees saved
```

### Key Implementation Points

**1. Debouncing Saves**

Don't save on every pixel of drag. Save after dragging stops:

```javascript
// In alpine-bridge.js
handleWorkflowUpdate(workflowData) {
    // Clear existing timeout
    if (this.saveTimeout) {
        clearTimeout(this.saveTimeout);
    }
    
    // Set new timeout - save after 1 second of no changes
    this.saveTimeout = setTimeout(() => {
        this.$wire.saveWorkflowState(workflowData);
    }, 1000);
}
```

**2. Optimistic UI Updates**

Update the UI immediately, save in background:

```javascript
handleNodeMove(nodeId, position) {
    // UI is already updated by React Flow
    
    // Save to server (async, non-blocking)
    this.$wire.updateNodePosition(nodeId, position);
    
    // Show subtle confirmation
    this.showSaveIndicator();
}
```

**3. Error Handling**

Handle save failures gracefully:

```php
// In Livewire component
public function updateNodePosition($nodeId, $position)
{
    try {
        $task = Task::findOrFail($nodeId);
        $task->position = $position;
        $task->save();
        
        return ['success' => true];
        
    } catch (\Exception $e) {
        Log::error('Position save failed', [
            'node_id' => $nodeId,
            'error' => $e->getMessage()
        ]);
        
        // Notify frontend of failure
        $this->dispatch('save-failed', [
            'message' => 'Could not save position. Please try again.'
        ]);
        
        return ['success' => false];
    }
}
```

---

## 9. Phase 7: Flux UI Integration

### Step 7.1: Task Details Modal

When user clicks a task node, open a Flux modal:

**File**: `resources/views/components/task-details-modal.blade.php`

```blade
<flux:modal 
    name="task-details"
    class="max-w-2xl"
>
    <flux:heading>
        {{ $task->name ?? 'Task Details' }}
    </flux:heading>
    
    <flux:description>
        <div class="space-y-4">
            <div>
                <flux:label>Description</flux:label>
                <p class="text-gray-700">{{ $task->description }}</p>
            </div>
            
            <div>
                <flux:label>Type</flux:label>
                <flux:badge 
                    :color="$task->type === 'ai' ? 'blue' : ($task->type === 'human' ? 'green' : 'orange')"
                >
                    {{ strtoupper($task->type) }}
                </flux:badge>
            </div>
            
            <div>
                <flux:label>Estimated Hours</flux:label>
                <p>{{ $task->estimated_hours }} hours</p>
            </div>
        </div>
    </flux:description>
    
    <flux:button @click="$flux.modal('task-details').close()">
        Close
    </flux:button>
</flux:modal>
```

**Listen for open event in Alpine**:

```javascript
// In Livewire component or layout
Alpine.data('taskModal', () => ({
    init() {
        this.$watch('$wire.selectedTaskId', (taskId) => {
            if (taskId) {
                this.$flux.modal('task-details').open();
            }
        });
    }
}));
```

### Step 7.2: Toolbar with Flux Buttons

Add Flux UI controls above the flowchart:

```blade
<div class="flex items-center justify-between mb-4">
    <div class="flex gap-2">
        <flux:button 
            icon="refresh-cw"
            variant="outline"
            @click="$wire.loadTasks()"
        >
            Refresh
        </flux:button>
        
        <flux:button 
            icon="git-branch"
            variant="outline"
        >
            Auto Layout
        </flux:button>
        
        <flux:button 
            icon="download"
            variant="outline"
        >
            Export PNG
        </flux:button>
    </div>
    
    <div>
        <flux:badge variant="success">
            {{ count($tasks) }} Tasks
        </flux:badge>
    </div>
</div>
```

---

## 10. Phase 8: Testing & Validation

### Testing Checklist

**✅ Phase 1: Package Installation**
- [ ] Run `npm install` without errors
- [ ] Confirm React and React Flow in package.json
- [ ] Run `npm run dev` successfully
- [ ] Check browser console for "React Flow module loaded"

**✅ Phase 2: Bridge Layer**
- [ ] Alpine bridge initializes
- [ ] Console shows "Bridge initialized"
- [ ] React container element exists in DOM
- [ ] No JavaScript errors in console

**✅ Phase 3: React Rendering**
- [ ] Flowchart canvas renders
- [ ] START and END nodes visible
- [ ] Task nodes display with correct colors
- [ ] Edges connect nodes correctly

**✅ Phase 4: Interactivity**
- [ ] Can drag task nodes
- [ ] Node positions update in UI
- [ ] Click task opens details (if implemented)
- [ ] Zoom and pan controls work

**✅ Phase 5: Data Persistence**
- [ ] Drag node → position saves to database
- [ ] Refresh page → node stays in new position
- [ ] Check database: `position` column updated
- [ ] Livewire logs show save operations

**✅ Phase 6: Error Handling**
- [ ] Disconnect internet → see error message
- [ ] Invalid task ID → doesn't crash
- [ ] Network errors handled gracefully

### Manual Testing Steps

**Test 1: Basic Rendering**
```
1. Navigate to project page
2. Click "Workflow" tab
3. Verify flowchart loads within 3 seconds
4. Count nodes: should match task count + 2 (START, END)
```

**Test 2: Drag and Drop**
```
1. Click and drag any task node
2. Release at new position
3. Wait 2 seconds
4. Refresh page (F5)
5. Verify node stayed in new position
```

**Test 3: Database Verification**
```
1. Drag a node to position (500, 600)
2. Run SQL query:
   SELECT id, name, position FROM tasks WHERE id = 'your-task-id';
3. Verify position shows: {"x": 500, "y": 600}
```

**Test 4: Multi-User (if applicable)**
```
1. User A: Drag node to new position
2. User B: Refresh page
3. Verify User B sees updated position
```

---

## 11. Troubleshooting Guide

### Problem: "React is not defined"

**Cause**: React not imported properly  
**Fix**:
```javascript
// At top of FlowchartContainer.jsx
import React from 'react';
```

### Problem: "Cannot read property 'render' of undefined"

**Cause**: Alpine tried to mount React before DOM ready  
**Fix**:
```javascript
init() {
    this.$nextTick(() => {
        this.mountReact();
    });
}
```

### Problem: Nodes don't show correct colors

**Cause**: CSS variables not loaded  
**Fix**: Ensure design system CSS is imported before flowchart:
```css
/* In app.css */
:root {
    --glaucous: #5B8DEE;
    --tea-green: #C4D6B0;
    --orange-peel: #FF9500;
}
```

### Problem: Position not saving

**Cause**: Node ID might be wrong or network error  
**Debug**:
```javascript
// In alpine-bridge.js
handleNodeMove(nodeId, position) {
    console.log('Saving:', nodeId, position);
    this.$wire.updateNodePosition(nodeId, position)
        .then(result => console.log('Save result:', result))
        .catch(error => console.error('Save error:', error));
}
```

### Problem: "Task not found" error

**Cause**: Trying to save START or END node positions  
**Fix**:
```javascript
handleNodeDragStop(event, node) {
    if (node.type !== 'taskNode') return; // Skip START/END
    
    this.onNodePositionChange(node.id, node.position);
}
```

### Problem: Flowchart doesn't update when tasks change

**Cause**: React not watching for changes  
**Fix**:
```jsx
useEffect(() => {
    console.log('Tasks changed, updating nodes');
    setNodes(tasksToNodes(tasks));
    setEdges(tasksToEdges(tasks));
}, [tasks]); // Add dependency
```

---

## 12. Next Steps After Basic Implementation

Once you have the basic flowchart working:

### Phase 2 Features (Week 2)
1. **Auto-layout button** - Use Dagre algorithm
2. **Task validation badges** - Show issues on nodes
3. **Context menu** - Right-click for actions
4. **Subtask modal** - Nested flowchart view

### Phase 3 Features (Week 3)
5. **Export to PNG** - Using html-to-image
6. **Undo/redo** - Track state changes
7. **Keyboard shortcuts** - Arrow keys, delete, etc.
8. **Search/filter** - Highlight specific tasks

### Resources

**Documentation**:
- React Flow: https://reactflow.dev/
- Livewire: https://livewire.laravel.com/
- Flux UI: https://fluxui.dev/
- Alpine.js: https://alpinejs.dev/

**Need Help?**
- Check browser console for errors
- Review Laravel logs: `storage/logs/laravel.log`
- Enable Livewire debugging: `LIVEWIRE_DEBUG=true` in .env

---

## 🎯 Summary: Your Implementation Path

1. **Install packages** (React, React Flow, Dagre)
2. **Configure Vite** for React
3. **Build Alpine bridge** (communication layer)
4. **Create React components** (FlowchartContainer, nodes)
5. **Build Livewire component** (data layer)
6. **Connect everything** (bridge calls Livewire)
7. **Test and debug**
8. **Add Flux UI** (modals, buttons)
9. **Implement advanced features**

**Key Success Factors**:
- ✅ Keep React focused ONLY on rendering
- ✅ Let Livewire handle ALL data operations
- ✅ Use Alpine as the bridge
- ✅ Test each layer independently
- ✅ Console.log liberally during development

---

**Good luck with your implementation! 🚀**

Remember: The islands architecture means React Flow is just a sophisticated widget in your Laravel app—not a full SPA. This keeps your architecture clean and maintainable.
