<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class AIEngineService
{
    private string $baseUrl;
    private int $timeout;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('services.python.url');
        $this->timeout = config('services.python.timeout');
        $this->enabled = config('services.python.enabled');
    }

    /**
     * Check if Python service is healthy
     *
     * @return bool
     */
    public function healthCheck(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['status']) && $data['status'] === 'healthy';
            }

            return false;

        } catch (ConnectionException $e) {
            Log::warning('Python service health check failed: Connection error', [
                'error' => $e->getMessage()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::warning('Python service health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Analyze project context
     *
     * @param array $projectData
     * @return array|null
     */
    public function analyzeContext(array $projectData): ?array
    {
        if (!$this->enabled) {
            Log::info('Python service disabled, skipping context analysis');
            return null;
        }

        // Create cache key from project data
        $cacheKey = 'context_analysis_' . md5(json_encode($projectData));

        // Check cache first (valid for 1 hour)
        return Cache::remember($cacheKey, 3600, function () use ($projectData) {
            try {
                Log::info('Calling Python service for context analysis', [
                    'project_name' => $projectData['details']['name'] ?? 'Unknown'
                ]);

                $response = Http::timeout($this->timeout)
                    ->retry(3, 100, function ($exception, $request) {
                        // Only retry on connection errors, not validation errors
                        return $exception instanceof ConnectionException;
                    })
                    ->post("{$this->baseUrl}/api/context/analyze", [
                        'project_details' => $projectData['details'] ?? [],
                        'goals_context' => $projectData['goals'] ?? [],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['analysis'])) {
                        Log::info('Context analysis successful', [
                            'domain' => $data['analysis']['domain'] ?? 'unknown',
                            'complexity' => $data['analysis']['complexity'] ?? 'unknown'
                        ]);

                        return $data['analysis'];
                    }

                    Log::error('Context analysis response missing analysis field', [
                        'response' => $data
                    ]);
                    return null;
                }

                Log::error('Context analysis failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;

            } catch (ConnectionException $e) {
                Log::error('Context analysis connection failed', [
                    'error' => $e->getMessage(),
                    'url' => $this->baseUrl
                ]);
                return null;

            } catch (RequestException $e) {
                Log::error('Context analysis request failed', [
                    'error' => $e->getMessage(),
                    'response' => $e->response->body() ?? null
                ]);
                return null;

            } catch (\Exception $e) {
                Log::error('Context analysis exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return null;
            }
        });
    }

    /**
     * Generate tasks for project
     *
     * @param string $projectId
     * @param string $userId
     * @param array $context
     * @param array $analysis
     * @return array|null
     */
    public function generateTasks(
        string $projectId,
        string $userId,
        array $context,
        array $analysis
    ): ?array {
        if (!$this->enabled) {
            Log::info('Python service disabled, skipping task generation');
            return null;
        }

        try {
            Log::info('Calling Python service for task generation', [
                'project_id' => $projectId,
                'user_id' => $userId
            ]);

            // Longer timeout for task generation (Python service takes 82-120s for task generation)
            $response = Http::timeout(150)
                ->retry(2, 200, function ($exception, $request) {
                    return $exception instanceof ConnectionException;
                })
                ->post("{$this->baseUrl}/api/tasks/generate", [
                    'project_id' => $projectId,
                    'user_id' => $userId,
                    'context' => $context,
                    'context_analysis' => $analysis,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!isset($data['tasks'])) {
                    Log::error('Task generation response missing tasks field', [
                        'response' => $data
                    ]);
                    return null;
                }

                // Map Python tasks to Laravel format
                $tasks = collect($data['tasks'])->map(function ($task) {
                    $mapped = $this->mapPythonTaskToLaravel($task);
                    Log::debug('Mapped task', [
                        'python_assigned_to' => $task['assigned_to'] ?? 'MISSING',
                        'laravel_type' => $mapped['type'],
                        'task_name' => $mapped['name']
                    ]);
                    return $mapped;
                })->toArray();

                Log::info('Task generation successful', [
                    'task_count' => count($tasks),
                    'ai_tasks' => $data['metadata']['ai_tasks'] ?? 0,
                    'human_tasks' => $data['metadata']['human_tasks'] ?? 0,
                    'hitl_tasks' => $data['metadata']['hitl_tasks'] ?? 0,
                    'actual_task_types' => collect($tasks)->countBy('type')->toArray()
                ]);

                return [
                    'tasks' => $tasks,
                    'dependencies' => $data['dependencies'] ?? [],
                    'metadata' => $data['metadata'] ?? [],
                ];
            }

            Log::error('Task generation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;

        } catch (ConnectionException $e) {
            Log::error('Task generation connection failed', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Task generation exception', [
                'error' => $e->getMessage(),
                'project_id' => $projectId
            ]);
            return null;
        }
    }

    /**
     * Map Python task format to Laravel format
     *
     * @param array $pythonTask
     * @return array
     */
    private function mapPythonTaskToLaravel(array $pythonTask): array
    {
        // Convert Python's assigned_to (UPPERCASE) to Laravel's type (lowercase)
        $type = $this->convertAssignmentType($pythonTask['assigned_to'] ?? 'HUMAN');

        return [
            'id' => $pythonTask['id'], // Keep Python ID for dependency mapping
            'name' => $pythonTask['name'],
            'description' => $pythonTask['description'] ?? '',
            'type' => $type,
            'estimated_hours' => $pythonTask['estimated_hours'] ?? 0,
            'complexity' => $pythonTask['complexity'] ?? null,
            'sequence' => $pythonTask['sequence'] ?? 0,
            'status' => strtolower($pythonTask['status'] ?? 'pending'),
            'dependencies' => $pythonTask['dependencies'] ?? [],
            'subtasks' => $pythonTask['subtasks'] ?? [],
            'metadata' => [
                'ai_suitability_score' => $pythonTask['ai_suitability_score'] ?? null,
                'confidence_score' => $pythonTask['confidence_score'] ?? null,
                'validation' => $pythonTask['validation'] ?? null,
                'position' => $pythonTask['position'] ?? null,
                'python_task_id' => $pythonTask['id'] ?? null
            ],
        ];
    }

    /**
     * Convert Python's assignment type to Laravel's task type
     * As of v1.1, Python returns lowercase values that match Laravel directly
     *
     * @param string $pythonType
     * @return string
     */
    private function convertAssignmentType(string $pythonType): string
    {
        // Python now returns lowercase values: 'ai', 'human', 'hitl', 'unassigned'
        // Laravel expects: 'ai', 'human', 'hitl'
        $type = strtolower($pythonType);

        return match($type) {
            'ai' => 'ai',
            'human' => 'human',
            'hitl' => 'hitl',
            'hybrid' => 'hitl', // Backward compatibility: old 'hybrid' -> 'hitl'
            'unassigned' => 'human', // Map unassigned to human
            default => 'human'   // Safe fallback
        };
    }

    /**
     * Validate task structure
     *
     * @param array $task
     * @return array|null
     */
    public function validateTask(array $task): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/api/tasks/validate", [
                    'task' => $task
                ]);

            if ($response->successful()) {
                return $response->json('validation');
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Task validation exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Regenerate specific tasks
     *
     * @param array $taskIds
     * @param array $existingTasks
     * @param array $context
     * @return array|null
     */
    public function regenerateTasks(
        array $taskIds,
        array $existingTasks,
        array $context
    ): ?array {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/tasks/regenerate", [
                    'task_ids' => $taskIds,
                    'existing_tasks' => $existingTasks,
                    'context' => $context,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['regenerated_tasks'])) {
                    return collect($data['regenerated_tasks'])
                        ->map(fn($task) => $this->mapPythonTaskToLaravel($task))
                        ->toArray();
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Task regeneration exception', [
                'error' => $e->getMessage(),
                'task_ids' => $taskIds
            ]);
            return null;
        }
    }

    /**
     * Upload documents to ChromaDB for RAG-based task generation
     *
     * @param string $projectId
     * @param array $files Array of UploadedFile objects or file paths
     * @return array|null Response from AI Engine
     */
    public function uploadDocuments(string $projectId, array $files): ?array
    {
        if (!$this->enabled) {
            Log::info('Python service disabled, skipping document upload');
            return null;
        }

        if (empty($files)) {
            Log::warning('No files provided for document upload');
            return null;
        }

        try {
            $parser = new DocumentParser();
            $documents = [];
            $failedFiles = [];

            Log::info('Starting document upload to ChromaDB', [
                'project_id' => $projectId,
                'file_count' => count($files)
            ]);

            foreach ($files as $file) {
                try {
                    // Validate file
                    $parser->validateFile($file);

                    // Extract text content
                    $content = $parser->extractContent($file);

                    $documents[] = [
                        'content' => $content,
                        'source' => $file->getClientOriginalName(),
                        'type' => $file->getClientOriginalExtension(),
                        'created_at' => now()->toIso8601String()
                    ];

                    Log::info('Document parsed successfully', [
                        'file' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'content_length' => strlen($content)
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to parse document', [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);

                    $failedFiles[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];

                    // Continue with other files instead of failing completely
                    continue;
                }
            }

            // If no documents were successfully parsed, return error
            if (empty($documents)) {
                throw new \Exception('No documents could be parsed successfully. Errors: ' . json_encode($failedFiles));
            }

            // Upload to Python service
            Log::info('Uploading documents to Python service', [
                'project_id' => $projectId,
                'document_count' => count($documents)
            ]);

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/api/context/documents/upload", [
                    'project_id' => $projectId,
                    'documents' => $documents
                ]);

            if ($response->failed()) {
                throw new \Exception("Document upload failed: {$response->body()}");
            }

            $result = $response->json();

            Log::info('Documents uploaded to ChromaDB successfully', [
                'project_id' => $projectId,
                'uploaded_count' => count($documents),
                'failed_count' => count($failedFiles),
                'response' => $result
            ]);

            // Add failed files info to result
            if (!empty($failedFiles)) {
                $result['partial_success'] = true;
                $result['failed_files'] = $failedFiles;
            }

            return $result;

        } catch (ConnectionException $e) {
            Log::error('Document upload connection failed', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'url' => $this->baseUrl
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Document upload exception', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
