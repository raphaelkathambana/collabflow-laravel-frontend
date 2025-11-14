# CollabFlow: Fix React Error #185 - Agent Instructions
## Implementing Hybrid React Externalization

**Priority**: CRITICAL  
**Issue**: React Error #185 - Multiple instances detected  
**Solution**: Hybrid externalization pattern  
**Estimated Time**: 2-3 hours

---

## 🎯 What You're Fixing

**Current Problem**:
```
Error: Minified React error #185
Cause: Multiple React instances loaded on the page
Impact: Flowchart completely broken, cannot proceed
```

**Solution Strategy**:
Load React ONCE as a shared global resource, configure Vite to not bundle React into flowchart code.

---

## 📋 Pre-Flight Checklist

Before starting, confirm:
- [ ] You have access to the Laravel project root
- [ ] Node.js and npm are installed (`node --version`)
- [ ] You can run `npm install` and `npm run dev`
- [ ] You understand the project is Laravel 12 + Vite + React Flow
- [ ] Current error happens when loading flowchart page

---

## 🔧 Implementation Steps

### Step 1: Create React Vendor Bundle (5 minutes)

**Create new file**: `resources/js/vendor-react.js`

```javascript
/**
 * React Vendor Bundle
 * Loads React ONCE and makes it available globally
 */

import React from 'react';
import ReactDOM from 'react-dom/client';

// Expose to window for shared access
window.React = React;
window.ReactDOM = ReactDOM;

// Log confirmation
console.log('✅ React vendor bundle loaded');
console.log('   Version:', React.version);
console.log('   Available as: window.React, window.ReactDOM');
```

**Why**: This creates a dedicated bundle that only contains React and exposes it globally.

---

### Step 2: Configure Vite for Externalization (15 minutes)

**Edit**: `vite.config.js`

**Replace the entire file with**:

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
                'resources/js/vendor-react.js',      // NEW: React vendor
                'resources/js/flowchart/index.jsx',  // Existing flowchart
            ],
            refresh: true,
        }),
        react(),
    ],
    
    build: {
        rollupOptions: {
            // Tell Vite: Don't bundle React into flowchart code
            external: (id) => {
                // Externalize React for all bundles EXCEPT vendor-react
                if (id === 'react' || id === 'react-dom' || id === 'react-dom/client') {
                    // Only bundle React in vendor-react.js, external everywhere else
                    return true; // Mark as external (don't bundle)
                }
                return false;
            },
            
            output: {
                // Tell bundled code how to find external React
                globals: {
                    'react': 'React',              // window.React
                    'react-dom': 'ReactDOM',        // window.ReactDOM
                    'react-dom/client': 'ReactDOM', // window.ReactDOM
                },
            },
        },
    },
    
    resolve: {
        // Safety net: If React imported multiple times, dedupe it
        dedupe: ['react', 'react-dom'],
        
        alias: {
            '@': '/resources/js',
        },
    },
    
    optimizeDeps: {
        // Optimize React Flow for dev server
        include: ['@xyflow/react', 'dagre'],
    },
});
```

**Key Changes**:
- Added `vendor-react.js` to input array
- Added `external` function to exclude React from bundles
- Added `dedupe` to prevent accidental duplication
- Added `globals` to map React to window properties

---

### Step 3: Update FlowchartContainer Component (10 minutes)

**Edit**: `resources/js/flowchart/FlowchartContainer.jsx`

**Change the import section from**:
```javascript
import React from 'react';
import { createRoot } from 'react-dom/client';
```

**To**:
```javascript
// Use global React instance (loaded by vendor-react.js)
const React = window.React;
const { useState, useCallback, useEffect, useMemo } = React;

// Verify React is available
if (!React) {
    console.error('❌ React not found on window.');
    console.error('   Make sure vendor-react.js loads before flowchart code.');
    throw new Error('React not available');
}
```

**Then update the rest of the file**:

```javascript
// React Flow can be imported normally
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

// Import your custom components (no changes needed to these imports)
import { TaskNode } from './nodes/TaskNode';
import { StartNode } from './nodes/StartNode';
import { EndNode } from './nodes/EndNode';
import { tasksToNodes, tasksToEdges } from './utils/dataTransformers';

/**
 * Main flowchart container component
 */
