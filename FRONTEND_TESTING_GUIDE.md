# CollabFlow Flowchart System - Frontend Testing Guide

## Overview
This guide provides comprehensive testing instructions for the React-based flowchart system integrated into the CollabFlow Laravel application.

**Testing Date:** 2025-11-03
**Implementation Status:** ~75% Complete
**Purpose:** Validate implemented features and identify bugs before implementing remaining features

---

## Pre-Testing Checklist

### Environment Setup
- [ ] Laravel development server running (`php artisan serve`)
- [ ] Vite dev server running (`npm run dev`)
- [ ] Database seeded with test data
- [ ] Browser console open (F12) to monitor logs
- [ ] Test user account created and logged in

### Browser Requirements
Test on at least one modern browser:
- Chrome/Edge (Recommended - React DevTools available)
- Firefox
- Safari (if on macOS)

---

## Test Suite 1: Project Creation Wizard - Step 4 (Workflow Review)

### 1.1 Initial Flowchart Rendering
**Path:** Create New Project → Steps 1-3 → Step 4 (Review Workflow)

**Test Steps:**
1. Navigate to create new project
2. Complete Steps 1 (Basic Info), 2 (Domain & Scope), 3 (Generate Tasks)
3. Click "Next" to Step 4
4. Verify flowchart renders with:
   - Start node (green circle)
   - Task nodes (purple rectangles)
   - End node (red circle)
   - Connecting edges (arrows)

**Expected Results:**
- ✅ Flowchart renders without errors
- ✅ All generated tasks appear as nodes
- ✅ Nodes are positioned automatically (Dagre layout)
- ✅ Edges connect tasks based on dependencies

**Check Console For:**
- ✅ "Flowchart Bridge initialized" message
- ✅ "FlowchartContainer mounted" message
- ❌ No React errors

---

### 1.2 Auto Layout Functionality
**Test Steps:**
1. In Step 4 flowchart, manually drag several nodes to random positions
2. Click "Auto Layout" button in toolbar
3. Observe nodes repositioning

**Expected Results:**
- ✅ Nodes animate smoothly to new positions
- ✅ Layout follows selected direction (horizontal/vertical)
- ✅ Nodes don't overlap
- ✅ Edges maintain proper connections

---

### 1.3 Layout Direction Toggle
**Test Steps:**
1. Note current layout (horizontal/vertical)
2. Click layout direction toggle button
3. Observe layout change
4. Toggle again to original direction

**Expected Results:**
- ✅ Layout switches between horizontal and vertical
- ✅ Button icon changes (left-right arrows ↔ up-down arrows)
- ✅ Nodes reposition appropriately
- ✅ Button label updates ("Horizontal" / "Vertical")

---

### 1.4 Task Node Interaction - Details Panel
**Test Steps:**
1. Click on any task node (purple rectangle)
2. Verify TaskDetailsPanel opens on right side
3. Check displayed information:
   - Task name
   - Description
   - Type (AI/Human/HITL)
   - Estimated hours
   - Status
4. Click "X" button to close panel
5. Click outside panel to close (if backdrop exists)

**Expected Results:**
- ✅ Panel slides in from right
- ✅ Displays correct task information
- ✅ "Edit" and "Delete" buttons visible
- ✅ Panel closes on "X" click
- ✅ Panel closes when clicking another node

---

### 1.5 Task Node Editing
**Test Steps:**
1. Click a task node to open details panel
2. Click "Edit" button
3. Modify:
   - Task name
   - Description
   - Type (change between AI/Human/HITL)
   - Estimated hours
4. Click "Save Changes"
5. Verify node label updates

**Expected Results:**
- ✅ Form pre-fills with current values
- ✅ All fields are editable
- ✅ Changes save successfully
- ✅ Node updates without page reload
- ✅ Console shows: "Task updated: [taskId]"

---

### 1.6 Task Node Deletion
**Test Steps:**
1. Click a task node
2. Click "Delete" button in details panel
3. Confirm deletion (if prompt exists)
4. Verify node removed from flowchart

**Expected Results:**
- ✅ Node disappears from canvas
- ✅ Connected edges removed
- ✅ Flowchart adjusts automatically
- ✅ Console shows: "Task deleted: [taskId]"

**⚠️ Critical Test:**
- Delete a task that has dependencies (other tasks depend on it)
- Verify dependent tasks don't break

---

### 1.7 Context Menu (Right-Click)
**Test Steps:**
1. Right-click on a task node
2. Verify context menu appears with options:
   - Edit
   - Delete
   - View Details
3. Click "Edit" → verify same behavior as details panel edit
4. Right-click on empty canvas
5. Verify no context menu or different menu

