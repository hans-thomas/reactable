<?php

    namespace Reactable\Examples;


    use phpDocumentor\Reflection\Types\Scalar;
    use function React\Promise\all;
    use React\Promise\Deferred;
    use function React\Promise\map;
    use React\Promise\Promise;
    use function React\Promise\race;
    use function React\Promise\reduce;
    use function React\Promise\reject;
    use function React\Promise\some;
    use Reactable\Utilities\Output;

    class Promises {
        public static function done() {
            $deferred = new Deferred();
            $deferred->promise()->done( function( $value ) {
                Output::send( 'Done => ' . $value );
            } );

            $deferred->resolve( 10 );
        }

        public static function then() {
            $deferred = new Deferred();

            $deferred->promise()->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );

                return $value + 1;
            } )->then( function( $value ) {
                Output::send( 'then #2 => ' . $value );

                return $value * 2;
            } )->done( function( $value ) {
                Output::send( 'done => ' . $value );
            } );

            $deferred->resolve( 10 );
        }

        public static function otherwise() {
            $deferred = new Deferred();

            $deferred->promise()->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );

                return $value;
            } )->then( function( $value ) {
                if ( $value < 10 ) {
                    throw new \Exception( 'the number must be greater than 10' );
                }

                return $value;
            } )->otherwise( function( \Throwable $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            } )->done( function( $value ) {
                Output::send( 'done => ' . $value );
            } );

            $deferred->resolve( rand( 5, 15 ) );
        }

        public static function always() {
            $deferred = new Deferred();

            $deferred->promise()->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );

                if ( $value < 5 ) {
                    throw new \Exception( 'insert a number that is greater than 5' );
                }

                return $value;
            } )->then( null, function( \Exception $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            } )->always( function() {
                Output::send( "always => this always run" );
            } )->done( function( $value ) {
                Output::send( 'done => ' . $value );
            } );

            $deferred->resolve( rand( 2, 9 ) );
        }

        public static function promise() {
            $promise = new Promise( function( callable $resolve, callable $reject ) {
                $sentence = 'the quick fox jump over the lazy dog.';
                $reverse  = '';
                foreach ( array_reverse( explode( ' ', $sentence ) ) as $word ) {
                    $reverse .= $word;
                }

                strlen( $reverse ) > 0 ? $resolve( $reverse ) : $reject( 'failed to reverse.' );
            } );

            $promise->done( function( $value ) {
                Output::send( 'reversed => ' . $value );
            }, function( $e ) {
                Output::send( 'Error! ' . $e );
            } );
        }

        public static function reject() {
            $promise = reject( 'promise rejected' );

            $promise->done( function( $value ) {
                Output::send( 'solved.' );
            }, function( $e ) {
                Output::send( 'Error! ' . $e );
            } );
        }

        public static function resolve() {
            $promise = \React\Promise\resolve( rand( 1, 9 ) );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );

                if ( $value <= 5 ) {
                    throw new \Exception( 'the number must be equals or greater than 5' );
                }

                return $value;
            } )->done( function( $value ) {
                Output::send( 'done, the number is greater than 5 => ' . $value );
            }, function( \Throwable $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            } );
        }

        public static function all() {
            $source1 = \React\Promise\resolve( 10 );
            $source2 = \React\Promise\resolve( 15 );

            $promise = \React\Promise\all( [ $source1, $source2 ] );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . json_encode( $value ) );

                return 'first value is : ' . $value[ 0 ] . ' and the second is : ' . $value[ 1 ];
            } )->done( function( $value ) {
                Output::send( 'done => ' . $value );
            } );
        }

        public static function race() {
            $source1 = \React\Promise\resolve( 10 );
            //            $source2 = \React\Promise\resolve( 50 );
            $source2 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 2 );
            } );
            $promise = race( [ $source2, $source1 ] );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );
            } );
        }

        public static function any() {
            $source1 = \React\Promise\resolve( 10 );
            $source2 = reject( 'rejected.' );
            $source3 = new Promise( function( callable $resolve, callable $reject ) {
                false === true ? $resolve( 'the end is near...' ) : $reject( "it make sense." );
            } );

            $promise = \React\Promise\any( [ $source1, $source2, $source3 ] );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );
            } );
        }

        public static function some() {
            $source1 = \React\Promise\resolve( 20 );
            $source2 = reject( 'rejected.' );
            $source3 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 'resolved.' );
            } );

            $promise = some( [ $source1, $source2, $source3 ], 1 );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );
            }, function( \Throwable $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            } );
        }

        public static function map() {
            $source1 = \React\Promise\resolve( 20 );
            $source2 = reject( 'rejected.' );
            $source3 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 'resolved.' );
            } );

            $promise = map( [ 'a', $source1, $source2, $source3 ], function( $item ) {
                Output::send( 'values => ' . $item );
            } );
        }

        public static function reduce() {
            $source1 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 1 );
            } );
            $source2 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 2 );
            } );
            $source3 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 3 );
            } );

            $source4 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 4 );
            } );
            $source5 = new Promise( function( callable $resolve, callable $reject ) {
                $resolve( 5 );
            } );

            $promise = reduce( [ $source1, $source2, $source3, $source4, $source5 ],
                function( $previous, $value, $index, $total ) {
                    Output::send( 'previous item : ' . ( $previous ?? 'null' ) . ' value : ' . $value . ' index : ' . $index . ' total values : ' . $total );

                    return $value+$previous;
                } );

            $promise->then( function( $value ) {
                Output::send( 'then #1 => ' . $value );
            }, function( \Throwable $e ) {
                Output::send( 'Error! ' . $e->getMessage() );
            } );
        }
    }