export function FlowchartContainer({
    tasks,
    workflowState,
    projectId,
    onNodePositionChange,
    onWorkflowUpdate,
    onTaskClick
}) {
    console.log('⚛️ FlowchartContainer using React', React.version);
    
    // Define custom node types
    const nodeTypes = useMemo(() => ({
        taskNode: TaskNode,
        startNode: StartNode,
        endNode: EndNode
    }), []);
    
    // Convert Laravel data to React Flow format
    const initialNodes = useMemo(() => tasksToNodes(tasks), [tasks]);
    const initialEdges = useMemo(() => tasksToEdges(tasks), [tasks]);
    
    // React Flow state management
    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);
    
    // ... rest of your component implementation (unchanged)
    // Keep all your existing handlers and logic
    
    return (
        <div className="w-full h-full">
            <ReactFlow
                nodes={nodes}
                edges={edges}
                nodeTypes={nodeTypes}
                onNodesChange={onNodesChange}
                onEdgesChange={onEdgesChange}
                fitView
            >
                <Background />
                <Controls />
                <MiniMap />
            </ReactFlow>
        </div>
    );
}
```

**What Changed**:
- Import React from `window.React` instead of `import React`
- Added verification check for React availability
- Rest of component logic stays exactly the same

---

### Step 4: Update Alpine Bridge (10 minutes)

**Edit**: `resources/js/flowchart/alpine-bridge.js`

**Change the import section from**:
```javascript
import { createRoot } from 'react-dom/client';
import React from 'react';
import { FlowchartContainer } from './FlowchartContainer';
```

**To**:
```javascript
// Use global React instances
const React = window.React;
const ReactDOM = window.ReactDOM;

// Verify availability
if (!React || !ReactDOM) {
    console.error('❌ React not available on window');
    throw new Error('React must be loaded before Alpine bridge');
}

// Import flowchart component (it also uses window.React)
import { FlowchartContainer } from './FlowchartContainer';
```

**Update the mountReact method**:

```javascript
export function createFlowchartBridge() {
    return {
        // ... existing properties ...
        
        init() {
            // Check React is available
            if (!React || !ReactDOM) {
                console.error('❌ React/ReactDOM not found on window');
                console.error('   Ensure vendor-react.js loads before this script');
                return;
            }
            
            console.log('🌉 Alpine bridge initialized with React', React.version);
            
            this.$nextTick(() => {
                this.mountReact();
            });
            
            this.watchLivewireUpdates();
        },
        
        mountReact() {
            const container = this.$refs.reactContainer;
            
            if (!container) {
                console.error('❌ React container element not found');
                return;
            }
            
            console.log('⚛️ Mounting React Flow island...');
            
            // Use global ReactDOM.createRoot
            this.reactRoot = ReactDOM.createRoot(container);
            this.renderReact();
            this.isReactMounted = true;
        },
        
        renderReact() {
            if (!this.reactRoot) return;
            
            console.log('🔄 Rendering React with', this.tasks.length, 'tasks');
            
            // Use global React.createElement
            this.reactRoot.render(
                React.createElement(FlowchartContainer, {
                    tasks: this.tasks,
                    workflowState: this.workflowState,
                    projectId: this.projectId,
                    onNodePositionChange: (nodeId, position) => {
                        this.handleNodeMove(nodeId, position);
                    },
                    onWorkflowUpdate: (workflowData) => {
                        this.handleWorkflowUpdate(workflowData);
                    },
                    onTaskClick: (taskId) => {
                        this.handleTaskClick(taskId);
                    }
                })
            );
        },
        
        // ... rest of your methods (unchanged) ...
    };
}

window.createFlowchartBridge = createFlowchartBridge;
```

**What Changed**:
- Use `window.React` and `window.ReactDOM` instead of imports
- Added error checking for React availability
- Rest of bridge logic stays the same

---

### Step 5: Update Node Components (5 minutes each)

You need to update ALL your custom node components.

**For EACH file** in `resources/js/flowchart/nodes/`:
- `TaskNode.jsx`
- `StartNode.jsx`
- `EndNode.jsx`

**Change the import section from**:
```javascript
import React from 'react';
import { Handle, Position } from '@xyflow/react';
```

**To**:
```javascript
const React = window.React;
import { Handle, Position } from '@xyflow/react';
```

**Example for TaskNode.jsx**:

```javascript
const React = window.React;
import { Handle, Position } from '@xyflow/react';

export function TaskNode({ data, selected }) {
    const { task, hasIssues } = data;
    
    // ... rest of your component (unchanged)
    
    return (
        <div className="...">
            <Handle type="target" position={Position.Top} />
            {/* Your existing node content */}
            <Handle type="source" position={Position.Bottom} />
        </div>
    );
}
```

**Repeat this pattern for StartNode.jsx and EndNode.jsx**.

---

### Step 6: Update Blade Layout (10 minutes)

**Edit**: `resources/views/layouts/app.blade.php` (or your main layout file)

**Find the Vite directives section**, change from:
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

**To**:
```blade
{{-- Load CSS --}}
@vite(['resources/css/app.css'])

{{-- Load React vendor bundle FIRST --}}
@vite(['resources/js/vendor-react.js'])