**Expected Results:**
- ✅ Context menu appears at cursor position
- ✅ Options are clickable
- ✅ Menu closes after selection
- ✅ Menu closes on outside click

---

### 1.8 Drag & Drop Node Positioning
**Test Steps:**
1. Click and drag a task node to new position
2. Release mouse button
3. Note new position
4. Continue through wizard and save project
5. Return to project detail page
6. Verify node position persisted

**Expected Results:**
- ✅ Node drags smoothly
- ✅ Edges update during drag
- ✅ Position saves after 1-second delay
- ✅ Console shows: "Saving positions to Livewire..."
- ✅ Position persists after save

---

### 1.9 Subtask Badge Interaction
**Test Steps:**
1. Identify task node with "subtasks" badge (should show count like "3 subtasks")
2. Click the subtask badge
3. Verify SubtaskModal opens
4. Check modal displays:
   - Nested flowchart with subtasks
   - Subtask nodes properly connected
   - Toolbar controls (Auto Layout, Layout Toggle)
5. Try interacting with subtask flowchart:
   - Click subtask nodes
   - Drag subtasks
   - Use toolbar controls
6. Close modal via "X" button or backdrop click

**Expected Results:**
- ✅ Modal opens fullscreen or large overlay
- ✅ Subtask flowchart renders correctly
- ✅ Can interact with subtasks (read-only or editable depending on spec)
- ✅ Modal closes cleanly
- ✅ Parent flowchart still intact after closing

**⚠️ Known Limitation:** Subtask position persistence may not save to DB yet

---

### 1.10 Validation Panel
**Test Steps:**
1. Locate ValidationPanel (should be visible on canvas)
2. Check validation metrics displayed:
   - Overall score (0-100)
   - Dependency coverage percentage
   - Circular dependencies detected
   - Orphaned tasks (no dependencies)
3. Deliberately create invalid state:
   - Delete all edges from a task
   - Create circular dependency (if manual edge creation works)
4. Observe validation score decrease

**Expected Results:**
- ✅ Panel displays validation metrics
- ✅ Score updates in real-time
- ✅ Color-coded indicators (green = good, yellow = warning, red = error)
- ✅ Issues list shows specific problems

**⚠️ Note:** Validation badges on nodes may not display yet (partial implementation)

---

### 1.11 Manual Edge Creation
**Test Steps:**
1. Hover over a task node
2. Locate connection handles (small dots on node edges)
3. Click and drag from one node's handle to another node
4. Release to create edge
5. Verify edge appears
6. Save project and check if edge persists

**Expected Results:**
- ✅ Can drag from handle
- ✅ Edge creation visual feedback
- ✅ Edge appears on release
- ⚠️ **KNOWN ISSUE:** May not persist to database (partial implementation)

---

### 1.12 Export Functionality - JSON
**Test Steps:**
1. Click "Export" button in toolbar
2. Select "Export as JSON"
3. Verify file downloads
4. Open JSON file in text editor
5. Verify contents include:
   - `nodes` array with task data
   - `edges` array with connections
   - `layoutDirection`
   - `exportedAt` timestamp

**Expected Results:**
- ✅ JSON file downloads instantly
- ✅ Filename format: `workflow-{timestamp}.json`
- ✅ Valid JSON structure
- ✅ All task and edge data present

---

### 1.13 Export Functionality - PNG
**Test Steps:**
1. Click "Export" button in toolbar
2. Select "Export as PNG"
3. Wait for processing
4. Verify PNG image downloads
5. Open image and verify:
   - All nodes visible
   - Edges visible
   - Labels readable
   - Proper background color

**Expected Results:**
- ✅ PNG downloads after brief processing
- ✅ Filename format: `workflow-{timestamp}.png`
- ✅ Image quality good
- ✅ Entire flowchart captured (not cropped)

**⚠️ Test Edge Cases:**
- Very large flowcharts (10+ nodes)
- After zooming in/out
- After panning canvas

---

### 1.14 Export Functionality - SVG
**Test Steps:**
1. Click "Export" button in toolbar
2. Select "Export as SVG"
3. Verify SVG downloads
4. Open SVG in browser or vector editor
5. Verify scalability (zoom without quality loss)

**Expected Results:**
- ✅ SVG downloads successfully
- ✅ Filename format: `workflow-{timestamp}.svg`
- ✅ Vector graphics scale perfectly
- ✅ Can edit in vector editors (Illustrator, Inkscape)

---

### 1.15 Saving Workflow to Project
**Test Steps:**
1. Make changes to flowchart:
   - Move nodes
   - Edit task details
   - Change layout direction
2. Click "Next" button to proceed
3. Complete wizard and save project
4. Navigate to Projects list
5. Open the created project
6. Go to "Workflow" tab

