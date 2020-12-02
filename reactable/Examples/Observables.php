<?php

    namespace Reactable\Examples;


    use Reactable\Utilities\Output;
    use Rx\Disposable\CallbackDisposable;
    use Rx\Observable;
    use Rx\ObserverInterface;
    use Rx\Subject\Subject;

    class Observables {
        public static function connectableObservable() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4, 6, 7, 8, 9 ] )
                                    ->map( function( $item ) use ( &$output ) {
                                        Output::send( "MAP => {$item}" );

                                        return $item += 1;
                                    } )
                                    ->filter( function( $item ) use ( &$output ) {
                                        Output::send( "FILTER => {$item}" );

                                        return $item % 2 == 0;
                                    } );

            $observer = new Observable\ConnectableObservable( $observable );

            $observer->subscribe( function( $item ) use ( &$output ) {
                Output::send( "OBSERVER #2 {$item}" );
            }, null, function() use ( &$output ) {
                Output::send( "OBSERVER #2 completed." );
            } );

            $observer->connect();
        }

        public static function observable() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4, 6, 7, 8, 9 ] )
                                    ->map( function( $item ) use ( &$output ) {
                                        Output::send( "MAP => {$item}" );

                                        return $item += 1;
                                    } )
                                    ->filter( function( $item ) use ( &$output ) {
                                        Output::send( "FILTER => {$item}" );

                                        return $item % 2 == 0;
                                    } );
            $observable->subscribe( function( $item ) use ( &$output ) {
                Output::send( "OBSERVER #1 {$item}" );
            }, null, function() use ( &$output ) {
                Output::send( "OBSERVER #1 completed." );
            } );

            $observable->subscribe( function( $item ) use ( &$output ) {
                Output::send( "OBSERVER #2 {$item}" );
            }, null, function() use ( &$output ) {
                Output::send( "OBSERVER #2 completed." );
            } );
        }

        public static function defer() {
            $observable = Observable::defer( function() {
                return Observable::range( 0, rand( 3, 10 ) );
            } );

            $observable->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
            $observable->subscribe( function( $item ) {
                Output::send( 'OBSERVER #2 => ' . $item );
            } );
        }

        public static function defaultEmpty() {
            Observable::range( 6, 5 )
                      ->take( 0 )
                      ->defaultIfEmpty( Observable::range( 1, 5 ) )
                      ->subscribe( function( $item ) {
                          Output::send( 'OBSERVER #1 => ' . $item );
                      } );
        }

        public static function create() {
            Observable::create( function( ObserverInterface $observer ) {
                $observer->onNext( 1 );
                $observer->onNext( 2 );
                $observer->onNext( 3 );
                $observer->onCompleted();

                return new CallbackDisposable( function() {
                    Output::send( 'CallbackDisposable' );
                } );
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            }, null, function() {
                Output::send( 'OnCompleted OBSERVER #1' );
            } );
        }

        public static function isEmpty() {
            Observable::empty()->isEmpty()->flatMap( function( $item ) {
                return Observable::of( $item == 1 ? 'Yes' : 'No' );
            } )->subscribe( function( $item ) {
                Output::send( 'observer : observable is empty? => ' . $item );
            } );
        }

        public static function multicast() {
            $subject = new Subject();
            $source  = Observable::range( 0, 6 )->multicast( $subject ); // a short hand for ConnectableObservable

            $subject->subscribe( function( $item ) {
                Output::send( 'observer #2 => ' . $item );
            } );
            $subject->subscribe( function( $item ) {
                Output::send( 'observer #3 => ' . $item );
            } );
            $source->connect();
        }

        public static function publishValue() {
            $observable = Observable::fromArray( range( 0, 1000 ) )->take( 10 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->publishValue( 'not yet.' );

            $published->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                Output::send( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        public static function publishLast() {
            $observable = Observable::range( 0, 20 )->take( 6 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->publishLast();

            $published->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                Output::send( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        public static function publish() {
            $observable = Observable::range( 0, 11 )->take( 5 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->publish();

            $published->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                Output::send( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        public static function fromIterator() {
            $generator = function() {
                for ( $i = 0; $i <= 10; $i ++ ) {
                    yield $i;
                }
            };

            Observable::fromIterator( $generator() )->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function fromPromise() {
            $promise = \React\Promise\resolve( 'promise' );

            Observable::fromPromise( $promise )->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function toPromise() {
            $promise = Observable::of( 'observable' )->toPromise();

            $promise->then( function( $item ) {
                Output::send( 'promise => ' . $item );
            } );
        }
    }
