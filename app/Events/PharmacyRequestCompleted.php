<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PharmacyRequestCompleted implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $doctorId,
        public readonly int $pharmacyRequestId,
        public readonly string $message,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('doctor.' . $this->doctorId)];
    }

    public function broadcastAs(): string
    {
        return 'PharmacyRequestCompleted';
    }

    public function broadcastWith(): array
    {
        return [
            'doctor_id' => $this->doctorId,
            'pharmacy_request_id' => $this->pharmacyRequestId,
            'message' => $this->message,
        ];
    }
}