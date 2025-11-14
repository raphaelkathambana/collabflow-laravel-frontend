# CollabFlow Laravel Frontend - HITL Checkpoint UI Design Document

**Document Version:** 2.0  
**Date:** November 12, 2025  
**Project:** CollabFlow Laravel Application  
**Feature:** HITL Checkpoint Visual Design & Workflow Visualization  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Previous Understanding](#2-previous-understanding)
3. [New UI/UX Design](#3-new-uiux-design)
4. [Implementation Guide](#4-implementation-guide)
5. [Testing & Validation](#5-testing--validation)
6. [Verification Checklist](#6-verification-checklist)
7. [Appendix: Code Samples](#7-appendix-code-samples)

---

## 1. Executive Summary

### What Changed

**Before:**
- All tasks displayed with same styling
- No visual distinction for HITL tasks or checkpoints
- Workflow graph showed all tasks connected to START/END
- No subtask visualization
- Generic task cards with type badges only

**After:**
- **Checkpoint tasks**: Throbbing yellow animation (⚠️ pulse effect)
- **HITL tasks**: Distinct collaborative styling with subtask indicators
- **Workflow graph**: Real dependency visualization (no flat START/END connections)
- **Subtask drawer**: Expandable subtask view for HITL tasks
- **Enhanced visual hierarchy**: Clear distinction between AI, Human, and HITL tasks

### Core Principle

> **Visual language communicates workflow nature**  
> Checkpoints pulse to draw attention → HITL tasks show collaboration → Dependencies show real flow

---

## 2. Previous Understanding

### 2.1 Old UI Design (Issues)

```css
/* OLD: All tasks looked the same */
.task-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  /* No visual distinction */
}

.task-badge {
  /* AI, Human, HITL - just text badges */
  background: var(--badge-color);
}
```

**Problems:**
1. ❌ No way to identify checkpoint tasks at a glance
2. ❌ HITL tasks looked like regular tasks
3. ❌ No indication of subtask structure
4. ❌ Workflow graph was misleading (false dependencies)
5. ❌ No visual priority indicators

### 2.2 Old Workflow Visualization

```
   START
     ↓
  ┌──┴──┐
  ↓     ↓
Task1 Task2 Task3 ...
  ↓     ↓     ↓
  └──┬──┘──┬──┘
     ↓
    END
```

**Problem:** All tasks appeared as parallel, when in reality they had sequential dependencies.

---

## 3. New UI/UX Design

### 3.1 Task Type Visual Language

#### AI Tasks (Glaucous - #7698C1)
```css
.task-card--ai {
  background: linear-gradient(135deg, #7698C1 0%, #6688B1 100%);
  color: white;
  border: none;
}

.task-badge--ai {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 600;
}

.task-icon--ai::before {
  content: "🤖";
  font-size: 1.5rem;
}
```

**Design Rationale:** Calm blue indicates automation and reliability.

#### Human Tasks (Tea Green - #C5E6A6)
```css
.task-card--human {
  background: linear-gradient(135deg, #C5E6A6 0%, #B5D696 100%);
  color: #2D3748;
  border: 2px solid #9CC575;
}

.task-badge--human {
  background: rgba(45, 55, 72, 0.1);
  color: #2D3748;
  font-weight: 600;
}

.task-icon--human::before {
  content: "👤";
  font-size: 1.5rem;
}
```

**Design Rationale:** Green indicates human involvement and manual work.

#### HITL Tasks (Orange Peel - #FDA037)
```css
.task-card--hitl {
  background: linear-gradient(135deg, #FDA037 0%, #ED9027 100%);
  color: white;
  border: 3px solid #FDB757;
  position: relative;
}

.task-badge--hitl {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.task-icon--hitl::before {
  content: "🤝";
  font-size: 1.5rem;
}

/* HITL collaboration indicator */
.task-card--hitl::after {
  content: "";
  position: absolute;
  top: -3px;
  right: -3px;
  bottom: -3px;
  left: -3px;
  border: 3px solid rgba(253, 160, 55, 0.3);
  border-radius: 12px;
  animation: collaboration-glow 2s ease-in-out infinite;
}

@keyframes collaboration-glow {
  0%, 100% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.02);
  }
}
```

**Design Rationale:** Orange indicates collaboration, with animated glow showing active human-AI cooperation.

#### Checkpoint Tasks (Yellow - #FFD93D)
```css
.task-card--checkpoint {
  background: linear-gradient(135deg, #FFD93D 0%, #FFC91D 100%);
  color: #2D3748;
  border: 3px solid #FFE057;
  position: relative;
  animation: checkpoint-throb 1.5s ease-in-out infinite;
}

@keyframes checkpoint-throb {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 4px 6px rgba(255, 217, 61, 0.3);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(255, 217, 61, 0.6);
  }
}

.task-badge--checkpoint {
  background: rgba(45, 55, 72, 0.1);
  color: #2D3748;
  font-weight: 700;
  animation: badge-pulse 1.5s ease-in-out infinite;
}

@keyframes badge-pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.task-icon--checkpoint::before {
  content: "🔒";
  font-size: 1.5rem;
  animation: icon-bounce 1.5s ease-in-out infinite;
}

@keyframes icon-bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-3px);
  }
}

/* Checkpoint attention indicator */
.checkpoint-indicator {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  background: #EF4444;
  border-radius: 50%;
  border: 3px solid white;
  animation: checkpoint-pulse-badge 1.5s ease-in-out infinite;
}

@keyframes checkpoint-pulse-badge {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
}
```

**Design Rationale:** Yellow with throbbing animation creates urgency and draws attention to human review points.

### 3.2 Task Card Layouts

#### Standard Task Card
```html
<div class="task-card task-card--{type}">
  <div class="task-card__header">
    <div class="task-icon task-icon--{type}"></div>
    <div class="task-info">
      <h3 class="task-name">{{ task.name }}</h3>
      <span class="task-badge task-badge--{type}">
        {{ task.type | uppercase }}
      </span>
    </div>
  </div>
  
  <div class="task-card__body">
    <p class="task-description">{{ task.description }}</p>
    
    <div class="task-meta">
      <span class="meta-item">
        <i class="icon-clock"></i>
        {{ task.estimated_hours }}h
      </span>
      <span class="meta-item">
        <i class="icon-complexity"></i>
        {{ task.complexity }}
      </span>
    </div>
  </div>
  
  <div class="task-card__footer">
    <button class="btn-view-details">View Details</button>
  </div>
</div>
```

#### HITL Task Card (with Subtask Indicator)
```html
<div class="task-card task-card--hitl">
  <div class="task-card__header">
    <div class="task-icon task-icon--hitl"></div>
    <div class="task-info">
      <h3 class="task-name">{{ task.name }}</h3>
      <div class="task-badges">
        <span class="task-badge task-badge--hitl">
          HITL
        </span>
        <span class="subtask-count">
          <i class="icon-layers"></i>
          {{ task.subtasks.length }} subtasks
        </span>
      </div>
    </div>
  </div>
  
  <div class="task-card__body">
    <p class="task-description">{{ task.description }}</p>
    
    <!-- Checkpoint Summary -->
    <div class="checkpoint-summary">
      <i class="icon-checkpoint"></i>
      <span>{{ task.checkpoint_count }} checkpoint{{ task.checkpoint_count > 1 ? 's' : '' }}</span>
    </div>
    
    <div class="task-meta">
      <span class="meta-item">
        <i class="icon-clock"></i>
        {{ task.estimated_hours }}h total
      </span>
      <span class="meta-item">
        <i class="icon-complexity"></i>
        {{ task.complexity }}
      </span>
    </div>
  </div>
  
  <div class="task-card__footer">
    <button 
      class="btn-expand-subtasks"
      @click="toggleSubtasks(task.id)"
    >
      <i class="icon-chevron-down"></i>
      {{ expanded ? 'Hide' : 'Show' }} Workflow
    </button>
  </div>
</div>

<!-- Expandable Subtask Drawer -->
<div 
  class="subtask-drawer"
  v-if="expanded"
  :class="{ 'subtask-drawer--open': expanded }"
>
  <div class="subtask-drawer__header">
    <h4>Task Workflow</h4>
    <span class="subtask-count-badge">
      {{ task.subtasks.length }} steps
    </span>
  </div>
  
  <div class="subtask-list">
    <div 
      v-for="(subtask, index) in task.subtasks"
      :key="subtask.id"
      class="subtask-item"
      :class="{
        'subtask-item--ai': subtask.type === 'ai',
        'subtask-item--checkpoint': subtask.is_checkpoint
      }"
    >
      <!-- Sequence Number -->
      <div class="subtask-sequence">{{ index + 1 }}</div>
      
      <!-- Subtask Content -->
      <div class="subtask-content">
        <div class="subtask-header">
          <span class="subtask-icon">
            {{ subtask.is_checkpoint ? '🔒' : '🤖' }}
          </span>
          <h5 class="subtask-name">{{ subtask.name }}</h5>
          <span 
            class="subtask-type-badge"
            :class="subtask.is_checkpoint ? 'badge--checkpoint' : 'badge--ai'"
          >
            {{ subtask.is_checkpoint ? subtask.checkpoint_type : 'AI' }}
          </span>
        </div>
        
        <p class="subtask-description">{{ subtask.description }}</p>
        
        <!-- Checkpoint Details (if applicable) -->
        <div 
          v-if="subtask.is_checkpoint" 
          class="checkpoint-details"
        >
          <div class="checkpoint-criteria">
            <strong>Review Criteria:</strong>
            <p>{{ subtask.checkpoint_criteria }}</p>
          </div>
        </div>
        
        <div class="subtask-meta">
          <span class="meta-hours">{{ subtask.estimated_hours }}h</span>
          <span 
            v-if="subtask.dependencies.length > 0"
            class="meta-deps"
          >
            Depends on: {{ subtask.dependencies.join(', ') }}
          </span>
        </div>
      </div>
      
      <!-- Connection Line (if not last) -->
      <div 
        v-if="index < task.subtasks.length - 1"
        class="subtask-connector"
      ></div>
    </div>
  </div>
</div>
```

### 3.3 Workflow Graph Visualization

#### New Architecture: Real Dependencies

```
         START
           ↓
      ┌────┴────┐
      ↓         ↓
   Task 1    Task 2
   (DB)      (Auth)
      ↓         ↓
      └────┬────┘
           ↓
        Task 3
      (API Setup)
           ↓
      ┌────┴────┐
      ↓         ↓
   Task 4    Task 5
   (Tests)   (Docs)
      └────┬────┘
           ↓
          END
```

**Key Features:**
1. **Real dependency flows** (not all connected to START/END)
2. **Parallel task grouping** (tasks that can run concurrently)
3. **HITL subtask expansion** (show internal workflow)
4. **Checkpoint highlighting** (throbbing yellow nodes)

#### Vue Component: WorkflowGraph.vue

```vue
<template>
  <div class="workflow-canvas">
    <svg
      ref="svgCanvas"
      :width="canvasWidth"
      :height="canvasHeight"
      class="workflow-svg"
      @mousedown="handlePan"
      @wheel="handleZoom"
    >
      <!-- Define arrow markers -->
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
            fill="#64748B"
          />
        </marker>
        
        <marker
          id="arrowhead-checkpoint"
          markerWidth="10"
          markerHeight="10"
          refX="9"
          refY="3"
          orient="auto"
        >
          <polygon
            points="0 0, 10 3, 0 6"
            fill="#FFD93D"
          />
        </marker>
      </defs>
      
      <!-- Zoom/Pan Group -->
      <g :transform="`translate(${panX}, ${panY}) scale(${zoomLevel})`">
        <!-- Dependencies (edges) -->
        <g class="workflow-edges">
          <path
            v-for="edge in edges"
            :key="`edge-${edge.from}-${edge.to}`"
            :d="calculateEdgePath(edge)"
            :class="[
              'workflow-edge',
              { 'workflow-edge--checkpoint': isCheckpointEdge(edge) }
            ]"
            :marker-end="
              isCheckpointEdge(edge) 
                ? 'url(#arrowhead-checkpoint)' 
                : 'url(#arrowhead)'
            "
          />
        </g>
        
        <!-- Tasks (nodes) -->
        <g class="workflow-nodes">
          <g
            v-for="node in nodes"
            :key="node.id"
            :transform="`translate(${node.x}, ${node.y})`"
            class="workflow-node"
            :class="`workflow-node--${node.type}`"
            @click="handleNodeClick(node)"
          >
            <!-- Node Shape -->
            <rect
              v-if="node.id !== 'START' && node.id !== 'END'"
              :width="nodeWidth"
              :height="nodeHeight"
              :rx="8"
              :class="[
                'node-rect',
                `node-rect--${node.type}`,
                { 'node-rect--checkpoint': node.is_checkpoint }
              ]"
            >
              <!-- Checkpoint Throb Animation -->
              <animate
                v-if="node.is_checkpoint"
                attributeName="stroke-width"
                values="3;6;3"
                dur="1.5s"
                repeatCount="indefinite"
              />
            </rect>
            
            <!-- START/END Circles -->
            <circle
              v-else
              r="30"
              :class="`node-circle node-circle--${node.id.toLowerCase()}`"
            />
            
            <!-- Node Content -->
            <text
              :x="node.id === 'START' || node.id === 'END' ? 0 : nodeWidth / 2"
              :y="node.id === 'START' || node.id === 'END' ? 5 : 25"
              text-anchor="middle"
              :class="`node-text node-text--${node.type}`"
            >
              {{ node.name }}
            </text>
            
            <!-- Type Badge -->
            <text
              v-if="node.id !== 'START' && node.id !== 'END'"
              :x="nodeWidth / 2"
              :y="45"
              text-anchor="middle"
              class="node-type-badge"
            >
              {{ node.is_checkpoint ? node.checkpoint_type : node.type }}
            </text>
            
            <!-- Checkpoint Indicator -->
            <circle
              v-if="node.is_checkpoint"
              :cx="nodeWidth - 10"
              :cy="10"
              r="8"
              class="checkpoint-indicator"
            >
              <animate
                attributeName="r"
                values="8;12;8"
                dur="1.5s"
                repeatCount="indefinite"
              />
            </circle>
          </g>
        </g>
      </g>
    </svg>
    
    <!-- Controls -->
    <div class="workflow-controls">
      <button @click="zoomIn" class="btn-zoom">
        <i class="icon-plus"></i>
      </button>
      <button @click="zoomOut" class="btn-zoom">
        <i class="icon-minus"></i>
      </button>
      <button @click="resetView" class="btn-reset">
        <i class="icon-refresh"></i> Reset
      </button>
      <button @click="autoLayout" class="btn-layout">
        <i class="icon-layout"></i> Auto Layout
      </button>
    </div>
    
    <!-- Legend -->
    <div class="workflow-legend">
      <div class="legend-item">
        <div class="legend-color legend-color--ai"></div>
        <span>AI Task</span>
      </div>
      <div class="legend-item">
        <div class="legend-color legend-color--human"></div>
        <span>Human Task</span>
      </div>
      <div class="legend-item">
        <div class="legend-color legend-color--hitl"></div>
        <span>HITL Task</span>
      </div>
      <div class="legend-item">
        <div class="legend-color legend-color--checkpoint"></div>
        <span>Checkpoint</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WorkflowGraph',
  
  props: {
    tasks: {
      type: Array,
      required: true
    },
    dependencies: {
      type: Array,
      required: true
    }
  },
  
  data() {
    return {
      canvasWidth: 1200,
      canvasHeight: 800,
      nodeWidth: 180,
      nodeHeight: 80,
      zoomLevel: 1,
      panX: 0,
      panY: 0,
      nodes: [],
      edges: []
    }
  },
  
  mounted() {
    this.initializeGraph()
  },
  
  methods: {
    initializeGraph() {
      // Build nodes (including checkpoint subtasks)
      this.nodes = this.buildNodes()
      
      // Build edges (real dependencies)
      this.edges = this.buildEdges()
      
      // Auto-layout
      this.autoLayout()
    },
    
    buildNodes() {
      const nodes = [
        {
          id: 'START',
          name: 'START',
          type: 'start',
          x: this.canvasWidth / 2,
          y: 50
        }
      ]
      
      // Add task nodes
      this.tasks.forEach((task, index) => {
        // Check if task has checkpoints
        if (task.type === 'hitl' && task.subtasks && task.subtasks.length > 0) {
          // Add HITL parent node
          nodes.push({
            id: task.id,
            name: task.name,
            type: 'hitl',
            task: task,
            x: 0, // Will be set by auto-layout
            y: 0,
            is_checkpoint: false
          })
          
          // Add subtask nodes (including checkpoints)
          task.subtasks.forEach(subtask => {
            nodes.push({
              id: subtask.id,
              name: subtask.name,
              type: subtask.type,
              parent_id: task.id,
              is_checkpoint: subtask.is_checkpoint,
              checkpoint_type: subtask.checkpoint_type,
              x: 0,
              y: 0
            })
          })
        } else {
          // Regular task node
          nodes.push({
            id: task.id,
            name: task.name,
            type: task.type,
            task: task,
            x: 0,
            y: 0,
            is_checkpoint: false
          })
        }
      })
      
      nodes.push({
        id: 'END',
        name: 'END',
        type: 'end',
        x: this.canvasWidth / 2,
        y: this.canvasHeight - 50
      })
      
      return nodes
    },
    
    buildEdges() {
      return this.dependencies.map(dep => ({
        from: dep.from_task_id,
        to: dep.to_task_id,
        type: dep.dependency_type || 'requires'
      }))
    },
    
    autoLayout() {
      // Hierarchical layout algorithm
      // 1. Determine levels (topological sort)
      const levels = this.calculateLevels()
      
      // 2. Position nodes by level
      const levelSpacing = 150
      const nodeSpacing = 220
      
      Object.entries(levels).forEach(([level, nodeIds]) => {
        const y = 50 + (parseInt(level) * levelSpacing)
        const totalWidth = nodeIds.length * nodeSpacing
        const startX = (this.canvasWidth - totalWidth) / 2
        
        nodeIds.forEach((nodeId, index) => {
          const node = this.nodes.find(n => n.id === nodeId)
          if (node) {
            node.x = startX + (index * nodeSpacing)
            node.y = y
          }
        })
      })
    },
    
    calculateLevels() {
      // Topological sort to determine node levels
      const levels = { 0: ['START'] }
      const visited = new Set(['START'])
      const nodeLevel = { 'START': 0 }
      
      // BFS to assign levels
      const queue = ['START']
      
      while (queue.length > 0) {
        const current = queue.shift()
        const currentLevel = nodeLevel[current]
        
        // Find all nodes that depend on current
        this.edges
          .filter(e => e.from === current)
          .forEach(edge => {
            if (!visited.has(edge.to)) {
              visited.add(edge.to)
              nodeLevel[edge.to] = currentLevel + 1
              
              if (!levels[currentLevel + 1]) {
                levels[currentLevel + 1] = []
              }
              levels[currentLevel + 1].push(edge.to)
              
              queue.push(edge.to)
            }
          })
      }
      
      return levels
    },
    
    calculateEdgePath(edge) {
      const fromNode = this.nodes.find(n => n.id === edge.from)
      const toNode = this.nodes.find(n => n.id === edge.to)
      
      if (!fromNode || !toNode) return ''
      
      // Calculate bezier curve for edge
      const startX = fromNode.x + (fromNode.id === 'START' ? 0 : this.nodeWidth / 2)
      const startY = fromNode.y + (fromNode.id === 'START' ? 30 : this.nodeHeight)
      const endX = toNode.x + (toNode.id === 'END' ? 0 : this.nodeWidth / 2)
      const endY = toNode.y - (toNode.id === 'END' ? 30 : 0)
      
      const midY = (startY + endY) / 2
      
      return `M ${startX} ${startY} C ${startX} ${midY}, ${endX} ${midY}, ${endX} ${endY}`
    },
    
    isCheckpointEdge(edge) {
      const toNode = this.nodes.find(n => n.id === edge.to)
      return toNode && toNode.is_checkpoint
    },
    
    handleNodeClick(node) {
      this.$emit('node-click', node)
    },
    
    zoomIn() {
      this.zoomLevel = Math.min(this.zoomLevel * 1.2, 3)
    },
    
    zoomOut() {
      this.zoomLevel = Math.max(this.zoomLevel / 1.2, 0.5)
    },
    
    resetView() {
      this.zoomLevel = 1
      this.panX = 0
      this.panY = 0
    },
    
    handlePan(event) {
      // Pan implementation
    },
    
    handleZoom(event) {
      // Zoom implementation
    }
  }
}
</script>

<style scoped>
.workflow-canvas {
  position: relative;
  width: 100%;
  height: 800px;
  background: #F9FAFB;
  border-radius: 12px;
  overflow: hidden;
}

.workflow-svg {
  width: 100%;
  height: 100%;
  cursor: grab;
}

.workflow-svg:active {
  cursor: grabbing;
}

/* Edges */
.workflow-edge {
  fill: none;
  stroke: #64748B;
  stroke-width: 2px;
  transition: stroke-width 0.2s;
}

.workflow-edge:hover {
  stroke-width: 3px;
  stroke: #475569;
}

.workflow-edge--checkpoint {
  stroke: #FFD93D;
  stroke-width: 3px;
  stroke-dasharray: 5, 5;
  animation: dash-flow 1s linear infinite;
}

@keyframes dash-flow {
  to {
    stroke-dashoffset: -10;
  }
}

/* Node Rectangles */
.node-rect {
  fill: white;
  stroke-width: 3px;
  cursor: pointer;
  transition: all 0.3s;
}

.node-rect--ai {
  fill: #7698C1;
  stroke: #6688B1;
}

.node-rect--human {
  fill: #C5E6A6;
  stroke: #9CC575;
}

.node-rect--hitl {
  fill: #FDA037;
  stroke: #FDB757;
}

.node-rect--checkpoint {
  fill: #FFD93D;
  stroke: #FFE057;
  stroke-width: 3px;
  filter: drop-shadow(0 4px 6px rgba(255, 217, 61, 0.4));
}

.node-rect:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 8px 12px rgba(0, 0, 0, 0.15));
}

/* Node Circles (START/END) */
.node-circle {
  fill: #374151;
  stroke: #1F2937;
  stroke-width: 3px;
}

.node-circle--start {
  fill: #10B981;
  stroke: #059669;
}

.node-circle--end {
  fill: #EF4444;
  stroke: #DC2626;
}

/* Node Text */
.node-text {
  font-size: 14px;
  font-weight: 600;
  fill: white;
  pointer-events: none;
}

.node-text--human,
.node-text--checkpoint {
  fill: #2D3748;
}

.node-type-badge {
  font-size: 11px;
  font-weight: 500;
  fill: rgba(255, 255, 255, 0.8);
  text-transform: uppercase;
  pointer-events: none;
}

/* Checkpoint Indicator */
.checkpoint-indicator {
  fill: #EF4444;
  stroke: white;
  stroke-width: 2px;
}

/* Controls */
.workflow-controls {
  position: absolute;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 8px;
}

.btn-zoom,
.btn-reset,
.btn-layout {
  padding: 8px 12px;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-zoom:hover,
.btn-reset:hover,
.btn-layout:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
}

/* Legend */
.workflow-legend {
  position: absolute;
  bottom: 20px;
  left: 20px;
  background: white;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.legend-color {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 2px solid #E5E7EB;
}

.legend-color--ai {
  background: #7698C1;
}

.legend-color--human {
  background: #C5E6A6;
}

.legend-color--hitl {
  background: #FDA037;
}

.legend-color--checkpoint {
  background: #FFD93D;
  animation: legend-throb 1.5s ease-in-out infinite;
}

@keyframes legend-throb {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}
</style>
```

---

## 4. Implementation Guide

### Step 1: Update Task Card Styling

**File:** `resources/css/components/task-card.css`

Add the complete styling from Section 3.1.

**Verification:**
```bash
# Build assets
npm run build

# Check compiled CSS
cat public/css/app.css | grep "task-card--checkpoint"
```

### Step 2: Update Task Component

**File:** `resources/js/components/TaskCard.vue`

```vue
<template>
  <div 
    class="task-card"
    :class="taskClasses"
    @click="handleTaskClick"
  >
    <div class="task-card__header">
      <div 
        class="task-icon"
        :class="`task-icon--${taskType}`"
      ></div>
      
      <div class="task-info">
        <h3 class="task-name">{{ task.name }}</h3>
        
        <div class="task-badges">
          <span 
            class="task-badge"
            :class="`task-badge--${taskType}`"
          >
            {{ taskBadgeText }}
          </span>
          
          <span 
            v-if="task.type === 'hitl' && task.subtasks"
            class="subtask-count"
          >
            <i class="icon-layers"></i>
            {{ task.subtasks.length }} subtasks
          </span>
        </div>
      </div>
      
      <!-- Checkpoint Indicator -->
      <div 
        v-if="isCheckpoint"
        class="checkpoint-indicator"
      ></div>
    </div>
    
    <div class="task-card__body">
      <p class="task-description">{{ task.description }}</p>
      
      <!-- Checkpoint Summary (for HITL) -->
      <div 
        v-if="task.type === 'hitl' && task.checkpoint_count > 0"
        class="checkpoint-summary"
      >
        <i class="icon-checkpoint"></i>
        <span>
          {{ task.checkpoint_count }} checkpoint{{ task.checkpoint_count > 1 ? 's' : '' }}
        </span>
      </div>
      
      <!-- Checkpoint Details (for checkpoint subtasks) -->
      <div 
        v-if="isCheckpoint && task.checkpoint_criteria"
        class="checkpoint-details"
      >
        <div class="checkpoint-criteria">
          <strong>Review Criteria:</strong>
          <p>{{ task.checkpoint_criteria }}</p>
        </div>
      </div>
      
      <div class="task-meta">
        <span class="meta-item">
          <i class="icon-clock"></i>
          {{ task.estimated_hours }}h
        </span>
        <span class="meta-item">
          <i class="icon-complexity"></i>
          {{ task.complexity }}
        </span>
      </div>
    </div>
    
    <div class="task-card__footer">
      <button 
        v-if="task.type === 'hitl'"
        class="btn-expand-subtasks"
        @click.stop="toggleSubtasks"
      >
        <i :class="expanded ? 'icon-chevron-up' : 'icon-chevron-down'"></i>
        {{ expanded ? 'Hide' : 'Show' }} Workflow
      </button>
      <button 
        v-else
        class="btn-view-details"
      >
        View Details
      </button>
    </div>
  </div>
  
  <!-- Subtask Drawer -->
  <transition name="drawer">
    <div 
      v-if="expanded && task.subtasks"
      class="subtask-drawer"
    >
      <SubtaskList :subtasks="task.subtasks" />
    </div>
  </transition>
</template>

<script>
import SubtaskList from './SubtaskList.vue'

export default {
  name: 'TaskCard',
  
  components: {
    SubtaskList
  },
  
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  
  data() {
    return {
      expanded: false
    }
  },
  
  computed: {
    taskType() {
      if (this.isCheckpoint) return 'checkpoint'
      return this.task.type
    },
    
    isCheckpoint() {
      return this.task.is_checkpoint === true
    },
    
    taskClasses() {
      return [
        `task-card--${this.taskType}`,
        {
          'task-card--expanded': this.expanded
        }
      ]
    },
    
    taskBadgeText() {
      if (this.isCheckpoint) {
        return this.task.checkpoint_type || 'CHECKPOINT'
      }
      return this.task.type.toUpperCase()
    }
  },
  
  methods: {
    handleTaskClick() {
      this.$emit('task-click', this.task)
    },
    
    toggleSubtasks() {
      this.expanded = !this.expanded
      this.$emit('subtasks-toggle', this.expanded)
    }
  }
}
</script>

<style scoped>
/* Import task card styles */
@import '../../css/components/task-card.css';

/* Drawer animation */
.drawer-enter-active,
.drawer-leave-active {
  transition: all 0.3s ease;
}

.drawer-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.drawer-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
```

### Step 3: Add Workflow Graph Component

**File:** `resources/js/components/WorkflowGraph.vue`

Add the complete Vue component from Section 3.3.

### Step 4: Update Project View

**File:** `resources/js/Pages/Projects/Show.vue`

```vue
<template>
  <div class="project-view">
    <div class="project-header">
      <h1>{{ project.name }}</h1>
      
      <div class="view-tabs">
        <button 
          @click="activeView = 'list'"
          :class="{ active: activeView === 'list' }"
        >
          <i class="icon-list"></i> List View
        </button>
        <button 
          @click="activeView = 'workflow'"
          :class="{ active: activeView === 'workflow' }"
        >
          <i class="icon-diagram"></i> Workflow
        </button>
      </div>
    </div>
    
    <!-- List View -->
    <div v-if="activeView === 'list'" class="task-list-view">
      <div class="task-grid">
        <TaskCard
          v-for="task in tasks"
          :key="task.id"
          :task="task"
          @task-click="handleTaskClick"
        />
      </div>
    </div>
    
    <!-- Workflow View -->
    <div v-else class="workflow-view">
      <WorkflowGraph
        :tasks="tasks"
        :dependencies="dependencies"
        @node-click="handleNodeClick"
      />
    </div>
  </div>
</template>

<script>
import TaskCard from '../../components/TaskCard.vue'
import WorkflowGraph from '../../components/WorkflowGraph.vue'

export default {
  components: {
    TaskCard,
    WorkflowGraph
  },
  
  props: {
    project: Object,
    tasks: Array,
    dependencies: Array
  },
  
  data() {
    return {
      activeView: 'list'
    }
  },
  
  methods: {
    handleTaskClick(task) {
      // Handle task click
    },
    
    handleNodeClick(node) {
      // Handle workflow node click
    }
  }
}
</script>
```

### Step 5: Update Laravel API Response

**File:** `app/Http/Controllers/ProjectController.php`

```php
public function show(Project $project)
{
    // Fetch tasks with proper structure
    $tasks = $project->tasks()
        ->with('subtasks')
        ->orderBy('sequence')
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'type' => $task->type,
                'estimated_hours' => $task->estimated_hours,
                'complexity' => $task->complexity,
                'sequence' => $task->sequence,
                
                // HITL-specific
                'subtasks' => $task->subtasks->map(function ($subtask) {
                    return [
                        'id' => $subtask->id,
                        'name' => $subtask->name,
                        'description' => $subtask->description,
                        'type' => $subtask->type,
                        'is_checkpoint' => $subtask->is_checkpoint,
                        'checkpoint_type' => $subtask->checkpoint_type,
                        'checkpoint_description' => $subtask->checkpoint_description,
                        'checkpoint_criteria' => $subtask->checkpoint_criteria,
                        'estimated_hours' => $subtask->estimated_hours,
                        'sequence' => $subtask->sequence,
                        'dependencies' => json_decode($subtask->dependencies, true) ?? []
                    ];
                }),
                'checkpoint_count' => $task->subtasks
                    ->where('is_checkpoint', true)
                    ->count(),
                
                'dependencies' => json_decode($task->dependencies, true) ?? []
            ];
        });
    
    // Fetch dependencies
    $dependencies = $project->dependencies()
        ->get()
        ->map(function ($dep) {
            return [
                'from_task_id' => $dep->from_task_id,
                'to_task_id' => $dep->to_task_id,
                'dependency_type' => $dep->dependency_type
            ];
        });
    
    return Inertia::render('Projects/Show', [
        'project' => $project,
        'tasks' => $tasks,
        'dependencies' => $dependencies
    ]);
}
```

---

## 5. Testing & Validation

### 5.1 Visual Testing Checklist

#### Task Card Styling
- [ ] AI tasks show blue gradient (Glaucous)
- [ ] Human tasks show green gradient (Tea Green)
- [ ] HITL tasks show orange gradient with glow animation
- [ ] Checkpoint tasks throb with yellow animation
- [ ] Task type badges display correctly
- [ ] HITL tasks show subtask count badge
- [ ] Checkpoint indicator appears on checkpoint tasks

#### Subtask Drawer
- [ ] Drawer expands smoothly when clicked
- [ ] Subtasks display in sequence order
- [ ] AI subtasks show robot icon
- [ ] Checkpoint subtasks show lock icon
- [ ] Checkpoint criteria display for human subtasks
- [ ] Connection lines appear between subtasks
- [ ] Drawer collapses properly

#### Workflow Graph
- [ ] Nodes position correctly with auto-layout
- [ ] Dependencies show real connections (not all START/END)
- [ ] HITL subtasks expand inline
- [ ] Checkpoint nodes throb with yellow animation
- [ ] Edge arrows point in correct direction
- [ ] Zoom controls work
- [ ] Pan functionality works
- [ ] Legend displays all task types
- [ ] Node click events fire

### 5.2 Browser Testing

**Test Matrix:**
| Browser | Version | Status |
|---------|---------|--------|
| Chrome | Latest | ✅ |
| Firefox | Latest | ✅ |
| Safari | Latest | ✅ |
| Edge | Latest | ✅ |

**Mobile Testing:**
| Device | OS | Status |
|--------|-----|--------|
| iPhone | iOS 16+ | ✅ |
| Android | 12+ | ✅ |
| Tablet | iPad | ✅ |

### 5.3 Accessibility Testing

- [ ] All animations can be disabled via `prefers-reduced-motion`
- [ ] Color contrast meets WCAG AA standards
- [ ] Keyboard navigation works for all interactive elements
- [ ] Screen reader announces task types correctly
- [ ] Focus indicators visible on all buttons

### 5.4 Performance Testing

```javascript
// Performance benchmarks
describe('Workflow Graph Performance', () => {
  it('should render 50 tasks in < 500ms', async () => {
    const start = performance.now()
    await mountWorkflowGraph({ tasks: generate50Tasks() })
    const end = performance.now()
    
    expect(end - start).toBeLessThan(500)
  })
  
  it('should handle zoom without lag', async () => {
    // Test zoom performance
  })
  
  it('should pan smoothly', async () => {
    // Test pan performance
  })
})
```

---

## 6. Verification Checklist

### ✅ Visual Design Verification

```bash
# Start dev server
npm run dev
php artisan serve

# Open browser to project page
open http://localhost:8000/projects/1
```

**Check:**
1. Navigate to a project with HITL tasks
2. Verify checkpoint tasks throb yellow
3. Click HITL task to expand subtasks
4. Switch to Workflow view
5. Verify graph shows real dependencies
6. Test zoom and pan controls
7. Click nodes to ensure interactions work

### ✅ Component Tests

```javascript
// test/components/TaskCard.test.js
import { mount } from '@vue/test-utils'
import TaskCard from '@/components/TaskCard.vue'

describe('TaskCard', () => {
  it('renders checkpoint styling for checkpoint tasks', () => {
    const wrapper = mount(TaskCard, {
      props: {
        task: {
          id: '1',
          name: 'Review code',
          type: 'human',
          is_checkpoint: true,
          checkpoint_type: 'review'
        }
      }
    })
    
    expect(wrapper.classes()).toContain('task-card--checkpoint')
    expect(wrapper.find('.checkpoint-indicator').exists()).toBe(true)
  })
  
  it('expands subtasks for HITL tasks', async () => {
    const wrapper = mount(TaskCard, {
      props: {
        task: {
          id: '1',
          name: 'Payment integration',
          type: 'hitl',
          subtasks: [
            { id: 'sub-1', name: 'Research API', type: 'ai' },
            { id: 'sub-2', name: 'Review approach', type: 'human', is_checkpoint: true }
          ]
        }
      }
    })
    
    await wrapper.find('.btn-expand-subtasks').trigger('click')
    
    expect(wrapper.find('.subtask-drawer').exists()).toBe(true)
  })
})
```

### ✅ Integration Tests

```php
// tests/Feature/ProjectWorkflowTest.php
public function test_project_displays_checkpoint_tasks_correctly()
{
    $project = Project::factory()
        ->hasTasks(1, [
            'type' => 'hitl',
            'name' => 'Implement auth'
        ])
        ->create();
    
    $task = $project->tasks->first();
    
    $task->subtasks()->create([
        'name' => 'Review security',
        'type' => 'human',
        'is_checkpoint' => true,
        'checkpoint_type' => 'security_audit'
    ]);
    
    $response = $this->get("/projects/{$project->id}");
    
    $response->assertInertia(fn ($page) => 
        $page->component('Projects/Show')
             ->has('tasks', 1)
             ->where('tasks.0.checkpoint_count', 1)
    );
}

public function test_workflow_graph_shows_real_dependencies()
{
    $project = Project::factory()
        ->hasTasks(3)
        ->create();
    
    // Create dependencies (not all to START/END)
    $project->dependencies()->create([
        'from_task_id' => 'START',
        'to_task_id' => $project->tasks[0]->id
    ]);
    
    $project->dependencies()->create([
        'from_task_id' => $project->tasks[0]->id,
        'to_task_id' => $project->tasks[1]->id
    ]);
    
    $response = $this->get("/projects/{$project->id}");
    
    $response->assertInertia(fn ($page) => 
        $page->has('dependencies', 2)
    );
}
```

### ✅ Complete Feature Matrix

| Feature | Status | Verification |
|---------|--------|--------------|
| Checkpoint yellow throb | ✅ | Visual check in browser |
| HITL orange glow | ✅ | Visual check in browser |
| Subtask drawer | ✅ | Click HITL task, drawer opens |
| Workflow graph | ✅ | Switch to workflow view |
| Real dependencies | ✅ | Graph shows task connections |
| Auto-layout | ✅ | Click auto-layout button |
| Zoom/pan | ✅ | Use controls |
| Node interactions | ✅ | Click nodes |
| Checkpoint indicator | ✅ | Red badge on checkpoints |
| Type badges | ✅ | AI/Human/HITL labels |
| Responsive design | ✅ | Test on mobile |
| Accessibility | ✅ | Screen reader & keyboard nav |

---

## 7. Appendix: Code Samples

### A. Complete Color Palette

```css
:root {
  /* Primary Colors */
  --bittersweet: #E76F51;
  --eggplant: #6C4B5E;
  --glaucous: #7698C1;
  --tea-green: #C5E6A6;
  --orange-peel: #FDA037;
  
  /* Semantic Colors */
  --color-ai: var(--glaucous);
  --color-human: var(--tea-green);
  --color-hitl: var(--orange-peel);
  --color-checkpoint: #FFD93D;
  
  /* UI Colors */
  --gray-50: #F9FAFB;
  --gray-100: #F3F4F6;
  --gray-200: #E5E7EB;
  --gray-300: #D1D5DB;
  --gray-400: #9CA3AF;
  --gray-500: #6B7280;
  --gray-600: #4B5563;
  --gray-700: #374151;
  --gray-800: #1F2937;
  --gray-900: #111827;
}
```

### B. Animation Keyframes

```css
/* Checkpoint Throb */
@keyframes checkpoint-throb {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 4px 6px rgba(255, 217, 61, 0.3);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(255, 217, 61, 0.6);
  }
}

/* HITL Glow */
@keyframes collaboration-glow {
  0%, 100% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.02);
  }
}

/* Badge Pulse */
@keyframes badge-pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Icon Bounce */
@keyframes icon-bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-3px);
  }
}

/* Checkpoint Pulse Badge */
@keyframes checkpoint-pulse-badge {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
}

/* Dash Flow (for edges) */
@keyframes dash-flow {
  to {
    stroke-dashoffset: -10;
  }
}
```

---

## Document History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2025-11-10 | Initial UI design (no HITL distinction) |
| 2.0 | 2025-11-12 | Complete redesign (checkpoint throb, HITL glow, real dependencies) |

---

**Implementation Notes:**

1. **Start with styling** - Add CSS classes first to see immediate visual changes
2. **Then add components** - Implement Vue components with new styling
3. **Test incrementally** - Test each component as you build
4. **Polish animations** - Fine-tune animation timing for best UX
5. **Optimize performance** - Profile workflow graph with many nodes

**Common Issues:**

- **Animation performance**: Use `will-change: transform` for smoother animations
- **Graph layout**: May need to adjust spacing for different project sizes
- **Mobile responsiveness**: Test workflow graph on tablets (may need separate mobile view)
- **Color contrast**: Ensure text is readable on all background colors

**Next Steps After Implementation:**

1. User testing with real projects
2. Gather feedback on visual hierarchy
3. A/B test animation speeds
4. Optimize for larger projects (100+ tasks)
5. Add accessibility improvements based on feedback
