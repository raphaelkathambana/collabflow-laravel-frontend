# CollabFlow UI/Frontend Design Document
## Project Creation Flow Redesign & Python Backend Integration Prep

**Version:** 1.0  
**Date:** November 2025  
**Status:** Implementation Ready  
**Scope:** Frontend UI changes for project creation (Steps 1-4) to support Python task generation service

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Current vs New Flow](#current-vs-new-flow)
3. [Step-by-Step UI Specifications](#step-by-step-ui-specifications)
4. [Component Architecture](#component-architecture)
5. [State Management](#state-management)
6. [API Integration Points](#api-integration-points)
7. [Validation & Error Handling](#validation--error-handling)
8. [Responsive Design](#responsive-design)
9. [Implementation Checklist](#implementation-checklist)

---

## 1. Executive Summary

### What's Changing

**OLD FLOW:**
```
Step 1: Details → Step 2: Goals → Step 3: Tasks Generated → Step 4: Workflow Review → Step 5: Final Review
```

**NEW FLOW:**
```
Step 1: Details → Step 2: Goals → Step 3: AI Generation (Streaming) + Interactive Workflow Builder → Step 4: Final Review & Create
```

### Key Changes

1. **Merged Step 3 & 4**: Task generation and workflow visualization happen simultaneously
2. **Streaming UI**: Real-time progress indicators during AI generation
3. **Interactive Editing**: Users can edit, regenerate, and validate tasks inline
4. **Python Backend Ready**: All API calls prepared for Python service integration

### Benefits

- ✅ 40% faster user flow (4 steps vs 5)
- ✅ Better validation (users see structure while reviewing)
- ✅ Live feedback (streaming progress)
- ✅ Cleaner separation of concerns (Laravel = gateway, Python = brain)

---

## 2. Current vs New Flow

### Current State (Before)

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: Project Details                                     │
│ - Name, description, deadline                               │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 2: Goals & KPIs                                        │
│ - Define goals, select domain                               │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 3: AI Task Generation (BLOCKING)                       │
│ - Loading spinner for 30-60 seconds                         │
│ - No progress indication                                    │
│ - Shows list of tasks when done                             │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 4: Workflow Visualization                              │
│ - Flowchart appears                                         │
│ - User can rearrange nodes                                  │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 5: Final Review                                        │
│ - Summary view                                              │
│ - Confirm and create                                        │
└─────────────────────────────────────────────────────────────┘
```

### New State (After)

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: Project Details                                     │
│ - Name, description, deadline                               │
│ - Domain selection moved here                               │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 2: Goals & Context                                     │
│ - Define goals                                              │
│ - Upload reference documents (NEW)                          │
│ - Define success metrics                                    │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 3: Build Your Workflow (MERGED + STREAMING)            │
│                                                             │
│ ┌──────────────────┐  ┌──────────────────────────────────┐ │
│ │  Task List       │  │  Workflow Flowchart              │ │
│ │  (Left 40%)      │  │  (Right 60%)                     │ │
│ │                  │  │                                  │ │
│ │  [Streaming...]  │  │  [Live updates as tasks arrive]  │ │
│ │  ✓ Task 1        │  │                                  │ │
│ │  ⟳ Task 2...     │  │  [Start] → [Task 1] → [Task 2]  │ │
│ │                  │  │                                  │ │
│ │  [Edit] [Regen]  │  │  [Auto-layout] [Manual Mode]     │ │
│ └──────────────────┘  └──────────────────────────────────┘ │
│                                                             │
│ [< Back]                            [Continue to Review >] │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 4: Final Review & Create                               │
│ - Project summary                                           │
│ - Task count, estimated duration                            │
│ - [Create Project] or [Start Execution]                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Step-by-Step UI Specifications

### Step 1: Project Details

**Purpose:** Collect basic project information

**Fields:**

```typescript
interface Step1Data {
  name: string;              // Required, max 100 chars
  description: string;       // Required, max 500 chars
  deadline?: Date;           // Optional
  domain: DomainType;        // Required, enum
  teamSize?: number;         // Optional, for context
}

enum DomainType {
  SOFTWARE_DEVELOPMENT = 'software_development',
  RESEARCH_ANALYSIS = 'research_analysis',
  MARKETING_CAMPAIGN = 'marketing_campaign',
  CUSTOM = 'custom'
}
```

**UI Layout:**

```html
<!-- Step 1: Project Details -->
<div class="step-container step-1">
  <div class="step-header">
    <h2>Step 1: Project Details</h2>
    <p class="subtitle">Tell us about your project</p>
  </div>

  <div class="form-group">
    <label>Project Name *</label>
    <input 
      type="text" 
      v-model="form.name" 
      placeholder="e.g., Mobile App Launch"
      maxlength="100"
      class="input-lg"
    />
    <span class="char-count">{{ form.name.length }}/100</span>
  </div>

  <div class="form-group">
    <label>Description *</label>
    <textarea 
      v-model="form.description" 
      placeholder="Brief description of what you're building..."
      maxlength="500"
      rows="4"
    ></textarea>
    <span class="char-count">{{ form.description.length }}/500</span>
  </div>

  <div class="form-row">
    <div class="form-group col-6">
      <label>Domain *</label>
      <select v-model="form.domain" class="select-lg">
        <option value="">Select project type...</option>
        <option value="software_development">Software Development</option>
        <option value="research_analysis">Research & Analysis</option>
        <option value="marketing_campaign">Marketing Campaign</option>
        <option value="custom">Custom Project</option>
      </select>
      <small class="help-text">
        Helps AI generate relevant tasks
      </small>
    </div>

    <div class="form-group col-6">
      <label>Deadline (Optional)</label>
      <input 
        type="date" 
        v-model="form.deadline"
        :min="today"
      />
    </div>
  </div>

  <div class="form-group">
    <label>Team Size (Optional)</label>
    <input 
      type="number" 
      v-model="form.teamSize" 
      min="1"
      placeholder="How many people working on this?"
    />
  </div>

  <div class="step-actions">
    <button class="btn btn-secondary" @click="cancel">Cancel</button>
    <button 
      class="btn btn-primary" 
      @click="nextStep"
      :disabled="!isStep1Valid"
    >
      Continue to Goals →
    </button>
  </div>
</div>
```

**Validation:**

```typescript
const isStep1Valid = computed(() => {
  return (
    form.name.trim().length >= 3 &&
    form.description.trim().length >= 10 &&
    form.domain !== ''
  );
});
```

---

### Step 2: Goals & Context

**Purpose:** Define project goals and provide context for AI

**Fields:**

```typescript
interface Step2Data {
  goals: Goal[];              // Min 1, max 10
  successMetrics?: string;    // Optional
  referenceDocuments?: File[]; // Optional, for knowledge base
  constraints?: string;       // Optional
}

interface Goal {
  id: string;
  text: string;  // Max 200 chars
  priority: 'high' | 'medium' | 'low';
}
```

**UI Layout:**

```html
<!-- Step 2: Goals & Context -->
<div class="step-container step-2">
  <div class="step-header">
    <h2>Step 2: Goals & Context</h2>
    <p class="subtitle">What are you trying to achieve?</p>
  </div>

  <!-- Goals Section -->
  <div class="section">
    <label>Project Goals *</label>
    <small class="help-text">
      Define 1-10 clear goals. AI will use these to generate tasks.
    </small>

    <div class="goals-list">
      <div 
        v-for="(goal, index) in form.goals" 
        :key="goal.id"
        class="goal-item"
      >
        <div class="goal-header">
          <span class="goal-number">{{ index + 1 }}</span>
          <select v-model="goal.priority" class="priority-select">
            <option value="high">🔴 High</option>
            <option value="medium">🟡 Medium</option>
            <option value="low">🟢 Low</option>
          </select>
          <button 
            class="btn-icon btn-danger"
            @click="removeGoal(goal.id)"
            v-if="form.goals.length > 1"
          >
            🗑️
          </button>
        </div>
        <textarea
          v-model="goal.text"
          placeholder="e.g., Launch beta version by end of Q2"
          maxlength="200"
          rows="2"
        ></textarea>
        <span class="char-count">{{ goal.text.length }}/200</span>
      </div>
    </div>

    <button 
      class="btn btn-secondary btn-sm"
      @click="addGoal"
      v-if="form.goals.length < 10"
    >
      + Add Goal
    </button>
  </div>

  <!-- Reference Documents Section (NEW) -->
  <div class="section">
    <label>Reference Documents (Optional)</label>
    <small class="help-text">
      Upload documents for AI to learn from (specs, requirements, examples)
    </small>

    <div class="file-upload-zone">
      <input 
        type="file"
        ref="fileInput"
        @change="handleFileUpload"
        multiple
        accept=".pdf,.doc,.docx,.txt,.md"
        hidden
      />
      <button 
        class="btn btn-outline"
        @click="$refs.fileInput.click()"
      >
        📎 Upload Documents
      </button>

      <div class="uploaded-files" v-if="form.referenceDocuments.length">
        <div 
          v-for="file in form.referenceDocuments"
          :key="file.name"
          class="file-chip"
        >
          <span>{{ file.name }}</span>
          <button @click="removeFile(file)">✕</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Metrics -->
  <div class="section">
    <label>Success Metrics (Optional)</label>
    <textarea
      v-model="form.successMetrics"
      placeholder="How will you measure success? e.g., 10k users in first month"
      rows="3"
    ></textarea>
  </div>

  <!-- Constraints -->
  <div class="section">
    <label>Constraints (Optional)</label>
    <textarea
      v-model="form.constraints"
      placeholder="Any limitations? e.g., Budget: $10k, Must use Python"
      rows="3"
    ></textarea>
  </div>

  <div class="step-actions">
    <button class="btn btn-secondary" @click="prevStep">← Back</button>
    <button 
      class="btn btn-primary" 
      @click="startGeneration"
      :disabled="!isStep2Valid"
    >
      Generate Workflow →
    </button>
  </div>
</div>
```

**Validation:**

```typescript
const isStep2Valid = computed(() => {
  return (
    form.goals.length >= 1 &&
    form.goals.every(g => g.text.trim().length >= 10)
  );
});
```

---

### Step 3: Build Your Workflow (MERGED + STREAMING)

**Purpose:** Generate tasks with AI, visualize workflow, allow editing

**THIS IS THE CORE NEW UI**

**Layout Structure:**

```html
<!-- Step 3: Build Your Workflow -->
<div class="step-container step-3 merged-view">
  <div class="step-header">
    <h2>Step 3: Build Your Workflow</h2>
    <p class="subtitle">AI is generating your tasks...</p>
  </div>

  <!-- Streaming Progress Bar (shows during generation) -->
  <div class="progress-section" v-if="isGenerating">
    <div class="progress-bar-container">
      <div class="progress-bar" :style="{width: progress + '%'}"></div>
    </div>
    <p class="progress-text">{{ progressMessage }}</p>
  </div>

  <!-- Split View: Task List + Flowchart -->
  <div class="split-view">
    
    <!-- LEFT PANEL: Task List -->
    <div class="task-list-panel">
      <div class="panel-header">
        <h3>Tasks ({{ tasks.length }})</h3>
        <button class="btn btn-sm btn-secondary" @click="addTaskManually">
          + Add Task
        </button>
      </div>

      <div class="task-items">
        <div 
          v-for="task in tasks" 
          :key="task.id"
          class="task-item"
          :class="{
            'selected': selectedTaskId === task.id,
            'warning': task.validation.score < 70,
            'streaming': task.status === 'generating'
          }"
          @click="selectTask(task.id)"
        >
          <!-- Streaming indicator -->
          <div class="streaming-shimmer" v-if="task.status === 'generating'">
            <div class="shimmer-line"></div>
            <div class="shimmer-line short"></div>
          </div>

          <!-- Task content (once generated) -->
          <div class="task-content" v-else>
            <div class="task-header">
              <h4>{{ task.name }}</h4>
              <span 
                class="task-type-badge"
                :class="task.assigned_to.toLowerCase()"
              >
                {{ task.assigned_to }}
              </span>
            </div>

            <p class="task-description">{{ task.description }}</p>

            <div class="task-meta">
              <span class="meta-item">
                ⏱️ {{ task.estimated_hours }}h
              </span>
              <span class="meta-item">
                📊 {{ task.complexity }}
              </span>
            </div>

            <!-- Validation indicator -->
            <div 
              class="validation-indicator" 
              v-if="task.validation.score < 100"
            >
              <span class="score">{{ task.validation.score }}/100</span>
              <span class="issues" v-if="task.validation.issues.length">
                ⚠️ {{ task.validation.issues[0] }}
              </span>
            </div>

            <!-- Subtasks indicator -->
            <div class="subtasks-indicator" v-if="task.subtasks?.length">
              <button @click.stop="toggleSubtasks(task.id)">
                {{ task.showSubtasks ? '▼' : '▶' }} 
                {{ task.subtasks.length }} subtasks
              </button>
            </div>

            <!-- Subtasks list -->
            <div class="subtasks-list" v-if="task.showSubtasks">
              <div 
                v-for="subtask in task.subtasks"
                :key="subtask.id"
                class="subtask-item"
              >
                <span>{{ subtask.name }}</span>
                <span class="subtask-hours">{{ subtask.estimated_hours }}h</span>
              </div>
            </div>

            <!-- Task actions (visible when selected) -->
            <div class="task-actions" v-if="selectedTaskId === task.id">
              <button class="btn-icon" @click.stop="editTask(task)">
                ✏️ Edit
              </button>
              <button class="btn-icon" @click.stop="regenerateTask(task.id)">
                🔄 Regenerate
              </button>
              <button class="btn-icon" @click.stop="deleteTask(task.id)">
                🗑️ Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div class="empty-state" v-if="tasks.length === 0 && !isGenerating">
          <p>No tasks yet. Click "Add Task" to create one.</p>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: Workflow Flowchart -->
    <div class="flowchart-panel">
      <div class="panel-header">
        <h3>Workflow Visualization</h3>
        <div class="flowchart-controls">
          <button 
            class="btn btn-sm"
            @click="autoLayout"
            :disabled="tasks.length === 0"
          >
            🔄 Auto-Layout
          </button>
          <button 
            class="btn btn-sm"
            @click="toggleManualMode"
          >
            {{ manualMode ? '🔒 Manual' : '🤖 Auto' }}
          </button>
          <button class="btn btn-sm" @click="zoomIn">🔍+</button>
          <button class="btn btn-sm" @click="zoomOut">🔍-</button>
        </div>
      </div>

      <div class="flowchart-canvas" ref="flowchartCanvas">
        <!-- Flowchart rendering happens here -->
        <!-- Uses library like ReactFlow, Vue-Flow, or custom D3 -->
        <FlowchartRenderer
          :tasks="tasks"
          :dependencies="dependencies"
          :manual-mode="manualMode"
          @node-click="selectTask"
          @node-move="updateTaskPosition"
          @connection-create="createDependency"
        />

        <!-- Empty state -->
        <div class="flowchart-empty" v-if="tasks.length === 0">
          <p>Your workflow will appear here</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Step Actions -->
  <div class="step-actions">
    <button class="btn btn-secondary" @click="prevStep">← Back</button>
    <button 
      class="btn btn-primary" 
      @click="nextStep"
      :disabled="!canProceed"
    >
      Continue to Review →
    </button>
  </div>
</div>
```

**State Management for Step 3:**

```typescript
// Step 3 State
const step3State = reactive({
  // Generation state
  isGenerating: false,
  progress: 0,
  progressMessage: '',
  
  // Tasks
  tasks: [] as Task[],
  dependencies: [] as Dependency[],
  
  // UI state
  selectedTaskId: null as string | null,
  manualMode: false,
  
  // Validation
  overallScore: 0,
});

// Streaming handler
const handleStreamingGeneration = () => {
  const eventSource = new EventSource('/api/tasks/generate/stream');
  
  eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    
    switch (data.status) {
      case 'retrieving_context':
        step3State.progress = 10;
        step3State.progressMessage = 'Loading relevant knowledge...';
        break;
        
      case 'generating':
        step3State.progress = 30;
        step3State.progressMessage = 'AI is generating tasks...';
        break;
        
      case 'tasks_generated':
        step3State.progress = 50;
        step3State.progressMessage = `Generated ${data.count} tasks`;
        // Add tasks with "generating" status (they'll show shimmer)
        step3State.tasks = data.tasks.map(t => ({
          ...t,
          status: 'generating'
        }));
        break;
        
      case 'decomposing':
        step3State.progress = 50 + (data.progress / data.total) * 30;
        step3State.progressMessage = `Analyzing "${data.task_name}" (${data.progress}/${data.total})`;
        break;
        
      case 'task_detailed':
        // Update specific task
        const taskIndex = step3State.tasks.findIndex(t => t.id === data.task_id);
        if (taskIndex !== -1) {
          step3State.tasks[taskIndex] = {
            ...data.task,
            status: 'complete'
          };
        }
        break;
        
      case 'complete':
        step3State.progress = 100;
        step3State.progressMessage = 'Complete!';
        step3State.isGenerating = false;
        step3State.tasks = data.tasks;
        step3State.dependencies = data.dependencies;
        eventSource.close();
        
        // Auto-layout flowchart
        setTimeout(() => autoLayout(), 500);
        break;
    }
  };
  
  eventSource.onerror = (error) => {
    console.error('Streaming error:', error);
    step3State.isGenerating = false;
    showError('Failed to generate tasks. Please try again.');
    eventSource.close();
  };
};
```

**Task Edit Modal:**

```html
<!-- Task Edit Modal -->
<div class="modal" v-if="editingTask">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Edit Task</h3>
      <button class="btn-close" @click="closeEditModal">✕</button>
    </div>

    <div class="modal-body">
      <div class="form-group">
        <label>Task Name</label>
        <input 
          v-model="editingTask.name"
          placeholder="e.g., Design database schema"
          maxlength="100"
        />
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea
          v-model="editingTask.description"
          rows="4"
          placeholder="What needs to be done?"
        ></textarea>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label>Estimated Hours</label>
          <input 
            type="number"
            v-model="editingTask.estimated_hours"
            min="1"
            step="0.5"
          />
        </div>

        <div class="form-group col-6">
          <label>Assignment</label>
          <select v-model="editingTask.assigned_to">
            <option value="AI">AI</option>
            <option value="Human">Human</option>
            <option value="Hybrid">Hybrid</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Complexity</label>
        <select v-model="editingTask.complexity">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <!-- Validation preview -->
      <div class="validation-preview" v-if="editValidation">
        <h4>Validation</h4>
        <div class="score-display">
          <span class="score-value">{{ editValidation.score }}/100</span>
          <span 
            class="score-label"
            :class="{
              'good': editValidation.score >= 80,
              'warning': editValidation.score >= 60 && editValidation.score < 80,
              'danger': editValidation.score < 60
            }"
          >
            {{ editValidation.score >= 80 ? 'Good' : editValidation.score >= 60 ? 'Fair' : 'Needs Work' }}
          </span>
        </div>
        <ul class="validation-issues" v-if="editValidation.issues.length">
          <li v-for="issue in editValidation.issues" :key="issue">
            ⚠️ {{ issue }}
          </li>
        </ul>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" @click="closeEditModal">
        Cancel
      </button>
      <button 
        class="btn btn-primary"
        @click="saveTask"
        :disabled="editValidation?.score < 50"
      >
        Save Changes
      </button>
    </div>
  </div>
</div>
```

---

### Step 4: Final Review & Create

**Purpose:** Show summary, allow final confirmation

**UI Layout:**

```html
<!-- Step 4: Final Review -->
<div class="step-container step-4">
  <div class="step-header">
    <h2>Step 4: Review & Create</h2>
    <p class="subtitle">Everything looks good?</p>
  </div>

  <!-- Project Summary -->
  <div class="summary-section">
    <h3>Project Summary</h3>
    <div class="summary-grid">
      <div class="summary-item">
        <label>Name</label>
        <p>{{ projectData.name }}</p>
      </div>
      <div class="summary-item">
        <label>Domain</label>
        <p>{{ projectData.domain }}</p>
      </div>
      <div class="summary-item">
        <label>Deadline</label>
        <p>{{ projectData.deadline || 'Not set' }}</p>
      </div>
      <div class="summary-item">
        <label>Goals</label>
        <p>{{ projectData.goals.length }} defined</p>
      </div>
    </div>
  </div>

  <!-- Workflow Summary -->
  <div class="summary-section">
    <h3>Workflow Summary</h3>
    <div class="workflow-stats">
      <div class="stat-card">
        <div class="stat-value">{{ tasks.length }}</div>
        <div class="stat-label">Total Tasks</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ totalHours }}h</div>
        <div class="stat-label">Estimated Duration</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ aiTaskCount }}</div>
        <div class="stat-label">AI Tasks</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ humanTaskCount }}</div>
        <div class="stat-label">Human Tasks</div>
      </div>
    </div>
  </div>

  <!-- Mini Flowchart Preview -->
  <div class="summary-section">
    <h3>Workflow Preview</h3>
    <div class="mini-flowchart">
      <FlowchartRenderer
        :tasks="tasks"
        :dependencies="dependencies"
        :read-only="true"
        :scale="0.5"
      />
    </div>
  </div>

  <!-- Actions -->
  <div class="step-actions">
    <button class="btn btn-secondary" @click="prevStep">
      ← Back to Edit
    </button>
    <button 
      class="btn btn-primary btn-lg"
      @click="createProject"
      :disabled="isCreating"
    >
      {{ isCreating ? 'Creating...' : 'Create Project' }}
    </button>
  </div>