**Expected Results:**
- ✅ All changes persist
- ✅ Node positions saved
- ✅ Layout direction saved
- ✅ Task modifications saved
- ✅ No duplicate tasks created

---

## Test Suite 2: Project Detail Page - Workflow Tab

### 2.1 Read-Only Flowchart Rendering
**Path:** Projects → Click Project → Workflow Tab

**Test Steps:**
1. Open an existing project with saved workflow
2. Click "Workflow" tab
3. Verify flowchart renders in read-only mode

**Expected Results:**
- ✅ Flowchart displays all tasks
- ✅ Node positions match saved state
- ✅ Layout direction correct
- ✅ Toolbar controls present but appropriate for read-only

**Check Console For:**
- ✅ "Project Workflow Bridge initialized" message
- ✅ "ProjectWorkflow component mounted successfully"

---

### 2.2 Read-Only Interaction
**Test Steps:**
1. Try to drag nodes (should be disabled)
2. Click on task nodes
3. Try to delete tasks (should be disabled)
4. Right-click on nodes
5. Try toolbar controls (Auto Layout, Layout Toggle, Export)

**Expected Results:**
- ❌ Cannot drag nodes
- ❌ Cannot edit tasks
- ❌ Cannot delete tasks
- ✅ Can view task details (read-only panel)
- ✅ Export functions work
- ⚠️ Auto Layout/Layout Toggle may be disabled in read-only mode

---

### 2.3 Export from Read-Only View
**Test Steps:**
1. In project detail workflow tab
2. Click "Export" → "Export as PNG"
3. Click "Export" → "Export as SVG"
4. Click "Export" → "Export as JSON"
5. Verify all exports work

**Expected Results:**
- ✅ All export formats work same as wizard
- ✅ Downloaded files reflect current project workflow

---

### 2.4 Subtask Viewing (Read-Only)
**Test Steps:**
1. Find task with subtasks badge
2. Click badge to open SubtaskModal
3. Verify subtask flowchart displays
4. Try to interact (should be read-only)
5. Close modal

**Expected Results:**
- ✅ Modal opens and displays subtasks
- ❌ Cannot edit subtasks
- ✅ Can view subtask details
- ✅ Export may work for subtasks

---

## Test Suite 3: Edge Cases & Stress Tests

### 3.1 Large Flowchart (10+ Tasks)
**Test Steps:**
1. Create project with 15+ tasks
2. Verify performance:
   - Rendering speed
   - Drag responsiveness
   - Auto Layout speed
3. Test export functions

**Expected Results:**
- ✅ Smooth performance (no lag)
- ✅ Auto Layout completes in <2 seconds
- ✅ Exports capture all nodes

---

### 3.2 Empty Task List
**Test Steps:**
1. Manipulate wizard to reach Step 4 with 0 tasks
2. Verify graceful handling

**Expected Results:**
- ✅ Shows empty state message
- ✅ No errors in console
- ✅ Can proceed or go back

---

### 3.3 Single Task
**Test Steps:**
1. Create project with only 1 task
2. Verify flowchart: Start → Task → End

**Expected Results:**
- ✅ All three nodes present
- ✅ Proper edge connections
- ✅ No layout issues

---

### 3.4 Very Long Task Names
**Test Steps:**
1. Create task with 200+ character name
2. Verify node displays properly

**Expected Results:**
- ✅ Text truncates or wraps
- ✅ Node doesn't break layout
- ✅ Full name visible in details panel

---

### 3.5 Special Characters in Task Names
**Test Steps:**
1. Create tasks with:
   - Emojis: "Deploy 🚀 to Production"
   - Symbols: "Setup <Database> & [Cache]"
   - Unicode: "Tâche Française"

**Expected Results:**
- ✅ Characters display correctly
- ✅ No encoding issues
- ✅ Export preserves characters

---

### 3.6 Rapid Interactions
**Test Steps:**
1. Quickly click multiple nodes in succession
2. Rapidly open/close details panel
3. Spam Auto Layout button
4. Rapidly toggle layout direction

**Expected Results:**
- ✅ No crashes
- ✅ State remains consistent
- ✅ Animations complete properly

---

### 3.7 Browser Refresh During Editing
**Test Steps:**
1. Make changes to flowchart in Step 4
2. Refresh browser (F5)
3. Verify state

**Expected Results:**
- ⚠️ **Expected Behavior:** Changes may be lost (Livewire wire:ignore)
- ✅ Flowchart still renders
- ✅ Returns to saved state

---

### 3.8 Multiple Browser Tabs
**Test Steps:**
1. Open project in two tabs
2. Edit workflow in Tab 1
3. Switch to Tab 2 and reload

