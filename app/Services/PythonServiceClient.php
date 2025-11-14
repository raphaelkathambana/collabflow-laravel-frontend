<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PythonServiceClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python.url');
        $this->timeout = config('services.python.timeout');
    }

    public function analyzeProjectContext(array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/projects/analyze-context", $data);

        return $response->json();
    }

    public function generateTasks(array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/tasks/generate", $data);

        return $response->json();
    }

    public function validateTasks(array $tasks): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->baseUrl}/api/tasks/validate", [
                'tasks' => $tasks
            ]);

        return $response->json();
    }

    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
