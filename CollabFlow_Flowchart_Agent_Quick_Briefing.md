# CollabFlow Flowchart: Agent Briefing
## Quick Start Instructions for Implementation Agent

**Date**: October 30, 2025  
**Task**: Implement interactive React Flow flowchart in Laravel CollabFlow  
**Architecture**: Islands Architecture (React embedded in Livewire)

---

## 🎯 Your Mission

Implement an interactive workflow flowchart feature in the CollabFlow Laravel application using React Flow as an embedded "island" of interactivity within the Livewire/Blade application.

---

## 📚 Context You Have

1. **CollabFlow Project** - Laravel 12 + Livewire + Flux UI + PostgreSQL
2. **React Demo** - Shows desired flowchart functionality (reference only, not part of main project)
3. **Design Specifications** - Available in project knowledge
4. **Database Schema** - Projects and tasks tables with position/workflow_state columns

---

## 🏗️ Architecture: Islands Pattern

```
Laravel/Livewire (Server) ←→ Alpine.js (Bridge) ←→ React Flow (Client)
      ↕                             ↕                       ↕
   Database                   Communication            Rendering
   Business Logic            Layer                    User Interactions
```

**Key Principle**: React Flow ONLY handles visualization and interaction. Livewire handles ALL data operations.

---

## 📦 Step 1: Install Packages (START HERE)

```bash
# In Laravel project root
npm install react@^18.3.0 react-dom@^18.3.0
npm install @xyflow/react@^12.0.0
npm install dagre@^0.8.5
npm install --save-dev @vitejs/plugin-react
```

---

## ⚙️ Step 2: Configure Vite

Edit `vite.config.js`:

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
                'resources/js/flowchart/index.jsx',  // Add this
            ],
            refresh: true,
        }),
        react(),  // Add this
    ],
});
```

---

## 📁 Step 3: Create File Structure

```bash
mkdir -p resources/js/flowchart/components
mkdir -p resources/js/flowchart/nodes
mkdir -p resources/js/flowchart/utils
```

**Required files to create**:
1. `resources/js/flowchart/index.jsx` - Entry point
2. `resources/js/flowchart/FlowchartContainer.jsx` - Main React component
3. `resources/js/flowchart/alpine-bridge.js` - Bridge layer
4. `resources/js/flowchart/nodes/TaskNode.jsx` - Task node component
5. `resources/js/flowchart/utils/dataTransformers.js` - Data conversion utilities
6. `app/Livewire/ProjectWorkflowFlowchart.php` - Livewire component
7. `resources/views/livewire/project-workflow-flowchart.blade.php` - View

---

## 🌉 Step 4: Build the Bridge (CRITICAL)

The Alpine.js bridge is the connector between Livewire and React.

**Key functions**:
- `init()` - Mount React when component loads
- `mountReact()` - Create React root and render
- `handleNodeMove()` - Pass drag events to Livewire
- `watchLivewireUpdates()` - Re-render React when data changes

**Register in `resources/js/app.js`**:
```javascript
import { createFlowchartBridge } from './flowchart/alpine-bridge';
Alpine.data('flowchartBridge', createFlowchartBridge);
```

---

## ⚛️ Step 5: React Components

### Main Container Pattern

```jsx
export function FlowchartContainer({ 
    tasks,              // From Livewire
    workflowState,      // From Livewire
    onNodePositionChange,  // Callback to Alpine
    onTaskClick         // Callback to Alpine
}) {
    const [nodes, setNodes] = useNodesState(tasksToNodes(tasks));
    const [edges, setEdges] = useEdgesState(tasksToEdges(tasks));
    
    return (
        <ReactFlow
            nodes={nodes}
            edges={edges}
            nodeTypes={{
                taskNode: TaskNode,
                startNode: StartNode,
                endNode: EndNode
            }}
            onNodeDragStop={(event, node) => {
                if (node.type === 'taskNode') {
                    onNodePositionChange(node.id, node.position);
                }
            }}
        >
            <Background />
            <Controls />
            <MiniMap />
        </ReactFlow>
    );
}
```

### Task Node Pattern

```jsx
export function TaskNode({ data }) {
    const colorClass = {
        'ai': 'bg-[#5B8DEE]',
        'human': 'bg-[#C4D6B0]',
        'hitl': 'bg-[#FF9500]'
    }[data.task.type];
    
    return (
        <div className={`${colorClass} rounded-lg p-4 min-w-[250px]`}>
            <Handle type="target" position={Position.Top} />
            <div className="font-semibold text-white">{data.task.name}</div>
            <Handle type="source" position={Position.Bottom} />
        </div>
    );
}
```

---

## 🔌 Step 6: Livewire Component

```php
class ProjectWorkflowFlowchart extends Component
{
    public $projectId;
    public $tasks = [];
    public $workflowState = null;
    
    public function mount($projectId) {
        $this->projectId = $projectId;
        $this->loadTasks();
    }
    
