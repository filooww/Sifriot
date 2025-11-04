<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\FileMetadata;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetadataConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public FileMetadata $fileMetadata
    ) {}
}
