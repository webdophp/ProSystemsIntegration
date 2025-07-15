<?php

namespace webdophp\ProSystemsIntegration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use webdophp\ProSystemsIntegration\Models\ProSystemsOperation;

class ProSystemsFetchAllData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        foreach (ProSystemsOperation::where('received_detailed', false)->cursor() as $operation) {
            ProSystemsProcessOperation::dispatch($operation)->onQueue('pro-systems');
        }
    }

}