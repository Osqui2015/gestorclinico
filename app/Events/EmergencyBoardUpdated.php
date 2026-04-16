<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EmergencyBoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly ?int $admissionId = null,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('emergency-board')];
    }

    public function broadcastAs(): string
    {
        return 'EmergencyBoardUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'refresh' => true,
            'admission_id' => $this->admissionId,
        ];
    }
}