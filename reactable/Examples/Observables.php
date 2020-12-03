<?php

    namespace Reactable\Examples;


    use React\EventLoop\Factory;
    use Reactable\Utilities\Observer;
    use Reactable\Utilities\Output;
    use Rx\Disposable\CallbackDisposable;
    use Rx\Observable;
    use Rx\ObserverInterface;
    use Rx\Scheduler\EventLoopScheduler;
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

        public static function start() {
            $observable = Observable::start( function() {
                return 27;
            } );

            $observable->subscribe( Observer::get() );
        }

        public static function interval() {
            $observable = Observable::interval( 500 )->take( 10 );

            $observable->subscribe( Observer::get() );
        }

        public static function timer() {
            $observable = Observable::timer( 3000 );

            $observable->subscribe( Observer::get() );
        }

        public static function share() {
            $observable = Observable::interval( 1000 )->take( 5 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->share();

            $published->subscribe( Observer::get( 1 ) );
            $published->subscribe( Observer::get( 2 ) );
            $published->subscribe( Observer::get( 3 ) );
        }

        public static function shareReplay() {
            $observable = Observable::interval( 1000 )->take( 7 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->shareReplay( 6 );

            $published->subscribe( Observer::get( 1 ) );
            $published->subscribe( Observer::get( 2 ) );
            $published->subscribe( Observer::get( 3 ) );
        }

        public static function shareValue() {
            $observable = Observable::interval( 1000 )->take( 10 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->shareValue( 20 );

            $published->subscribe( Observer::get( 1 ) );
            $published->subscribe( Observer::get( 2 ) );
            $published->subscribe( Observer::get( 3 ) );
        }

        public static function subscribeOn() {
            $loop = Factory::create();

            $observable = Observable::create( function( ObserverInterface $observer ) use ( $loop ) {
                $timer = $loop->addTimer( 2, function() use ( $observer ) {
                    $observer->onNext( 1 );
                    $observer->onNext( 2 );
                    $observer->onNext( 3 );
                    $observer->onCompleted();
                } );

                return new CallbackDisposable( function() use ( $loop, $timer ) {
                    if ( $timer ) {
                        $loop->cancelTimer( $timer );
                    } else {
                        $loop->stop();
                    }
                } );
            } );

            $observable->subscribeOn( new EventLoopScheduler( $loop ) )->subscribe( Observer::get() );

            $loop->run();
        }

        public static function toArray() {
            $observable = Observable::interval( 10 )->take( 5 )->toArray();

            $observable->subscribe( function( $item ) {
                Output::send( 'observer => ' . json_encode( $item ) );
            } );
        }

        public static function withLatestFrom() {
            $source1 = Observable::interval( 50 )->map( fn( $item ) => 'first ' . $item);
            $source2 = Observable::interval( 100 )->map( fn( $item ) => 'second ' . $item);
            $source3 = Observable::interval( 150 )->map( fn( $item ) => 'third ' . $item);

            $observable = $source1->withLatestFrom( [ $source2, $source3 ] )->take( 10 );

            $observable->subscribe( Observer::get() );
        }

        public static function zip() {
            $source1 = Observable::range( 0, 5 );
            $source2 = Observable::range( 6, 5 );
            $source3 = Observable::range( 11, 5 );

            $observable = $source1->zip( [ $source2, $source3 ], function( $s1, $s2, $s3 ) {
                return $s1 . ' : ' . $s2 . ' : ' . $s3;
            } )->take( 10 );

            $observable->subscribe( Observer::get() );
        }
    }
