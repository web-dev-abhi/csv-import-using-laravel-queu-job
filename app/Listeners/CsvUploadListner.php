<?php

namespace App\Listeners;

use App\Events\CsvImportEvent;
use App\Events\CsvUploadEvent;
use App\Jobs\CsvImportJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class CsvUploadListner implements ShouldQueue
{
    use InteractsWithQueue;
    public $queue = 'csvListner';
    public $timeout = 300;

    public function __construct() {}
    public function handle(CsvUploadEvent $event): void
    {
        try {
            $writer = SimpleExcelReader::create($event->file_path);
            $writer
                ->getRows()
                ->chunk(250)
                ->when(
                    $event->processInBatch,
                    function ($dataChunk) use ($event) {
                        $batch = Bus::batch([])
                            ->before(function (Batch $batch) use ($event) {
                                broadcast(new CsvImportEvent("Csv import started", $event->channel));
                            })
                            ->progress(function (Batch $batch) use ($event) {
                                $p = round((($batch->totalJobs - ($batch->pendingJobs < 0 ? 0 : $batch->pendingJobs)) * 100) / $batch->totalJobs, 0);
                                broadcast(new CsvImportEvent($p, $event->channel));
                            })
                            ->then(function (Batch $batch) use ($event) {
                                broadcast(new CsvImportEvent("Import completed", $event->channel));
                            })
                            ->catch(function (Batch $batch, \Throwable $e) use ($event) {
                                broadcast(new CsvImportEvent("{$e->getMessage()}", $event->channel));
                            })
                            ->finally(function (Batch $batch) use ($event) {
                                broadcast(new CsvImportEvent("Csv imported in " . $batch->createdAt->diffInSeconds(now(), true) . " seconds", $event->channel));
                            })
                            ->name('data_insert_batches')
                            ->onQueue('dataInsert');

                        $dataChunk->each(function ($data, $key) use ($batch) {
                            $batch->add((new CsvImportJob($data->toArray()))->delay(now()->addSeconds(5))
                                ->delay(now()->addSeconds(5)));
                            flush();
                        });
                        $batch->dispatchAfterResponse();
                    },
                    function ($dataChunk) {
                        $dataChunk->each(function ($data, $key) {
                            CsvImportJob::dispatch($data->toArray())->delay(now()->addSeconds(20));
                            flush();
                        });
                    }
                );
        } catch (\Throwable $th) {
            info('import-error', [$th->getMessage()]);
        }
    }
}