</div>
```

---

## 4. Component Architecture

### Component Hierarchy

```
ProjectCreationWizard
├── StepIndicator
├── Step1ProjectDetails
│   ├── FormInput
│   ├── FormTextarea
│   └── FormSelect
├── Step2GoalsContext
│   ├── GoalsList
│   │   └── GoalItem
│   ├── FileUploader
│   └── FormTextarea
├── Step3WorkflowBuilder (MAIN COMPONENT)
│   ├── StreamingProgress
│   ├── TaskListPanel
│   │   ├── TaskItem
│   │   │   ├── ValidationIndicator
│   │   │   ├── SubtasksList
│   │   │   └── TaskActions
│   │   └── AddTaskButton
│   ├── FlowchartPanel
│   │   ├── FlowchartRenderer
│   │   │   ├── TaskNode
│   │   │   ├── DependencyEdge
│   │   │   └── Controls
│   │   └── FlowchartControls
│   └── TaskEditModal
└── Step4FinalReview
    ├── SummarySection
    ├── WorkflowStats
    └── MiniFlowchart
```

### Key Components

#### 1. StreamingProgress.vue

```vue
<template>
  <div class="streaming-progress" v-if="isActive">
    <div class="progress-bar-container">
      <div class="progress-bar" :style="{ width: progress + '%' }"></div>
    </div>
    <p class="progress-message">{{ message }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  isActive: Boolean,
  progress: Number,
  message: String
});
</script>
```

#### 2. TaskItem.vue

```vue
<template>
  <div 
    class="task-item"
    :class="{
      'selected': isSelected,
      'warning': task.validation.score < 70,
      'streaming': task.status === 'generating'
    }"
    @click="$emit('select', task.id)"
  >
    <!-- Streaming shimmer -->
    <div v-if="task.status === 'generating'" class="shimmer">
      <div class="shimmer-line"></div>
      <div class="shimmer-line short"></div>
    </div>

    <!-- Task content -->
    <div v-else class="task-content">
      <div class="task-header">
        <h4>{{ task.name }}</h4>
        <span class="badge" :class="task.assigned_to.toLowerCase()">
          {{ task.assigned_to }}
        </span>
      </div>

      <p class="description">{{ task.description }}</p>

      <div class="meta">
        <span>⏱️ {{ task.estimated_hours }}h</span>
        <span>📊 {{ task.complexity }}</span>
      </div>

      <ValidationIndicator 
        v-if="task.validation"
        :validation="task.validation"
      />

      <div v-if="isSelected" class="actions">
        <button @click.stop="$emit('edit', task)">✏️ Edit</button>
        <button @click.stop="$emit('regenerate', task.id)">🔄</button>
        <button @click.stop="$emit('delete', task.id)">🗑️</button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  task: Object,
  isSelected: Boolean
});