    public function loadTasks() {
        $this->tasks = Task::where('project_id', $this->projectId)
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'name' => $task->name,
                'type' => $task->type,
                'position' => $task->position,
                'dependencies' => $task->dependencies ?? []
            ])
            ->toArray();
    }
    
    public function updateNodePosition($nodeId, $position) {
        $task = Task::findOrFail($nodeId);
        $task->position = $position;
        $task->save();
    }
}
```

---

## 🎨 Step 7: Blade View

```blade
<div 
    x-data="flowchartBridge()"
    x-init="
        tasks = @js($tasks);
        workflowState = @js($workflowState);
        projectId = @js($projectId);
    "
    class="w-full h-[600px]"
>
    <div x-ref="reactContainer" class="w-full h-full"></div>
</div>
```

---

## 🔄 Data Flow Example

**User drags a node:**

1. **React Flow** detects drag → calls `onNodeDragStop`
2. **React** calls callback: `onNodePositionChange(nodeId, position)`
3. **Alpine bridge** receives call: `handleNodeMove(nodeId, position)`
4. **Alpine** calls Livewire: `this.$wire.updateNodePosition(nodeId, position)`
5. **Livewire** saves to database: `$task->position = $position; $task->save();`
6. **Database** stores: `UPDATE tasks SET position = '{"x":400,"y":300}'`

**On page load:**

1. **Livewire** fetches tasks from database
2. **Blade** passes to Alpine: `tasks = @js($tasks)`
3. **Alpine** initializes React: `mountReact()`
4. **React** transforms data: `tasksToNodes(tasks)`
5. **React Flow** renders flowchart

---

## ✅ Testing Checklist

**Phase 1: Installation**
- [ ] `npm install` runs without errors
- [ ] `npm run dev` compiles successfully
- [ ] Browser console shows "React Flow module loaded"

**Phase 2: Rendering**
- [ ] Flowchart displays on project page
- [ ] START and END nodes visible
- [ ] Task nodes show correct colors (blue=AI, green=human, orange=HITL)
- [ ] Edges connect nodes correctly

**Phase 3: Interaction**
- [ ] Can drag task nodes
- [ ] Positions update visually
- [ ] Console shows "Node moved: [id] [position]"

**Phase 4: Persistence**
- [ ] Drag node to new position
- [ ] Refresh page (F5)
- [ ] Node stays in new position
- [ ] Database check: `SELECT position FROM tasks WHERE id = ?`

---

## 🚨 Common Issues & Fixes

**"React is not defined"**
→ Add `import React from 'react'` at top of JSX files

**"Cannot read property 'render' of undefined"**
→ Use `this.$nextTick()` before mounting React in Alpine

**Nodes wrong color**
→ Check CSS variables defined: `--glaucous`, `--tea-green`, `--orange-peel`

**Position not saving**
→ Check: (1) Node type is 'taskNode' not 'start'/'end', (2) Network tab shows POST to Livewire, (3) Laravel logs

**Flowchart doesn't update**
→ Add `useEffect(() => { setNodes(...) }, [tasks])` dependency array

---

## 📖 Reference Documentation

Full detailed guide: `CollabFlow_Flowchart_Implementation_Guide_For_Agent.md`

**Package Docs**:
- React Flow: https://reactflow.dev/
- Livewire: https://livewire.laravel.com/
- Alpine: https://alpinejs.dev/

**Design System**:
- Colors: Glaucous (#5B8DEE), Tea Green (#C4D6B0), Orange Peel (#FF9500)
- Typography: Tahoma (headings), Montserrat (body)

---

## 🎯 Implementation Order

1. ✅ Install packages & configure Vite
2. ✅ Create file structure
3. ✅ Build data transformers (Laravel → React format)
4. ✅ Create custom node components
5. ✅ Build FlowchartContainer
6. ✅ Create Alpine bridge
7. ✅ Build Livewire component
8. ✅ Create Blade view
9. ✅ Test basic rendering
10. ✅ Test drag and drop
11. ✅ Test persistence
12. ✅ Add Flux UI (modals, buttons)

**Estimated time**: 
- Basic flowchart (drag-drop, save): 2-3 days
- Full features (auto-layout, validation, subtasks): 5-7 days

---

## 💡 Key Success Principles

1. **Separation of concerns**: React renders, Livewire manages data
2. **Alpine is just a bridge**: Don't put business logic here
3. **Test each layer**: Bridge → React → Livewire → Database
4. **Console.log everything**: During development, log all data flow
5. **Start simple**: Get basic rendering working before adding features

---

## 🆘 When Stuck

1. Check browser console for JavaScript errors
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Check network tab for Livewire requests
4. Add console.log at each bridge point
5. Verify database schema matches expectations

---

## ✨ You've Got This!

The islands architecture is elegant once you understand it:
- Your Laravel app is the continent (server-rendered)
- React Flow is a small island (client-rendered) 
- Alpine is the bridge connecting them

Follow the steps, test incrementally, and you'll have a beautiful interactive flowchart working in no time.

**Start with Step 1 (Install Packages) and work through methodically.** 

Good luck! 🚀
