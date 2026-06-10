<?php

namespace App\Jobs;

use App\Services\ImportResponseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $kuesionerId;
    protected $status;
    protected $userId;
    protected $deleteExisting;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $kuesionerId, string $status, int $userId, bool $deleteExisting = false)
    {
        $this->filePath = $filePath;
        $this->kuesionerId = $kuesionerId;
        $this->status = $status;
        $this->userId = $userId;
        $this->deleteExisting = $deleteExisting;
    }

    /**
     * Execute the job.
     */
    public function handle(ImportResponseService $service): void
    {
        $absolutePath = storage_path('app/' . $this->filePath);

        $result = $service->import($absolutePath, $this->kuesionerId, $this->status, $this->userId, $this->deleteExisting);

        // Save result in cache
        $result['selesai_pada'] = now()->toIso8601String();
        cache()->put("import_response_result_{$this->userId}", $result, now()->addHours(1));

        // Delete temporary file
        if (Storage::exists($this->filePath)) {
            Storage::delete($this->filePath);
        }
    }
}