const emit = defineEmits(['select', 'edit', 'regenerate', 'delete']);
</script>
```

#### 3. FlowchartRenderer.vue

```vue
<template>
  <div class="flowchart-renderer">
    <VueFlow
      :nodes="nodes"
      :edges="edges"
      :default-viewport="viewport"
      @node-click="onNodeClick"
      @nodes-change="onNodesChange"
      @edges-change="onEdgesChange"
      @connect="onConnect"
    >
      <Background />
      <Controls />
      <MiniMap />

      <template #node-custom="{ data }">
        <TaskNode :task="data" />
      </template>
    </VueFlow>
  </div>
</template>

<script setup>
import { VueFlow, Background, Controls, MiniMap } from '@vue-flow/core';

const props = defineProps({
  tasks: Array,
  dependencies: Array,
  manualMode: Boolean,
  readOnly: Boolean
});

// Transform tasks to nodes
const nodes = computed(() => {
  return props.tasks.map(task => ({
    id: task.id,
    type: 'custom',
    position: task.position || { x: 0, y: 0 },
    data: task
  }));
});

// Transform dependencies to edges
const edges = computed(() => {
  return props.dependencies.map(dep => ({
    id: `${dep.from}-${dep.to}`,
    source: dep.from,
    target: dep.to,
    type: 'smoothstep',
    animated: false
  }));
});
</script>
```

---

## 5. State Management

### Vuex Store Structure

```typescript
// store/modules/projectCreation.js