{{-- Then load main app --}}
@vite(['resources/js/app.js'])
```

**Important**: The order matters! React vendor MUST load before app.js.

**Full layout structure should be**:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CollabFlow') }}</title>
    
    {{-- Styles --}}
    @vite(['resources/css/app.css'])
    
    {{-- React vendor (loads once, cached) --}}
    @vite(['resources/js/vendor-react.js'])
    
    {{-- Main app --}}
    @vite(['resources/js/app.js'])
    
    @livewireStyles
</head>
<body>
    {{ $slot }}
    
    @livewireScripts
    
    {{-- Page-specific scripts load here --}}
    @stack('scripts')
</body>
</html>
```

---

### Step 7: Update Project Page (5 minutes)

**Edit**: `resources/views/projects/show.blade.php` (or wherever flowchart appears)

**Add conditional flowchart loading**:

```blade
<x-app-layout>
    {{-- Load flowchart bundle for this page only --}}
    @push('scripts')
        @vite(['resources/js/flowchart/index.jsx'])
    @endpush
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">{{ $project->name }}</h1>
        
        {{-- Workflow/Flowchart Section --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4">Workflow</h2>
            
            <div class="h-[600px] border rounded-lg">
                @livewire('project-workflow-flowchart', ['projectId' => $project->id])
            </div>
        </div>
    </div>
</x-app-layout>
```

**Key point**: `@push('scripts')` ensures flowchart.js loads AFTER vendor-react.js.

---

## ✅ Testing & Verification

### Test 1: Build Assets

```bash
# Clear any cached builds
rm -rf public/build

# Run production build
npm run build
```

**Expected output**:
```
✓ built in XXXms
✓ public/build/manifest.json
✓ public/build/assets/app-[hash].js
✓ public/build/assets/vendor-react-[hash].js
✓ public/build/assets/flowchart-[hash].js
```

**Check**: Should see THREE separate JS files.

### Test 2: Check File Sizes

```bash
ls -lh public/build/assets/*.js
```

**Expected**:
- `app-*.js` → ~120KB
- `vendor-react-*.js` → ~160KB (contains React)
- `flowchart-*.js` → ~120KB (does NOT contain React)

**If flowchart.js is still 280KB**: React is still being bundled. Go back to Step 2.

### Test 3: Check React in Bundles

```bash
# Search for React code in flowchart bundle
grep -i "createElement" public/build/assets/flowchart-*.js
```

**Expected**: Should find NOTHING or minimal references (external calls only).

**If you find React code**: Externalization didn't work. Check vite.config.js.

### Test 4: Dev Server Test

```bash
npm run dev
```

Then visit your project page in browser.

**Open browser console** and look for:
```
✅ React vendor bundle loaded
   Version: 18.3.1
   Available as: window.React, window.ReactDOM
🌉 Alpine bridge initialized with React 18.3.1
⚛️ FlowchartContainer using React 18.3.1
```

**Should NOT see**:
```
❌ Error: Minified React error #185
❌ React not found on window
❌ Invalid hook call
```

### Test 5: Verify Global React

In browser console, type:

```javascript
window.React
window.ReactDOM
React.version
```

**Expected results**:
- `window.React` → Object {createElement: ƒ, ...}
- `window.ReactDOM` → Object {createRoot: ƒ, ...}
- `React.version` → "18.3.1"

**If undefined**: vendor-react.js didn't load. Check layout blade file.

### Test 6: Functional Test

On the project page with flowchart:

1. ✅ Flowchart should render (no error)
2. ✅ Can see START, END, and task nodes
3. ✅ Can drag nodes
4. ✅ Nodes have correct colors (blue, green, orange)
5. ✅ No console errors

---

## 🐛 Troubleshooting

### Problem: "React is not defined"

**Cause**: vendor-react.js not loaded or loaded in wrong order

**Fix**:
1. Check `resources/views/layouts/app.blade.php`
2. Ensure `@vite(['resources/js/vendor-react.js'])` comes BEFORE `@vite(['resources/js/app.js'])`
3. Clear browser cache (Cmd+Shift+R / Ctrl+F5)

### Problem: Still getting Error #185

**Cause**: React is still being bundled in flowchart.js

**Fix**:
1. Check `vite.config.js` has `external` function
2. Run `npm run build` and check bundle sizes
3. If flowchart.js > 200KB, React is still bundled
4. Delete `node_modules/.vite` and rebuild: `rm -rf node_modules/.vite && npm run dev`

### Problem: "Cannot read property 'createElement' of undefined"

**Cause**: Trying to use React before vendor-react.js loads

**Fix**:
1. Check script load order in layout
2. Ensure flowchart.js loads via `@push('scripts')` (loads AFTER body)
3. vendor-react.js should load in `<head>` before app.js

### Problem: Flowchart doesn't render, just blank

**Cause**: Alpine bridge not initializing React

