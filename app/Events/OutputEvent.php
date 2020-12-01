<?php

    namespace App\Events;

    use Illuminate\Broadcasting\Channel;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Broadcasting\PresenceChannel;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Queue\SerializesModels;

    class OutputEvent implements ShouldBroadcast {
        use Dispatchable, InteractsWithSockets, SerializesModels;
        private $message;

        /**
         * Create a new event instance.
         *
         * @param string $message
         */
        public function __construct(string $message) {
            $this->message = $message;
        }

        public function broadcastOn() {
            return new Channel( 'output' );
        }

        public function broadcastAs() {
            return 'data';
        }

        public function broadcastWith() {
            return [
                'data' => $this->message
            ];
        }
    }