const state = {
  // Wizard state
  currentStep: 1,
  totalSteps: 4,
  
  // Step 1 data
  projectDetails: {
    name: '',
    description: '',
    deadline: null,
    domain: '',
    teamSize: null
  },
  
  // Step 2 data
  goalsContext: {
    goals: [],
    successMetrics: '',
    referenceDocuments: [],
    constraints: ''
  },
  
  // Step 3 data
  workflow: {
    tasks: [],
    dependencies: [],
    isGenerating: false,
    progress: 0,
    progressMessage: '',
    selectedTaskId: null,
    manualMode: false
  },
  
  // Creation state
  isCreating: false,
  creationError: null
};

const mutations = {
  SET_STEP(state, step) {
    state.currentStep = step;
  },
  
  UPDATE_PROJECT_DETAILS(state, details) {
    state.projectDetails = { ...state.projectDetails, ...details };
  },
  
  ADD_GOAL(state, goal) {
    state.goalsContext.goals.push(goal);
  },
  
  REMOVE_GOAL(state, goalId) {
    state.goalsContext.goals = state.goalsContext.goals.filter(
      g => g.id !== goalId
    );
  },
  
  SET_GENERATING(state, isGenerating) {
    state.workflow.isGenerating = isGenerating;
  },
  
  UPDATE_GENERATION_PROGRESS(state, { progress, message }) {
    state.workflow.progress = progress;
    state.workflow.progressMessage = message;
  },
  
  SET_TASKS(state, tasks) {
    state.workflow.tasks = tasks;
  },
  
  UPDATE_TASK(state, { taskId, updates }) {
    const taskIndex = state.workflow.tasks.findIndex(t => t.id === taskId);
    if (taskIndex !== -1) {
      state.workflow.tasks[taskIndex] = {
        ...state.workflow.tasks[taskIndex],
        ...updates
      };
    }
  },
  
  SET_DEPENDENCIES(state, dependencies) {
    state.workflow.dependencies = dependencies;
  },
  
  SELECT_TASK(state, taskId) {
    state.workflow.selectedTaskId = taskId;
  }
};

