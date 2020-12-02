<?php

    namespace Reactable\Utilities;

    use App\Events\OutputEvent;

    class Output {
        public static function send( string $message ) {
            event( new OutputEvent( "[" . microtime( true ) . "] " . $message ) );
        }
    }
