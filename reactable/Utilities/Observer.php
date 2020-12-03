<?php

    namespace Reactable\Utilities;


    use Rx\Observer\CallbackObserver;

    class Observer {
        public static function get( string $number = '0' ): CallbackObserver {
            return new CallbackObserver( function( $item ) use ( $number ) {
                if ( is_array( $item ) ) {
                    $item = json_encode( $item );
                }
                Output::send( "observer " . ( $number > 0 ? '#' . $number : null ) . " => " . $item  );
            }, function( \Exception $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            }, function() use ( $number ) {
                Output::send( "observer " . ( $number > 0 ? '#' . $number : null ) . " completed" );
            } );
        }
    }
