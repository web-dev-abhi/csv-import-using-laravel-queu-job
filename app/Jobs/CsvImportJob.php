<?php

namespace App\Jobs;

use App\Models\User2;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CsvImportJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;
    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        $this->onQueue('dataInsert');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            User2::insert($this->data);
            // info('dddd', $this->data);
        } catch (\Throwable $th) {
            // info('err-insertJob', [$th->getMessage()]);
        }
    }
}
