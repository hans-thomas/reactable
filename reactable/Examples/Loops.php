<?php

    namespace Reactable\Examples;


    use React\EventLoop\Factory;
    use Reactable\Utilities\Output;

    class Loops {
        /**
         * @throws \Exception
         */
        public static function addReadStream() {
            define( 'STDIN', fopen( "php://stdin", "r" ) );
            $loop = Factory::create();
            $file = fopen( realpath( __DIR__ . "/../../resources/views/home.blade.php" ), 'r' );
            $loop->addReadStream( $file, function( $stream ) use ( $loop ) {
                $chunk = fread( $stream, 50 );

                if ( $chunk == '' ) {
                    $loop->removeReadStream( $stream );
                    stream_set_blocking( $stream, true );
                    fclose( $stream );

                    return;
                }
                Output::send( strlen( $chunk ) . " bytes" );
            } );
            $loop->run();
        }
    }