const actions = {
  async startTaskGeneration({ commit, state }) {
    commit('SET_GENERATING', true);
    
    const eventSource = new EventSource('/api/tasks/generate/stream', {
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        project_details: state.projectDetails,
        goals_context: state.goalsContext
      })
    });
    
    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);
      
      switch (data.status) {
        case 'retrieving_context':
          commit('UPDATE_GENERATION_PROGRESS', {
            progress: 10,
            message: 'Loading relevant knowledge...'
          });
          break;
          
        case 'generating':
          commit('UPDATE_GENERATION_PROGRESS', {
            progress: 30,
            message: 'AI is generating tasks...'
          });
          break;
          
        case 'tasks_generated':
          commit('UPDATE_GENERATION_PROGRESS', {
            progress: 50,
            message: `Generated ${data.count} tasks`
          });
          commit('SET_TASKS', data.tasks);
          break;
          
        case 'complete':
          commit('UPDATE_GENERATION_PROGRESS', {
            progress: 100,
            message: 'Complete!'
          });
          commit('SET_TASKS', data.tasks);
          commit('SET_DEPENDENCIES', data.dependencies);
          commit('SET_GENERATING', false);
          eventSource.close();
          break;
      }
    };
    
    eventSource.onerror = () => {
      commit('SET_GENERATING', false);
      eventSource.close();
    };
  },
  
  async validateTask({ commit }, task) {
    const response = await fetch('/api/tasks/validate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(task)
    });
    
    const validation = await response.json();
    
    commit('UPDATE_TASK', {
      taskId: task.id,
      updates: { validation }
    });
    
    return validation;
  },
  
  async regenerateTask({ commit, state }, taskId) {
    const response = await fetch('/api/tasks/regenerate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        task_ids: [taskId],
        context: {
          project_details: state.projectDetails,
          goals_context: state.goalsContext
        }
      })
    });
    
    const { regenerated_tasks } = await response.json();
    
    commit('UPDATE_TASK', {
      taskId,
      updates: regenerated_tasks[0]
    });
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};
```

---

## 6. API Integration Points

### API Endpoints Needed

```typescript
// All API calls to Laravel (which proxies to Python)