**Expected Results:**
- ✅ Tab 2 shows updated workflow after reload
- ⚠️ Real-time sync not expected (Livewire limitation)

---

## Test Suite 4: Responsive & Accessibility

### 4.1 Window Resizing
**Test Steps:**
1. View flowchart in Step 4
2. Resize browser window (smaller/larger)
3. Verify responsiveness

**Expected Results:**
- ✅ Flowchart adjusts to container
- ✅ Toolbar remains accessible
- ✅ Details panel doesn't overflow

---

### 4.2 Zoom Controls
**Test Steps:**
1. Use React Flow minimap (bottom-right)
2. Use browser zoom (Ctrl +/-)
3. Scroll wheel zoom on canvas

**Expected Results:**
- ✅ React Flow zoom works smoothly
- ✅ Browser zoom doesn't break layout
- ⚠️ Scroll wheel zoom may be disabled

---

### 4.3 Keyboard Navigation
**Test Steps:**
1. Try keyboard shortcuts:
   - Delete key (on selected node)
   - Escape key (close modals)
   - Tab key (navigate UI)

**Expected Results:**
- ⚠️ **KNOWN LIMITATION:** Keyboard shortcuts not fully implemented yet
- ✅ Tab navigation works on toolbar buttons
- ✅ Escape closes modals

---

## Test Suite 5: Known Issues & Limitations

### 5.1 Regenerate Workflow Button (❌ NOT FUNCTIONAL)
**Test Steps:**
1. In Step 4, locate "Regenerate Workflow" button (if present)
2. Click button
3. Observe behavior

**Expected Results:**
- ❌ **KNOWN ISSUE:** Button exists but doesn't regenerate dependencies
- ❌ May only re-layout existing nodes
- 🔴 **CRITICAL GAP:** Needs AI integration

---

### 5.2 PDF Export (❌ MISSING)
**Test Steps:**
1. Click "Export" dropdown
2. Check for "Export as PDF" option

**Expected Results:**
- ❌ **KNOWN ISSUE:** PDF option not present
- ✅ PNG and SVG work as alternatives
- 🔴 **CRITICAL GAP:** Needs jspdf library

---

### 5.3 Validation Badges on Nodes (🟡 PARTIAL)
**Test Steps:**
1. Look for validation indicators on task nodes (e.g., warning icons)

**Expected Results:**
- 🟡 **PARTIAL IMPLEMENTATION:** Logic exists, visual badges may not display
- ✅ ValidationPanel shows issues

---

### 5.4 Subtask Position Persistence (🟡 PARTIAL)
**Test Steps:**
1. Open subtask modal
2. Drag subtask nodes
3. Close modal
4. Reopen modal

**Expected Results:**
- 🟡 **PARTIAL IMPLEMENTATION:** Positions may not persist
- ⚠️ May reset to auto-layout on reopen

---

### 5.5 Manual Edge Persistence (🟡 PARTIAL)
**Test Steps:**
1. Manually create edge between tasks
2. Save project
3. Reload page

**Expected Results:**
- 🟡 **PARTIAL IMPLEMENTATION:** Edge may not persist to database
- ✅ Edge exists during current session

---

## Bug Reporting Template

When you find a bug, please document:

```
**Bug Title:** [Short description]

**Severity:** Critical / High / Medium / Low

**Steps to Reproduce:**
1.
2.
3.

**Expected Result:**


**Actual Result:**


**Console Errors:** (Copy from browser console)


**Screenshots:** (If applicable)


**Browser:** Chrome 120 / Firefox 121 / etc.
```

---

## Testing Completion Checklist

After completing all tests, verify:

- [ ] All Test Suite 1 tests completed
- [ ] All Test Suite 2 tests completed
- [ ] At least 3 edge case tests completed
- [ ] Responsive test on at least 2 window sizes
- [ ] Bugs documented using template
- [ ] Console logs reviewed for errors
- [ ] Export functionality tested for all 3 formats
- [ ] Subtask modal tested (if tasks have subtasks)

---

## Next Steps After Testing

1. **Document all bugs found** using the template
2. **Prioritize issues** (Critical → High → Medium → Low)
3. **Share feedback** with development team
4. **Decide on implementation priorities** for remaining features:
   - Regenerate Workflow (4-6h)
   - PDF Export (2h)
   - Subtask Position Persistence (2-3h)
   - Validation Badges on Nodes (2-3h)
   - Manual Edge Persistence (3-4h)

---

## Support & Questions

- Check browser console for error messages (F12 → Console tab)
- Enable React DevTools for component inspection
- Review CollabFlow_Flowchart_Complete_Design_Specification.md for expected behavior

**Good luck with testing! 🚀**