**Fix**:
1. Check browser console for errors
2. Verify Alpine bridge is registered: `window.createFlowchartBridge`
3. Check Livewire component is rendering: inspect element, should see `<div x-data="flowchartBridge()">`

### Problem: Dev server works, production doesn't

**Cause**: Build configuration different from dev configuration

**Fix**:
1. Run `npm run build` locally
2. Check public/build/assets/ for correct files
3. Test production build locally: `php artisan serve`
4. Check for minification errors in production bundle

---

## 🎯 Success Criteria

You've successfully fixed the issue when:

- [ ] `npm run build` completes without errors
- [ ] vendor-react-[hash].js exists (~160KB)
- [ ] flowchart-[hash].js exists (~120KB, NOT 280KB)
- [ ] Browser console shows "React vendor bundle loaded"
- [ ] Browser console shows "Alpine bridge initialized"
- [ ] Browser console shows "FlowchartContainer using React 18.3.1"
- [ ] Flowchart renders on project page
- [ ] Can drag nodes without errors
- [ ] NO Error #185 in console
- [ ] `window.React` is defined in console

---

## 📊 Before & After Comparison

### Before (Broken)
```
Page loads
├─ app.js (Alpine, Livewire)
└─ flowchart.js (React + React Flow)
    └─ React instance created
        └─ React Flow tries to use hooks
            └─ ❌ ERROR #185: Multiple React instances detected
                └─ Application crashes
```

### After (Working)
```
Page loads
├─ vendor-react.js
│   └─ window.React created (single instance)
├─ app.js (Alpine, Livewire)
│   └─ Uses window.React if needed
└─ flowchart.js (React Flow only, no React bundled)
    └─ Uses window.React (external reference)
        └─ React Flow hooks work correctly
            └─ ✅ Flowchart renders successfully
```

---

## ⏱️ Estimated Time Breakdown

| Step | Time | Critical? |
|------|------|-----------|
| 1. Create vendor-react.js | 5 min | ✅ Yes |
| 2. Configure vite.config.js | 15 min | ✅ Yes |
| 3. Update FlowchartContainer | 10 min | ✅ Yes |
| 4. Update Alpine bridge | 10 min | ✅ Yes |
| 5. Update node components | 15 min | ✅ Yes |
| 6. Update layout blade | 10 min | ✅ Yes |
| 7. Update project page | 5 min | ⚠️ Important |
| Testing & verification | 30 min | ⚠️ Important |
| **Total** | **~2 hours** | |

---

## 🚀 Quick Start Command Sequence

If you want to execute everything in order:

```bash
# 1. Create vendor bundle
cat > resources/js/vendor-react.js << 'EOF'
import React from 'react';
import ReactDOM from 'react-dom/client';
window.React = React;
window.ReactDOM = ReactDOM;
console.log('✅ React vendor bundle loaded');
EOF

# 2. Backup current config
cp vite.config.js vite.config.js.backup

# 3. Update vite config (manual - see Step 2)
# Edit vite.config.js with the new configuration

# 4. Update all JS files to use window.React (manual - see Steps 3-5)
# Edit FlowchartContainer.jsx, alpine-bridge.js, and node files

# 5. Clear caches
rm -rf node_modules/.vite
rm -rf public/build

# 6. Rebuild
npm run build

# 7. Test
npm run dev
```

Then open browser and test the flowchart page.

---

## ✉️ Support

If you encounter issues not covered here:

1. **Check Laravel logs**: `tail -f storage/logs/laravel.log`
2. **Check browser console**: Look for any React-related errors
3. **Check network tab**: Verify vendor-react.js loads (200 status)
4. **Verify bundle contents**: Use browser dev tools → Sources tab
5. **Check file sizes**: Ensure flowchart.js is smaller after changes

---

## 📝 Checklist for Agent

Copy this checklist and mark off as you complete each step:

```
□ Step 1: Created vendor-react.js
□ Step 2: Updated vite.config.js with external config
□ Step 3: Updated FlowchartContainer.jsx to use window.React
□ Step 4: Updated alpine-bridge.js to use window.React
□ Step 5: Updated TaskNode.jsx to use window.React
□ Step 5: Updated StartNode.jsx to use window.React
□ Step 5: Updated EndNode.jsx to use window.React
□ Step 6: Updated layouts/app.blade.php with vendor-react loading
□ Step 7: Updated projects/show.blade.php with @push('scripts')
□ Test 1: npm run build completes successfully
□ Test 2: Bundle sizes are correct
□ Test 3: No React in flowchart bundle
□ Test 4: Dev server shows correct console logs
□ Test 5: window.React is defined
□ Test 6: Flowchart renders and works
□ SUCCESS: Error #185 is gone!
```

---

**Good luck! This fix will make your flowchart work perfectly.** 🚀