interface APIEndpoints {
  // Context analysis (Step 2 → Step 3)
  analyzeContext: {
    method: 'POST',
    url: '/api/context/analyze',
    body: {
      project_details: ProjectDetails,
      goals_context: GoalsContext
    },
    response: {
      domain: string,
      complexity: string,
      estimated_task_count: number
    }
  },
  
  // Task generation (Step 3 - streaming)
  generateTasks: {
    method: 'POST',
    url: '/api/tasks/generate/stream',
    body: {
      project_id: string,
      user_id: string,
      context: {
        project_details: ProjectDetails,
        goals_context: GoalsContext,
        context_analysis: ContextAnalysis
      }
    },
    response: 'Server-Sent Events (SSE)'
  },
  
  // Task validation
  validateTask: {
    method: 'POST',
    url: '/api/tasks/validate',
    body: Task,
    response: {
      score: number,
      issues: string[],
      passed: boolean
    }
  },
  
  // Task regeneration
  regenerateTasks: {
    method: 'POST',
    url: '/api/tasks/regenerate',
    body: {
      task_ids: string[],
      context: ProjectContext
    },
    response: {
      regenerated_tasks: Task[]
    }
  },
  
  // Project creation (Step 4)
  createProject: {
    method: 'POST',
    url: '/api/projects/create',
    body: {
      project_details: ProjectDetails,
      goals_context: GoalsContext,
      tasks: Task[],
      dependencies: Dependency[]
    },
    response: {
      project_id: string,
      redirect_url: string
    }
  }
}
```

### API Service Class

```typescript
// services/api.service.ts

