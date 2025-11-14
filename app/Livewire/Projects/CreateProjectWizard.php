<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Services\AIEngineService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreateProjectWizard extends Component
{
    use WithFileUploads;

    // Step tracking
    public $currentStep = 1;
    public $totalSteps = 4;

    // Step 1: Project Details
    public $name = '';
    public $description = '';
    public $domain = '';
    public $timeline = '';
    public $start_date = '';
    public $end_date = '';
    public $team_size = null;

    // Step 2: Goals & Context (redesigned to match design spec)
    public $goals = []; // Array of goal objects: [{id, title, description, priority}]
    public $goalIdCounter = 0; // Counter for generating unique goal IDs
    public $referenceDocuments = [];
    public $successMetrics = '';
    public $constraints = '';
    public $optionalFieldsExpanded = false; // Collapsible state for UI

    // Step 3: Generate Tasks (with streaming support)
    public $tasks = [];
    public $isGenerating = false;
    public $streamingProgress = 0;
    public $streamingMessage = 'Analyzing your project...';
    public $currentStreamingStep = 1;
    public $aiAnalysis = null; // AI context analysis results
    public $usingFallback = false; // Whether we're using fallback task generation
    public $aiDependencies = []; // Dependencies from Python service
    public $aiMetadata = null; // Metadata from Python service (task counts, hours, etc.)

    // Step 4: Workflow Review (NEW)
    public $workflowState = null;
    public $showRegenerateConfirmation = false;
    public $preserveManualEdits = true;

    // Error handling and loading states
    public $errorMessage = '';
    public $errorField = '';
    public $isUploading = false;
    public $uploadProgress = 0;
    public $isSaving = false;
    public $showError = false;

    // Task Edit Modal
    public $showTaskEditModal = false;
    public $editingTaskIndex = null;
    public $editingTask = [
        'name' => '',
        'description' => '',
        'type' => 'ai',
        'estimated_hours' => null,
        'dependencies' => [],
    ];

    // Step 5: Final Review
    public $taskBreakdown = [
        'ai' => 0,
        'human' => 0,
        'hitl' => 0,
    ];

    // AI Engine Service
    protected AIEngineService $aiService;

    public function boot(AIEngineService $aiService)
    {
        $this->aiService = $aiService;
    }

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'description' => 'required|min:10|max:500',
        'domain' => 'required',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after:start_date',
        'team_size' => 'nullable|integer|min:1|max:1000',
        // Goals validation (array of goal objects)
        'goals' => 'required|array|min:1',
        'goals.*.title' => 'required|min:3|max:100',
        'goals.*.description' => 'required|min:10|max:500',
        'goals.*.priority' => 'required|in:high,medium,low',
        // File uploads
        'referenceDocuments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,md',
        // Optional fields
        'successMetrics' => 'nullable|string|max:1000',
        'constraints' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'name.required' => 'Please provide a project name.',
        'name.min' => 'Project name must be at least 3 characters.',
        'name.max' => 'Project name cannot exceed 100 characters.',
        'description.required' => 'Please describe your project.',
        'description.min' => 'Description must be at least 10 characters.',
        'description.max' => 'Description cannot exceed 500 characters.',
        'domain.required' => 'Please select a project domain.',
        'timeline.required' => 'Please select a project timeline.',
        'start_date.required' => 'Please select a start date.',
        'start_date.date' => 'Start date must be a valid date.',
        'end_date.required' => 'Please select an end date.',
        'end_date.date' => 'End date must be a valid date.',
        'end_date.after' => 'End date must be after the start date.',
        'team_size.integer' => 'Team size must be a number.',
        'team_size.min' => 'Team size must be at least 1.',
        'team_size.max' => 'Team size cannot exceed 1000.',
        // Goals validation messages (redesigned for array structure)
        'goals.required' => 'Please add at least one project goal.',
        'goals.array' => 'Goals must be a valid list.',
        'goals.min' => 'Please add at least one project goal.',
        'goals.*.title.required' => 'Goal title is required.',
        'goals.*.title.min' => 'Goal title must be at least 3 characters.',
        'goals.*.title.max' => 'Goal title cannot exceed 100 characters.',
        'goals.*.description.required' => 'Goal description is required.',
        'goals.*.description.min' => 'Goal description must be at least 10 characters.',
        'goals.*.description.max' => 'Goal description cannot exceed 500 characters.',
        'goals.*.priority.required' => 'Goal priority is required.',
        'goals.*.priority.in' => 'Goal priority must be High, Medium, or Low.',
        // File upload validation messages
        'referenceDocuments.*.file' => 'Each reference document must be a valid file.',
        'referenceDocuments.*.max' => 'Each file must not exceed 10MB.',
        'referenceDocuments.*.mimes' => 'Only PDF, DOC, DOCX, TXT, and MD files are allowed.',
        // Optional fields
        'successMetrics.max' => 'Success metrics cannot exceed 1000 characters.',
        'constraints.max' => 'Constraints cannot exceed 1000 characters.',
    ];

    protected $validationAttributes = [
        'name' => 'project name',
        'description' => 'project description',
        'domain' => 'project domain',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'team_size' => 'team size',
        'goals' => 'project goals',
        'goals.*.title' => 'goal title',
        'goals.*.description' => 'goal description',
        'goals.*.priority' => 'goal priority',
        'referenceDocuments.*' => 'reference document',
        'successMetrics' => 'success metrics',
        'constraints' => 'constraints',
        'newTask.name' => 'task name',
        'newTask.description' => 'task description',
        'newTask.type' => 'task type',
        'newTask.estimated_hours' => 'estimated hours',
        'editTask.name' => 'task name',
        'editTask.description' => 'task description',
        'editTask.type' => 'task type',
        'editTask.estimated_hours' => 'estimated hours',
    ];

    public function mount()
    {
        // Set default dates
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(30)->format('Y-m-d');
        // Set default timeline
        $this->timeline = 'medium_term';

        // Initialize with one default goal
        $this->goals = [
            [
                'id' => $this->goalIdCounter++,
                'title' => '',
                'description' => '',
                'priority' => 'medium'
            ]
        ];
    }

    /**
     * Add a new goal to the goals array
     */
    public function addGoal()
    {
        $this->goals[] = [
            'id' => $this->goalIdCounter++,
            'title' => '',
            'description' => '',
            'priority' => 'medium'
        ];

        // Dispatch toast notification
        $this->dispatch('show-toast', message: 'New goal added', type: 'success', duration: 3000);
    }

    /**
     * Remove a goal from the goals array
     * @param int $goalId - The unique ID of the goal to remove
     */
    public function removeGoal($goalId)
    {
        $this->goals = array_values(array_filter($this->goals, function($goal) use ($goalId) {
            return $goal['id'] !== $goalId;
        }));

        // Ensure at least one goal remains
        if (empty($this->goals)) {
            $this->goals = [
                [
                    'id' => $this->goalIdCounter++,
                    'title' => '',
                    'description' => '',
                    'priority' => 'medium'
                ]
            ];
        }

        // Dispatch toast notification
        $this->dispatch('show-toast', message: 'Goal removed', type: 'info', duration: 3000);
    }

    /**
     * Update the priority of a specific goal
     * @param int $goalId - The unique ID of the goal
     * @param string $priority - New priority (high, medium, low)
     */
    public function updateGoalPriority($goalId, $priority)
    {
        foreach ($this->goals as &$goal) {
            if ($goal['id'] === $goalId) {
                $goal['priority'] = $priority;
                break;
            }
        }
    }

    /**
     * Remove a file from the reference documents array
     * @param int $index - Array index of the file to remove
     */
    public function removeFile($index)
    {
        try {
            if (isset($this->referenceDocuments[$index])) {
                array_splice($this->referenceDocuments, $index, 1);
                $this->dispatch('show-toast', message: 'File removed successfully', type: 'info', duration: 3000);
            }
        } catch (\Exception $e) {
            $this->handleError('Failed to remove file. Please try again.', 'referenceDocuments');
            $this->dispatch('show-toast', message: 'Failed to remove file', type: 'error', duration: 4000);
            logger()->error('Error removing file: ' . $e->getMessage());
        }
    }

    public function nextStep()
    {
        try {
            logger()->info('nextStep called, current step: ' . $this->currentStep);

            $this->clearError();
            $this->validateCurrentStep();

            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;

                logger()->info('Advanced to step: ' . $this->currentStep);

                // Step 3: UI will trigger async task generation on mount
                // No blocking call here - prevents 504 timeout
                if ($this->currentStep === 3 && empty($this->tasks)) {
                    // Set flag to indicate generation should start
                    $this->isGenerating = true;
                }

                // Calculate task breakdown when entering step 4 (Final Review)
                if ($this->currentStep === 4) {
                    $this->calculateTaskBreakdown();
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors naturally
            throw $e;
        } catch (\Exception $e) {
            $this->handleError('Failed to proceed: ' . $e->getMessage());
            logger()->error('Error in nextStep: ' . $e->getMessage(), [
                'current_step' => $this->currentStep,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    protected function validateCurrentStep()
    {
        if ($this->currentStep === 1) {
            logger()->info('Validating Step 1...', [
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]);

            $this->validate([
                'name' => 'required|min:3|max:100',
                'description' => 'required|min:10|max:500',
                'domain' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'team_size' => 'nullable|integer|min:1|max:1000',
            ]);

            logger()->info('Step 1 validation passed!');
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'goals' => 'required|array|min:1',
                'goals.*.title' => 'required|min:3|max:100',
                'goals.*.description' => 'required|min:10|max:500',
                'goals.*.priority' => 'required|in:high,medium,low',
                'referenceDocuments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,md',
                'successMetrics' => 'nullable|string|max:1000',
                'constraints' => 'nullable|string|max:1000',
            ]);
        }
    }

    public function removeDocument($index)
    {
        try {
            if (isset($this->referenceDocuments[$index])) {
                array_splice($this->referenceDocuments, $index, 1);
            }
        } catch (\Exception $e) {
            $this->handleError('Failed to remove document. Please try again.', 'referenceDocuments');
            logger()->error('Error removing document: ' . $e->getMessage());
        }
    }

    // Task Edit Modal Methods
    public function openTaskEditModal($taskIndex)
    {
        if (isset($this->tasks[$taskIndex])) {
            $this->editingTaskIndex = $taskIndex;
            $this->editingTask = $this->tasks[$taskIndex];
            $this->showTaskEditModal = true;
        }
    }

    public function closeTaskEditModal()
    {
        $this->showTaskEditModal = false;
        $this->editingTaskIndex = null;
        $this->editingTask = [
            'name' => '',
            'description' => '',
            'type' => 'ai',
            'estimated_hours' => null,
            'dependencies' => [],
        ];
        $this->resetErrorBag();
    }

    public function saveTaskEdit()
    {
        // Validate edited task
        $this->validate([
            'editingTask.name' => 'required|min:3|max:200',
            'editingTask.description' => 'nullable|max:1000',
            'editingTask.type' => 'required|in:ai,human,hitl',
            'editingTask.estimated_hours' => 'nullable|numeric|min:0.5|max:999',
        ], [
            'editingTask.name.required' => 'Task name is required.',
            'editingTask.name.min' => 'Task name must be at least 3 characters.',
            'editingTask.name.max' => 'Task name cannot exceed 200 characters.',
            'editingTask.type.required' => 'Task type is required.',
            'editingTask.type.in' => 'Invalid task type selected.',
            'editingTask.estimated_hours.numeric' => 'Estimated hours must be a number.',
            'editingTask.estimated_hours.min' => 'Estimated hours must be at least 0.5.',
        ]);

        // Update the task in the array
        if ($this->editingTaskIndex !== null) {
            $this->tasks[$this->editingTaskIndex] = $this->editingTask;
            $this->closeTaskEditModal();
        }
    }

    public function regenerateSingleTask($taskIndex)
    {
        if (isset($this->tasks[$taskIndex])) {
            // TODO: Call Python API to regenerate single task
            // For now, just show a message
            $this->handleError('Single task regeneration will be available when streaming is implemented.', 'tasks');
        }
    }

    /**
     * Centralized error handling
     */
    protected function handleError($message, $field = '')
    {
        $this->errorMessage = $message;
        $this->errorField = $field;
        $this->showError = true;

        // Auto-hide error after 5 seconds
        $this->dispatch('error-occurred', ['message' => $message]);
    }

    /**
     * Clear error state
     */
    public function clearError()
    {
        $this->errorMessage = '';
        $this->errorField = '';
        $this->showError = false;
    }

    /**
     * Analyze project context with Python AI service
     */
    public function analyzeProject()
    {
        try {
            $this->streamingMessage = 'Analyzing project context...';
            $this->currentStreamingStep = 1;
            $this->streamingProgress = 10;

            // Prepare data for Python service
            $projectData = [
                'details' => [
                    'name' => $this->name,
                    'description' => $this->description,
                    'domain' => $this->domain,
                    'timeline' => $this->timeline,
                    'team_size' => $this->team_size ?? 1,
                ],
                'goals' => [
                    'goals' => array_column(
                        array_filter($this->goals, fn($g) => !empty($g['title'])),
                        'title'
                    ),
                    'success_metrics' => $this->successMetrics,
                    'constraints' => $this->constraints,
                ]
            ];

            // Call Python service
            $analysis = $this->aiService->analyzeContext($projectData);

            if ($analysis) {
                $this->aiAnalysis = $analysis;
                $this->usingFallback = false;
                Log::info('AI context analysis completed', [
                    'domain' => $analysis['domain'] ?? 'unknown',
                    'complexity' => $analysis['complexity'] ?? 'unknown'
                ]);

                // Notify user of successful analysis
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => sprintf(
                        'Project analyzed: %s complexity, estimated %d tasks',
                        ucfirst(strtolower($analysis['complexity'] ?? 'medium')),
                        $analysis['estimated_task_count'] ?? 0
                    )
                ]);
            } else {
                // Fallback: Use rule-based analysis
                $this->aiAnalysis = $this->fallbackAnalysis();
                $this->usingFallback = true;
                Log::warning('Using fallback analysis - Python service unavailable');

                // Notify user of fallback
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'AI service unavailable. Using basic analysis. Results may be less detailed.'
                ]);
            }

            $this->streamingProgress = 30;
            $this->currentStreamingStep = 2;

        } catch (\Exception $e) {
            // Use fallback on error
            $this->aiAnalysis = $this->fallbackAnalysis();
            $this->usingFallback = true;
            Log::error('Project analysis failed, using fallback', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Upload documents to ChromaDB for RAG-based task generation
     */
    private function uploadDocumentsToChromaDB()
    {
        try {
            Log::info('Uploading documents to ChromaDB', [
                'component_id' => $this->getId(),
                'document_count' => count($this->referenceDocuments)
            ]);

            $this->streamingMessage = 'Uploading project documents to knowledge base...';
            $this->currentStreamingStep = 2;
            $this->streamingProgress = 15;

            // Generate temporary project ID for ChromaDB (same format as task generation)
            $tempProjectId = 'temp_' . \Illuminate\Support\Str::uuid();

            // Upload documents to Python service
            $result = $this->aiService->uploadDocuments($tempProjectId, $this->referenceDocuments);

            if ($result) {
                Log::info('Documents uploaded to ChromaDB successfully', [
                    'project_id' => $tempProjectId,
                    'result' => $result
                ]);

                // Check for partial success (some files failed)
                if (isset($result['partial_success']) && $result['partial_success']) {
                    $failedCount = count($result['failed_files'] ?? []);
                    $this->dispatch('show-toast', [
                        'type' => 'warning',
                        'message' => "Documents uploaded with {$failedCount} file(s) skipped due to parsing errors."
                    ]);
                } else {
                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => 'Project documents uploaded successfully. Tasks will be generated based on document content.'
                    ]);
                }
            } else {
                Log::warning('Document upload failed or was skipped');
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Document upload failed. Tasks will be generated without document context.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error uploading documents to ChromaDB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't fail the entire wizard - just log and continue
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Document upload encountered an error. Continuing with task generation.'
            ]);
        }
    }

    /**
     * Check generation status - called by wire:poll
     * This triggers generation if needed WITHOUT blocking (uses background job)
     */
    public function checkGenerationStatus()
    {
        $cacheKey = "task_generation_{$this->getId()}";

        // Check if we need to START generation (dispatch job)
        if ($this->isGenerating && empty($this->tasks) && !cache()->has($cacheKey) && !cache()->has("{$cacheKey}_started")) {
            logger()->info('Poll detected generation needed - dispatching background job');

            // Mark as started to prevent duplicate jobs
            cache()->put("{$cacheKey}_started", true, now()->addMinutes(10));

            // Analyze project if not already done
            if (!$this->aiAnalysis) {
                $this->analyzeProject();
            }

            // Upload documents to ChromaDB before task generation
            if (!empty($this->referenceDocuments)) {
                $this->uploadDocumentsToChromaDB();
                // Move to step 3 after document upload (step 2 is documents)
                $this->currentStreamingStep = 3;
                $this->streamingProgress = 25;
            } else {
                // No documents, move straight to step 2 (task generation)
                $this->currentStreamingStep = 2;
                $this->streamingProgress = 15;
            }

            // Prepare context for job
            $context = [
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain ?: 'custom',
                'goals' => array_column(
                    array_filter($this->goals, fn($g) => !empty($g['title'])),
                    'title'
                ),
            ];

            // Dispatch background job (returns immediately!)
            \App\Jobs\GenerateProjectTasks::dispatch(
                $this->getId(),
                $context,
                $this->aiAnalysis
            );

            $this->streamingMessage = 'Starting AI task generation...';
        }

        // Check for RESULTS from background job
        if (cache()->has($cacheKey)) {
            $result = cache()->get($cacheKey);

            if ($result['status'] === 'completed') {
                $this->tasks = $result['tasks'];
                $this->aiDependencies = $result['dependencies'] ?? [];
                $this->aiMetadata = $result['metadata'] ?? null;
                $this->usingFallback = false;

                logger()->info('Task generation completed from background job', [
                    'task_count' => count($this->tasks),
                    'tasks' => $this->tasks // Debug: log actual tasks
                ]);

                // Clear cache
                cache()->forget($cacheKey);
                cache()->forget("{$cacheKey}_started");

                // Update progress to completion AND stop loading
                $this->streamingProgress = 100;
                $this->currentStreamingStep = 4;
                $this->isGenerating = false; // CRITICAL: Stop loading screen

                // Notify user
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => sprintf(
                        'AI generated %d tasks (%d AI-suitable, %d human-required, %d hybrid)',
                        count($this->tasks),
                        $result['metadata']['ai_tasks'] ?? 0,
                        $result['metadata']['human_tasks'] ?? 0,
                        $result['metadata']['hitl_tasks'] ?? 0
                    )
                ]);

            } elseif ($result['status'] === 'failed') {
                // Use fallback
                $this->tasks = $this->fallbackTasks();
                $this->aiDependencies = [];
                $this->aiMetadata = null;
                $this->usingFallback = true;

                cache()->forget($cacheKey);
                cache()->forget("{$cacheKey}_started");

                $this->streamingProgress = 100;
                $this->currentStreamingStep = 4;
                $this->isGenerating = false; // CRITICAL: Stop loading screen

                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'AI service unavailable. Generated basic task structure.'
                ]);
            }
        }

        // Simulate incremental progress for UI feedback while job runs
        if ($this->isGenerating && empty($this->tasks) && cache()->has("{$cacheKey}_started")) {
            if ($this->streamingProgress < 90) {
                $this->streamingProgress = min(90, $this->streamingProgress + 5);

                // Update step based on progress
                if ($this->streamingProgress >= 30 && $this->currentStreamingStep < 2) {
                    $this->currentStreamingStep = 2;
                    $this->streamingMessage = 'Generating task breakdown...';
                } elseif ($this->streamingProgress >= 60 && $this->currentStreamingStep < 3) {
                    $this->currentStreamingStep = 3;
                    $this->streamingMessage = 'Estimating effort...';
                }
            }
        }

        return [
            'isGenerating' => $this->isGenerating,
            'currentStep' => $this->currentStreamingStep,
            'progress' => $this->streamingProgress,
            'tasksCount' => count($this->tasks),
        ];
    }

    /**
     * Generate tasks - now called asynchronously via Livewire polling
     */
    public function generateTasks()
    {
        try {
            $this->clearError();
            $this->isGenerating = true;
            $this->currentStreamingStep = 1;
            $this->streamingProgress = 0;
            $this->streamingMessage = 'Analyzing project context...';
            $this->tasks = [];

            // Step 1: Analyze project context if not already done
            if (!$this->aiAnalysis) {
                $this->analyzeProject();
            }

            // Step 2: Generate tasks using AI service
            $this->streamingMessage = 'Generating tasks with AI...';
            $this->streamingProgress = 40;
            $this->currentStreamingStep = 2;

            $tempProjectId = 'temp_' . Str::uuid();

            $context = [
                'name' => $this->name,
                'description' => $this->description,
                'domain' => $this->domain ?: 'custom',
                'goals' => array_column(
                    array_filter($this->goals, fn($g) => !empty($g['title'])),
                    'title'
                ),
            ];

            // Call Python service for task generation
            $result = $this->aiService->generateTasks(
                $tempProjectId,
                auth()->id(),
                $context,
                $this->aiAnalysis
            );

            if ($result && isset($result['tasks']) && !empty($result['tasks'])) {
                $this->tasks = $result['tasks'];
                $this->aiDependencies = $result['dependencies'] ?? [];
                $this->aiMetadata = $result['metadata'] ?? null;
                $this->usingFallback = false;
                Log::info('AI task generation completed', [
                    'task_count' => count($result['tasks']),
                    'dependencies_count' => count($this->aiDependencies),
                    'ai_tasks' => $result['metadata']['ai_tasks'] ?? 0,
                    'human_tasks' => $result['metadata']['human_tasks'] ?? 0,
                    'hitl_tasks' => $result['metadata']['hitl_tasks'] ?? 0
                ]);

                // Notify user of successful AI generation
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => sprintf(
                        'AI generated %d tasks (%d AI-suitable, %d human-required, %d hybrid)',
                        count($result['tasks']),
                        $result['metadata']['ai_tasks'] ?? 0,
                        $result['metadata']['human_tasks'] ?? 0,
                        $result['metadata']['hitl_tasks'] ?? 0
                    )
                ]);
            } else {
                // Fallback: Generate basic tasks
                $this->tasks = $this->fallbackTasks();
                $this->aiDependencies = [];
                $this->aiMetadata = null;
                $this->usingFallback = true;
                Log::warning('Using fallback task generation - Python service unavailable or returned no tasks');

                // Notify user of fallback
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'AI service unavailable. Generated basic task structure. Please review and customize tasks manually.'
                ]);
            }

            if (empty($this->tasks)) {
                throw new \Exception('Failed to generate tasks. Please try again.');
            }

            $this->streamingProgress = 80;
            $this->currentStreamingStep = 3;
            $this->streamingMessage = 'Finalizing tasks...';

            // Tasks will appear progressively via JavaScript polling
            // The UI will handle the streaming animation

        } catch (\Exception $e) {
            $this->isGenerating = false;
            $this->currentStreamingStep = 1;
            $this->streamingProgress = 0;

            // Try fallback one more time on error
            if (empty($this->tasks)) {
                try {
                    $this->tasks = $this->fallbackTasks();
                    $this->usingFallback = true;
                    Log::warning('Using fallback tasks after error', [
                        'error' => $e->getMessage()
                    ]);
                } catch (\Exception $fallbackError) {
                    $this->handleError('Failed to generate tasks: ' . $e->getMessage(), 'tasks');
                    Log::error('Task generation error and fallback failed', [
                        'error' => $e->getMessage(),
                        'fallback_error' => $fallbackError->getMessage(),
                        'domain' => $this->domain,
                        'goals' => $this->goals,
                    ]);
                }
            }
        }
    }

    public function getStreamingStatus()
    {
        // This method is called by JavaScript to poll streaming status
        return [
            'isGenerating' => $this->isGenerating,
            'currentStep' => $this->currentStreamingStep,
            'progress' => $this->streamingProgress,
            'message' => $this->streamingMessage,
            'tasksCount' => count($this->tasks),
        ];
    }

    public function completeGeneration()
    {
        // Called when streaming animation is complete - now start showing tasks progressively
        $this->isGenerating = false;
        $this->currentStreamingStep = 4;
        $this->streamingProgress = 100;

        // AI tasks are already in $this->tasks from generateTasks()
        // Just update their status to 'generating' for UI animation
        $this->tasks = array_map(function($task) {
            return array_merge($task, ['status' => 'generating']);
        }, $this->tasks);

        // Dispatch event to start progressive completion
        $this->dispatch('start-task-streaming', ['totalTasks' => count($this->tasks)]);
    }

    public function completeTask($taskIndex)
    {
        // Called by frontend to progressively complete tasks during animation
        // This is ONLY for UI animation - don't actually change status
        if (isset($this->tasks[$taskIndex])) {
            $this->tasks[$taskIndex]['status'] = 'complete'; // Use 'complete' (not 'completed') for UI-only state
        }
    }

    /**
     * Generate a single task with streaming (for future real-time generation)
     */
    public function generateSingleTask($taskData)
    {
        $task = [
            'id' => Str::uuid()->toString(),
            'name' => $taskData['name'],
            'description' => $taskData['description'] ?? '',
            'type' => $taskData['type'] ?? 'human',
            'status' => 'pending',
            'estimated_hours' => $taskData['estimated_hours'] ?? 0,
            'dependencies' => $taskData['dependencies'] ?? [],
        ];

        $this->tasks[] = $task;

        // Dispatch event to update UI
        $this->dispatch('task-generated', $task);
    }

    /**
     * DEPRECATED: No longer used in production
     * Kept for reference only - use generateTasks() which calls AI service
     */
    protected function mockGenerateTasks()
    {
        // Mock AI-generated tasks based on domain
        $taskTemplates = [
            'web_development' => [
                ['name' => 'Setup project repository and version control', 'type' => 'human', 'status' => 'pending', 'description' => 'Initialize Git repository, configure branching strategy, and set up version control workflow', 'estimated_hours' => 4, 'dependencies' => []],
                ['name' => 'Design database schema', 'type' => 'human', 'status' => 'pending', 'description' => 'Create entity-relationship diagrams and define database tables, relationships, and indexes', 'estimated_hours' => 8, 'dependencies' => []],
                ['name' => 'Create wireframes and mockups', 'type' => 'human', 'status' => 'pending', 'description' => 'Design user interface layouts and create interactive prototypes for key pages', 'estimated_hours' => 12, 'dependencies' => []],
                ['name' => 'Implement authentication system', 'type' => 'ai', 'status' => 'pending', 'description' => 'Build user registration, login, password reset, and session management functionality', 'estimated_hours' => 16, 'dependencies' => []],
                ['name' => 'Build frontend components', 'type' => 'ai', 'status' => 'pending', 'description' => 'Develop reusable UI components based on design mockups', 'estimated_hours' => 24, 'dependencies' => []],
                ['name' => 'Review and optimize code', 'type' => 'hitl', 'status' => 'pending', 'description' => 'Review codebase for performance, security, and maintainability improvements', 'estimated_hours' => 8, 'dependencies' => []],
                ['name' => 'Write unit tests', 'type' => 'ai', 'status' => 'pending', 'description' => 'Create comprehensive test coverage for all critical functionality', 'estimated_hours' => 16, 'dependencies' => []],
                ['name' => 'Deploy to staging environment', 'type' => 'human', 'status' => 'pending', 'description' => 'Configure staging server and deploy application for testing', 'estimated_hours' => 6, 'dependencies' => []],
                ['name' => 'Conduct user acceptance testing', 'type' => 'hitl', 'status' => 'pending', 'description' => 'Perform end-to-end testing with stakeholders and gather feedback', 'estimated_hours' => 12, 'dependencies' => []],
                ['name' => 'Production deployment', 'type' => 'human', 'status' => 'pending', 'description' => 'Deploy application to production environment and monitor for issues', 'estimated_hours' => 8, 'dependencies' => []],
            ],
            'marketing' => [
                ['name' => 'Conduct market research', 'type' => 'human', 'status' => 'pending', 'description' => 'Analyze market trends, competitor strategies, and customer needs', 'estimated_hours' => 16, 'dependencies' => []],
                ['name' => 'Define target audience personas', 'type' => 'human', 'status' => 'pending', 'description' => 'Create detailed customer personas based on research data', 'estimated_hours' => 8, 'dependencies' => []],
                ['name' => 'Generate content ideas with AI', 'type' => 'ai', 'status' => 'pending', 'description' => 'Use AI to brainstorm content topics and campaign themes', 'estimated_hours' => 4, 'dependencies' => []],
                ['name' => 'Create social media content calendar', 'type' => 'ai', 'status' => 'pending', 'description' => 'Plan and schedule social media posts across all platforms', 'estimated_hours' => 12, 'dependencies' => []],
                ['name' => 'Design marketing materials', 'type' => 'human', 'status' => 'pending', 'description' => 'Create graphics, videos, and other visual assets for campaigns', 'estimated_hours' => 20, 'dependencies' => []],
                ['name' => 'Review and refine messaging', 'type' => 'hitl', 'status' => 'pending', 'description' => 'Review all content for brand consistency and effectiveness', 'estimated_hours' => 6, 'dependencies' => []],
                ['name' => 'Launch campaigns', 'type' => 'human', 'status' => 'pending', 'description' => 'Execute marketing campaigns across all channels', 'estimated_hours' => 8, 'dependencies' => []],
                ['name' => 'Monitor analytics and metrics', 'type' => 'ai', 'status' => 'pending', 'description' => 'Track campaign performance and generate insights', 'estimated_hours' => 10, 'dependencies' => []],
            ],
            'data_analysis' => [
                ['name' => 'Collect and organize data sources', 'type' => 'human', 'status' => 'pending', 'description' => 'Gather data from various sources and organize into structured format', 'estimated_hours' => 12, 'dependencies' => []],
                ['name' => 'Clean and preprocess data', 'type' => 'ai', 'status' => 'pending', 'description' => 'Handle missing values, outliers, and normalize data for analysis', 'estimated_hours' => 16, 'dependencies' => []],
                ['name' => 'Perform exploratory data analysis', 'type' => 'ai', 'status' => 'pending', 'description' => 'Generate statistical summaries and identify patterns in data', 'estimated_hours' => 20, 'dependencies' => []],
                ['name' => 'Build predictive models', 'type' => 'ai', 'status' => 'pending', 'description' => 'Develop and train machine learning models for predictions', 'estimated_hours' => 32, 'dependencies' => []],
                ['name' => 'Validate model accuracy', 'type' => 'hitl', 'status' => 'pending', 'description' => 'Test models with validation data and refine as needed', 'estimated_hours' => 12, 'dependencies' => []],
                ['name' => 'Create visualizations and dashboards', 'type' => 'human', 'status' => 'pending', 'description' => 'Design interactive charts and dashboards to present findings', 'estimated_hours' => 16, 'dependencies' => []],
                ['name' => 'Present findings to stakeholders', 'type' => 'human', 'status' => 'pending', 'description' => 'Prepare and deliver presentation of analysis results', 'estimated_hours' => 8, 'dependencies' => []],
            ],
        ];

        $tasks = $taskTemplates[$this->domain] ?? $taskTemplates['web_development'];

        // Add unique IDs to each task
        $tasksWithIds = array_map(function($task) {
            $task['id'] = Str::uuid()->toString();
            return $task;
        }, $tasks);

        // Add example subtasks to first few tasks for testing
        if (count($tasksWithIds) > 0) {
            $tasksWithIds[0]['subtasks'] = [
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Initialize Git repository',
                    'title' => 'Initialize Git repository',
                    'description' => 'Create new Git repo and configure settings',
                    'type' => 'human',
                    'estimatedHours' => 1,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Configure branching strategy',
                    'title' => 'Configure branching strategy',
                    'description' => 'Set up main, develop, and feature branch workflow',
                    'type' => 'human',
                    'estimatedHours' => 2,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Add .gitignore and README',
                    'title' => 'Add .gitignore and README',
                    'description' => 'Create initial project documentation',
                    'type' => 'human',
                    'estimatedHours' => 1,
                ],
            ];
        }

        if (count($tasksWithIds) > 1) {
            $tasksWithIds[1]['subtasks'] = [
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Create ER diagram',
                    'title' => 'Create ER diagram',
                    'description' => 'Design entity relationships',
                    'type' => 'human',
                    'estimatedHours' => 3,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Define table schemas',
                    'title' => 'Define table schemas',
                    'description' => 'Specify columns, types, and constraints',
                    'type' => 'human',
                    'estimatedHours' => 4,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Plan indexes and optimization',
                    'title' => 'Plan indexes and optimization',
                    'description' => 'Identify indexes needed for performance',
                    'type' => 'human',
                    'estimatedHours' => 1,
                ],
            ];
        }

        return $tasksWithIds;
    }

    protected function calculateTaskBreakdown()
    {
        $this->taskBreakdown = [
            'ai' => collect($this->tasks)->where('type', 'ai')->count(),
            'human' => collect($this->tasks)->where('type', 'human')->count(),
            'hitl' => collect($this->tasks)->where('type', 'hitl')->count(),
        ];
    }

    public function confirmDeleteTask($index)
    {
        $this->taskToDelete = $index;
        $this->showDeleteConfirmation = true;
    }

    public function cancelDelete()
    {
        $this->taskToDelete = null;
        $this->showDeleteConfirmation = false;
    }

    public function removeTask($index = null)
    {
        try {
            // Use the stored index if not provided
            $taskIndex = $index ?? $this->taskToDelete;

            if ($taskIndex !== null && isset($this->tasks[$taskIndex])) {
                unset($this->tasks[$taskIndex]);
                $this->tasks = array_values($this->tasks);
                $this->calculateTaskBreakdown();
                $this->dispatch('tasks-updated');
            }

            // Close confirmation dialog
            $this->showDeleteConfirmation = false;
            $this->taskToDelete = null;
        } catch (\Exception $e) {
            $this->handleError('Failed to remove task. Please try again.', 'tasks');
            logger()->error('Error removing task: ' . $e->getMessage());
        }
    }

    /**
     * Confirmation dialog
     */
    public $showDeleteConfirmation = false;
    public $taskToDelete = null;

    /**
     * Add a new manual task
     */
    public $showAddTaskModal = false;
    public $newTask = [
        'name' => '',
        'description' => '',
        'type' => 'human',
        'estimated_hours' => 0,
    ];

    public function openAddTaskModal()
    {
        $this->showAddTaskModal = true;
        $this->newTask = [
            'name' => '',
            'description' => '',
            'type' => 'human',
            'estimated_hours' => 0,
        ];
    }

    public function closeAddTaskModal()
    {
        $this->showAddTaskModal = false;
    }

    public function addManualTask()
    {
        try {
            $this->clearError();

            $this->validate([
                'newTask.name' => 'required|min:3|max:200',
                'newTask.description' => 'nullable|max:500',
                'newTask.type' => 'required|in:ai,human,hitl',
                'newTask.estimated_hours' => 'nullable|integer|min:0|max:1000',
            ], [
                'newTask.name.required' => 'Please provide a task name.',
                'newTask.name.min' => 'Task name must be at least 3 characters.',
                'newTask.name.max' => 'Task name cannot exceed 200 characters.',
                'newTask.description.max' => 'Description cannot exceed 500 characters.',
                'newTask.type.required' => 'Please select a task type.',
                'newTask.type.in' => 'Task type must be AI, Human, or HITL.',
                'newTask.estimated_hours.integer' => 'Estimated hours must be a number.',
                'newTask.estimated_hours.min' => 'Estimated hours cannot be negative.',
                'newTask.estimated_hours.max' => 'Estimated hours cannot exceed 1000.',
            ]);

            $task = [
                'id' => Str::uuid()->toString(),
                'name' => $this->newTask['name'],
                'description' => $this->newTask['description'],
                'type' => $this->newTask['type'],
                'status' => 'pending',
                'estimated_hours' => $this->newTask['estimated_hours'] ?? 0,
                'dependencies' => [],
            ];

            $this->tasks[] = $task;
            $this->closeAddTaskModal();
            $this->dispatch('tasks-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors naturally
            throw $e;
        } catch (\Exception $e) {
            $this->handleError('Failed to add task. Please try again.', 'newTask');
            logger()->error('Error adding task: ' . $e->getMessage());
        }
    }


    /**
     * Toggle subtasks visibility
     */
    public $expandedSubtasks = [];

    public function toggleSubtasks($taskIndex)
    {
        if (isset($this->expandedSubtasks[$taskIndex])) {
            unset($this->expandedSubtasks[$taskIndex]);
        } else {
            $this->expandedSubtasks[$taskIndex] = true;
        }
    }

    /**
     * Add a new subtask to a specific task
     */
    public function addSubtask($taskIndex)
    {
        if (!isset($this->tasks[$taskIndex])) {
            return;
        }

        $subtask = [
            'id' => Str::uuid()->toString(),
            'name' => '',
            'title' => '',
            'description' => '',
            'type' => 'human',
            'estimatedHours' => null,
        ];

        if (!isset($this->tasks[$taskIndex]['subtasks'])) {
            $this->tasks[$taskIndex]['subtasks'] = [];
        }

        $this->tasks[$taskIndex]['subtasks'][] = $subtask;

        // Automatically expand this task's subtasks
        $this->expandedSubtasks[$taskIndex] = true;

        $this->dispatch('show-toast',
            message: 'New subtask added',
            type: 'success',
            duration: 3000
        );
    }

    /**
     * Update a subtask within a task
     */
    public function updateSubtask($taskIndex, $subtaskIndex, $field, $value)
    {
        if (!isset($this->tasks[$taskIndex]['subtasks'][$subtaskIndex])) {
            return;
        }

        $this->tasks[$taskIndex]['subtasks'][$subtaskIndex][$field] = $value;

        // Also update the 'title' field if 'name' is updated (for compatibility)
        if ($field === 'name') {
            $this->tasks[$taskIndex]['subtasks'][$subtaskIndex]['title'] = $value;
        }
    }

    /**
     * Remove a subtask from a task
     */
    public function removeSubtask($taskIndex, $subtaskIndex)
    {
        if (!isset($this->tasks[$taskIndex]['subtasks'][$subtaskIndex])) {
            return;
        }

        array_splice($this->tasks[$taskIndex]['subtasks'], $subtaskIndex, 1);

        $this->dispatch('show-toast',
            message: 'Subtask removed',
            type: 'info',
            duration: 3000
        );
    }

    /**
     * Save workflow state from React flowchart
     * Called by Alpine.js bridge when user drags nodes or makes changes
     *
     * @param array $state - Workflow state with nodes and edges
     */
    public function saveWorkflowState($state)
    {
        logger()->info('Saving workflow state', ['state' => $state]);

        // Store the workflow state (will be saved to DB when project is created)
        $this->workflowState = $state;

        logger()->info('Workflow state saved successfully');
    }

    /**
     * Update individual node position
     * Alternative method for more granular updates
     *
     * @param string $nodeId
     * @param array $position - {x: number, y: number}
     */
    public function updateNodePosition($nodeId, $position)
    {
        logger()->info('Updating node position', ['nodeId' => $nodeId, 'position' => $position]);

        if (!$this->workflowState) {
            $this->workflowState = ['nodes' => [], 'edges' => []];
        }

        // Find and update the node position
        $nodes = $this->workflowState['nodes'] ?? [];
        $nodeFound = false;

        foreach ($nodes as &$node) {
            if ($node['id'] === $nodeId) {
                $node['position'] = $position;
                $nodeFound = true;
                break;
            }
        }

        if (!$nodeFound) {
            // Add new node position
            $nodes[] = [
                'id' => $nodeId,
                'position' => $position,
            ];
        }

        $this->workflowState['nodes'] = $nodes;

        logger()->info('Node position updated successfully');
    }

    /**
     * Show confirmation dialog before regenerating
     */
    public function confirmRegenerateWorkflow()
    {
        $this->showRegenerateConfirmation = true;
    }

    /**
     * Cancel regeneration
     */
    public function cancelRegenerate()
    {
        $this->showRegenerateConfirmation = false;
        $this->preserveManualEdits = true;
    }

    /**
     * Regenerate workflow - Call AI to generate new task structure
     * This is called from Step 4 when user clicks "Regenerate Workflow"
     */
    public function regenerateWorkflow()
    {
        logger()->info('Regenerating workflow...', [
            'preserve_edits' => $this->preserveManualEdits
        ]);

        // Store current workflow state if preserving edits
        $savedPositions = [];
        $savedEdges = [];

        if ($this->preserveManualEdits && $this->workflowState) {
            // Extract manually saved positions
            $nodes = $this->workflowState['nodes'] ?? [];
            foreach ($nodes as $node) {
                if (isset($node['id']) && isset($node['position'])) {
                    $savedPositions[$node['id']] = $node['position'];
                }
            }

            // Extract manually created edges
            $edges = $this->workflowState['edges'] ?? [];
            foreach ($edges as $edge) {
                $savedEdges[] = [
                    'source' => $edge['source'],
                    'target' => $edge['target'],
                ];
            }

            logger()->info('Saved manual edits', [
                'positions' => count($savedPositions),
                'edges' => count($savedEdges)
            ]);
        }

        // Re-generate tasks using the AI service
        // This will create a fresh set of AI-generated tasks
        $oldTasks = $this->tasks;

        // Call generateTasks() to use real AI (will use fallback if service unavailable)
        $this->generateTasks();

        // If generation failed and tasks are empty, restore old tasks
        if (empty($this->tasks)) {
            $this->tasks = $oldTasks;
            $this->handleError('Failed to regenerate tasks. Please try again.', 'tasks');
            return;
        }

        if ($this->preserveManualEdits && !empty($savedPositions)) {
            // Try to match new tasks with old tasks by name and restore positions
            foreach ($this->tasks as &$newTask) {
                // Try to find matching old task
                foreach ($oldTasks as $oldTask) {
                    if ($newTask['name'] === $oldTask['name']) {
                        // Restore position if it was saved
                        if (isset($savedPositions[$oldTask['id']])) {
                            $newTask['id'] = $oldTask['id']; // Keep same ID
                            $newTask['position'] = $savedPositions[$oldTask['id']];
                        }
                        break;
                    }
                }
            }

            // Rebuild workflow state with saved positions and edges
            $this->workflowState = [
                'nodes' => array_map(function($task) use ($savedPositions) {
                    return [
                        'id' => $task['id'],
                        'type' => 'task',
                        'position' => $savedPositions[$task['id']] ?? null,
                        'data' => $task,
                    ];
                }, $this->tasks),
                'edges' => $savedEdges,
                'lastUpdated' => now()->toISOString(),
            ];
        } else {
            // Complete regeneration - reset workflow state
            $this->workflowState = null;
        }

        // Close confirmation dialog
        $this->showRegenerateConfirmation = false;
        $this->preserveManualEdits = true;

        // Dispatch success notification
        $this->dispatch('workflow-regenerated');

        logger()->info('Workflow regenerated successfully', [
            'task_count' => count($this->tasks),
            'preserved_edits' => $this->preserveManualEdits
        ]);
    }

    public function createProject()
    {
        try {
            $this->clearError();
            $this->isSaving = true;

            $this->validate();

            // Handle file uploads - store reference documents
            $documentPaths = [];
            if (!empty($this->referenceDocuments)) {
                $this->isUploading = true;
                $totalFiles = count($this->referenceDocuments);

                foreach ($this->referenceDocuments as $index => $file) {
                    try {
                        // Check file size
                        if ($file->getSize() > 10485760) { // 10MB in bytes
                            throw new \Exception("File {$file->getClientOriginalName()} exceeds maximum size of 10MB");
                        }

                        $path = $file->store('reference-documents', 'public');
                        $documentPaths[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime' => $file->getMimeType(),
                        ];

                        // Update progress
                        $this->uploadProgress = round((($index + 1) / $totalFiles) * 100);
                    } catch (\Exception $e) {
                        logger()->error('File upload error: ' . $e->getMessage(), [
                            'file' => $file->getClientOriginalName(),
                        ]);
                        throw new \Exception("Failed to upload {$file->getClientOriginalName()}: {$e->getMessage()}");
                    }
                }

                $this->isUploading = false;
                $this->uploadProgress = 0;
            }

            // Create the project with transaction
            \DB::beginTransaction();

            $project = Project::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'status' => 'planning', // Start as 'planning', user can activate when ready
                'progress' => 0,
                'domain' => $this->domain,
                'timeline' => $this->timeline,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'team_size' => $this->team_size,
                'goals' => $this->goals,
                'reference_documents' => !empty($documentPaths) ? json_encode($documentPaths) : null,
                'success_metrics' => $this->successMetrics,
                'constraints' => $this->constraints,
                'workflow_state' => $this->workflowState ? json_encode($this->workflowState) : null,
                'ai_analysis' => $this->aiAnalysis, // Store AI analysis results
                'workflow_metadata' => $this->aiMetadata ? [
                    'total_tasks' => $this->aiMetadata['total_tasks'] ?? count($this->tasks),
                    'ai_tasks' => $this->aiMetadata['ai_tasks'] ?? 0,
                    'human_tasks' => $this->aiMetadata['human_tasks'] ?? 0,
                    'hitl_tasks' => $this->aiMetadata['hitl_tasks'] ?? 0,
                    'total_estimated_hours' => $this->aiMetadata['total_estimated_hours'] ?? 0,
                    'avg_validation_score' => $this->aiMetadata['avg_validation_score'] ?? null,
                    'has_parallel_branches' => $this->aiMetadata['has_parallel_branches'] ?? false,
                    'generated_at' => now()->toIso8601String(),
                    'using_fallback' => $this->usingFallback,
                ] : null,
            ]);

        // Build task ID mapping (Python task IDs -> Laravel UUIDs)
        $pythonToLaravelIds = [];
        $laravelTasks = [];

        // First pass: Create all tasks and build ID mapping
        foreach ($this->tasks as $index => $task) {
            $pythonTaskId = $task['id'] ?? null;
            $laravelTaskId = Str::uuid()->toString();

            if ($pythonTaskId) {
                $pythonToLaravelIds[$pythonTaskId] = $laravelTaskId;
            }

            // Force status to 'pending' - ignore UI animation status ('complete')
            $status = ($task['status'] ?? 'pending');
            if ($status === 'complete' || $status === 'completed' || $status === 'generating') {
                $status = 'pending';
            }

            $newTask = new Task([
                'project_id' => $project->id,
                'name' => $task['name'],
                'description' => $task['description'] ?? null,
                'type' => $task['type'],
                'complexity' => $task['complexity'] ?? $task['metadata']['complexity'] ?? null,
                'sequence' => $task['sequence'] ?? $task['metadata']['sequence'] ?? ($index + 1),
                'ai_suitability_score' => $task['ai_suitability_score'] ?? $task['metadata']['ai_suitability_score'] ?? null,
                'confidence_score' => $task['confidence_score'] ?? $task['metadata']['confidence_score'] ?? null,
                'validation_score' => isset($task['metadata']['validation']['score']) ? $task['metadata']['validation']['score'] : null,
                'status' => $status,
                'estimated_hours' => $task['estimatedHours'] ?? $task['estimated_hours'] ?? null,
                'dependencies' => [], // Will be set in second pass
                'metadata' => array_merge(
                    isset($task['subtasks']) ? ['subtasks' => $task['subtasks']] : [],
                    isset($task['metadata']) ? $task['metadata'] : [],
                    ['python_task_id' => $pythonTaskId]
                ),
            ]);

            $newTask->id = $laravelTaskId;
            $newTask->save();
            $laravelTasks[$pythonTaskId] = $newTask;
        }

        // Second pass: Set dependencies from Python response
        if (!empty($this->aiDependencies)) {
            foreach ($this->aiDependencies as $dependency) {
                $fromTaskId = $dependency['from_task_id'] ?? null;
                $toTaskId = $dependency['to_task_id'] ?? null;
                $depType = $dependency['type'] ?? 'blocks';

                if ($fromTaskId && $toTaskId && isset($pythonToLaravelIds[$fromTaskId]) && isset($pythonToLaravelIds[$toTaskId])) {
                    $laravelFromId = $pythonToLaravelIds[$fromTaskId];
                    $laravelToId = $pythonToLaravelIds[$toTaskId];

                    // Update the 'to' task to include 'from' task as dependency
                    $toTask = Task::find($laravelToId);
                    if ($toTask) {
                        $currentDeps = $toTask->dependencies ?? [];
                        $currentDeps[] = $laravelFromId;
                        $toTask->dependencies = $currentDeps;

                        // Store dependency type in metadata
                        $metadata = $toTask->metadata ?? [];
                        if (!isset($metadata['dependency_types'])) {
                            $metadata['dependency_types'] = [];
                        }
                        $metadata['dependency_types'][$laravelFromId] = $depType;
                        $toTask->metadata = $metadata;

                        $toTask->save();
                    }
                }
            }

            Log::info('Dependencies processed from Python service', [
                'total_dependencies' => count($this->aiDependencies),
                'task_id_mappings' => count($pythonToLaravelIds)
            ]);
        }

            \DB::commit();

            $this->isSaving = false;

            session()->flash('success', 'Project created successfully!');

            return redirect()->route('projects.show', $project->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isSaving = false;
            $this->isUploading = false;
            $this->uploadProgress = 0;
            // Let Livewire handle validation errors naturally
            throw $e;
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->isSaving = false;
            $this->isUploading = false;
            $this->uploadProgress = 0;

            $errorMsg = 'Failed to create project: ' . $e->getMessage();
            $this->handleError($errorMsg);

            logger()->error('Project creation error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'project_name' => $this->name,
                'trace' => $e->getTraceAsString(),
            ]);

            // Show user-friendly message
            session()->flash('error', 'Failed to create project. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Fallback analysis when Python service unavailable
     */
    protected function fallbackAnalysis(): array
    {
        $estimatedHours = str_word_count($this->description) * 2; // Rough estimate

        return [
            'domain' => $this->domain ?: 'unknown',
            'complexity' => 'medium',
            'estimated_task_count' => min(max(count($this->goals) * 3, 7), 20),
            'key_objectives' => array_column(
                array_filter($this->goals, fn($g) => !empty($g['title'])),
                'title'
            ),
            'challenges' => [
                'Limited AI analysis available',
                'Manual task planning may be required'
            ],
            'required_skills' => [],
            'recommendations' => [
                'Define clear milestones',
                'Break work into manageable tasks',
                'Set up regular check-ins',
            ],
            'confidence_score' => 0.7,
        ];
    }

    /**
     * Fallback task generation when Python service unavailable
     */
    protected function fallbackTasks(): array
    {
        return [
            [
                'id' => 'task_001',
                'name' => 'Project Setup & Planning',
                'description' => 'Set up project structure, define requirements, create initial documentation',
                'type' => 'human',
                'estimated_hours' => 8,
                'complexity' => 'medium',
                'sequence' => 1,
                'status' => 'pending',
                'dependencies' => [],
                'subtasks' => [],
                'metadata' => [],
            ],
            [
                'id' => 'task_002',
                'name' => 'Core Implementation',
                'description' => 'Implement main features and functionality',
                'type' => 'human',
                'estimated_hours' => 40,
                'complexity' => 'high',
                'sequence' => 2,
                'status' => 'pending',
                'dependencies' => ['task_001'],
                'subtasks' => [],
                'metadata' => [],
            ],
            [
                'id' => 'task_003',
                'name' => 'Testing & Quality Assurance',
                'description' => 'Write tests, perform QA, fix bugs',
                'type' => 'human',
                'estimated_hours' => 16,
                'complexity' => 'medium',
                'sequence' => 3,
                'status' => 'pending',
                'dependencies' => ['task_002'],
                'subtasks' => [],
                'metadata' => [],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.projects.create-project-wizard');
    }
}