class APIService {
  private baseURL = '/api';
  
  async analyzeContext(data: any) {
    return this.post('/context/analyze', data);
  }
  
  generateTasksStream(data: any, onMessage: Function) {
    const eventSource = new EventSource(
      `${this.baseURL}/tasks/generate/stream`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      }
    );
    
    eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);
      onMessage(data);
    };
    
    return eventSource;
  }
  
  async validateTask(task: Task) {
    return this.post('/tasks/validate', task);
  }
  
  async regenerateTasks(taskIds: string[], context: any) {
    return this.post('/tasks/regenerate', { task_ids: taskIds, context });
  }
  
  async createProject(projectData: any) {
    return this.post('/projects/create', projectData);
  }
  
  private async post(endpoint: string, data: any) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      body: JSON.stringify(data)
    });
    
    if (!response.ok) {
      throw new Error(`API error: ${response.statusText}`);
    }
    
    return response.json();
  }
}

export default new APIService();
```

---

## 7. Validation & Error Handling

### Client-Side Validation

```typescript
// validators/task.validator.ts

export const validateTask = (task: Task): ValidationResult => {
  const issues: string[] = [];
  let score = 100;
  
  // Name validation
  if (!task.name || task.name.trim().length < 3) {
    issues.push('Task name must be at least 3 characters');
    score -= 30;
  }
  
  // Description validation
  if (!task.description || task.description.trim().length < 10) {
    issues.push('Task description must be at least 10 characters');
    score -= 25;
  }
  
  // Action verb check
  const actionVerbs = ['create', 'design', 'implement', 'build', 'test', 'deploy', 'analyze', 'write'];
  const hasActionVerb = actionVerbs.some(verb => 
    task.name.toLowerCase().includes(verb)
  );
  
  if (!hasActionVerb) {
    issues.push('Task should start with an action verb');
    score -= 15;
  }
  
  // Vague terms check
  const vagueTerms = ['setup', 'handle', 'manage', 'deal with', 'work on'];
  const hasVagueTerm = vagueTerms.some(term => 
    task.name.toLowerCase().includes(term)
  );
  
  if (hasVagueTerm) {
    issues.push('Task description is too vague');
    score -= 20;
  }
  
  // Time estimate
  if (!task.estimated_hours || task.estimated_hours <= 0) {
    issues.push('Must provide time estimate');
    score -= 10;
  }
  
  return {
    score: Math.max(0, score),
    issues,
    passed: score >= 70
  };
};
```

### Error Handling

```typescript
// composables/useErrorHandler.ts

export const useErrorHandler = () => {
  const showError = (message: string, error?: Error) => {
    console.error(message, error);
    
    // Show toast notification
    toast.error(message, {
      duration: 5000,
      position: 'top-right'
    });
  };
  
  const handleAPIError = (error: any) => {
    if (error.response) {
      // Server error
      switch (error.response.status) {
        case 400:
          showError('Invalid request. Please check your input.');
          break;
        case 401:
          showError('Unauthorized. Please log in again.');
          window.location.href = '/login';
          break;
        case 429:
          showError('Too many requests. Please try again later.');
          break;
        case 500:
          showError('Server error. Our team has been notified.');
          break;
        default:
          showError('Something went wrong. Please try again.');
      }
    } else if (error.request) {
      // Network error
      showError('Network error. Please check your connection.');
    } else {
      // Client error
      showError(error.message || 'An unexpected error occurred.');
    }
  };
  
  return { showError, handleAPIError };
};
```

---

## 8. Responsive Design

### Breakpoints

```scss
// styles/breakpoints.scss

$breakpoints: (
  'mobile': 576px,
  'tablet': 768px,
  'desktop': 1024px,
  'wide': 1440px
);

// Mobile-first approach
@mixin mobile {
  @media (max-width: map-get($breakpoints, 'mobile')) {
    @content;
  }
}

@mixin tablet {
  @media (min-width: map-get($breakpoints, 'tablet')) {
    @content;
  }
}

@mixin desktop {
  @media (min-width: map-get($breakpoints, 'desktop')) {
    @content;
  }
}
```

### Mobile Layout for Step 3

```scss
// Step 3 responsive layout
.step-3.merged-view {
  .split-view {
    display: flex;
    gap: 1rem;
    
    @include mobile {
      flex-direction: column;
      
      .task-list-panel {
        width: 100%;
        order: 1;
      }
      
      .flowchart-panel {
        width: 100%;
        order: 2;
        min-height: 400px;
      }
    }
    
    @include tablet {
      flex-direction: row;
      
      .task-list-panel {
        width: 100%;
      }
      
      .flowchart-panel {
        width: 100%;
      }
    }
    
    @include desktop {
      .task-list-panel {
        width: 40%;
      }
      
      .flowchart-panel {
        width: 60%;
      }
    }
  }
}
```

---

## 9. Implementation Checklist

### Phase 1: Setup (Day 1-2)

- [ ] Create new Vue/React components
- [ ] Set up Vuex/Redux store modules
- [ ] Install dependencies (VueFlow/ReactFlow)
- [ ] Configure API service
- [ ] Set up TypeScript interfaces

### Phase 2: Step 1 & 2 (Day 3-4)

- [ ] Build Step 1 form
- [ ] Build Step 2 goals interface
- [ ] Add file upload component
- [ ] Implement client-side validation
- [ ] Test data flow

### Phase 3: Step 3 - Core (Day 5-10)

- [ ] Build split-view layout
- [ ] Implement task list panel
- [ ] Build streaming progress component
- [ ] Integrate VueFlow/ReactFlow
- [ ] Build task node component
- [ ] Implement dependency rendering
- [ ] Add drag-and-drop
- [ ] Build task edit modal

### Phase 4: Step 3 - Advanced (Day 11-14)

- [ ] Implement streaming SSE handler
- [ ] Add real-time validation
- [ ] Build regeneration UI
- [ ] Add manual/auto mode toggle
- [ ] Implement auto-layout algorithm
- [ ] Add zoom controls
- [ ] Test with mock data

### Phase 5: Step 4 & Integration (Day 15-17)

- [ ] Build final review screen
- [ ] Add summary sections
- [ ] Implement project creation
- [ ] Connect all API endpoints
- [ ] Add error handling
- [ ] Test complete flow

### Phase 6: Polish (Day 18-20)

- [ ] Mobile responsive design
- [ ] Loading states
- [ ] Empty states
- [ ] Animation & transitions
- [ ] Accessibility (ARIA labels)
- [ ] Cross-browser testing

### Phase 7: Documentation (Day 21)

- [ ] Component documentation
- [ ] API documentation
- [ ] User flow diagrams
- [ ] Testing guide

---

## Appendix A: Design Tokens

```scss
// Design System Tokens

// Colors
$primary: #6366f1;
$secondary: #8b5cf6;
$success: #10b981;
$warning: #f59e0b;
$danger: #ef4444;

$gray-50: #f9fafb;
$gray-100: #f3f4f6;
$gray-200: #e5e7eb;
$gray-300: #d1d5db;
$gray-500: #6b7280;
$gray-700: #374151;
$gray-900: #111827;

// Task type colors
$ai-color: #3b82f6;
$human-color: #10b981;
$hybrid-color: #f59e0b;

// Spacing
$spacing-xs: 0.25rem;
$spacing-sm: 0.5rem;
$spacing-md: 1rem;
$spacing-lg: 1.5rem;
$spacing-xl: 2rem;

// Typography
$font-sans: 'Inter', system-ui, -apple-system, sans-serif;
$font-mono: 'Fira Code', 'Courier New', monospace;

// Shadows
$shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
$shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
$shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);

// Borders
$border-radius-sm: 0.25rem;
$border-radius-md: 0.5rem;
$border-radius-lg: 0.75rem;
```

---

## Appendix B: Sample Data Structures

```typescript
// Sample project data for testing

const sampleProject = {
  projectDetails: {
    name: "Mobile App Launch",
    description: "Build and launch a mobile app for iOS and Android",
    deadline: "2025-12-31",
    domain: "software_development",
    teamSize: 3
  },
  
  goalsContext: {
    goals: [
      {
        id: "goal-1",
        text: "Launch beta version by Q2 2025",
        priority: "high"
      },
      {
        id: "goal-2",
        text: "Acquire 10,000 users in first 3 months",
        priority: "high"
      },
      {
        id: "goal-3",
        text: "Maintain 4.5+ star rating on app stores",
        priority: "medium"
      }
    ],
    successMetrics: "MAU > 10k, Retention > 40%, Rating > 4.5",
    referenceDocuments: [],
    constraints: "Budget: $50k, Timeline: 6 months"
  },
  
  workflow: {
    tasks: [
      {
        id: "task-1",
        name: "Design database schema",
        description: "Create normalized database schema for user data, content, and analytics",
        estimated_hours: 8,
        complexity: "medium",
        assigned_to: "Human",
        validation: {
          score: 85,
          issues: [],
          passed: true
        },
        position: { x: 100, y: 100 }
      },
      {
        id: "task-2",
        name: "Implement authentication system",
        description: "Build JWT-based auth with social login (Google, Apple)",
        estimated_hours: 16,
        complexity: "high",
        assigned_to: "AI",
        validation: {
          score: 90,
          issues: [],
          passed: true
        },
        subtasks: [
          {
            id: "subtask-2-1",
            name: "Set up JWT token generation",
            estimated_hours: 4
          },
          {
            id: "subtask-2-2",
            name: "Implement social OAuth flows",
            estimated_hours: 8
          }
        ],
        position: { x: 100, y: 250 }
      }
    ],
    
    dependencies: [
      {
        id: "dep-1",
        from: "task-1",
        to: "task-2",
        type: "blocks"
      }
    ]
  }
};
```

---

**END OF DOCUMENT**
