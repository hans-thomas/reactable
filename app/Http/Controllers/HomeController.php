<?php

    namespace App\Http\Controllers;

    use App\Events\OutputEvent;
    use Illuminate\Http\Request;
    use React\EventLoop\Factory;
    use Rx\Disposable\CallbackDisposable;
    use Rx\Observable;
    use Rx\Observable\ConnectableObservable;
    use Rx\ObserverInterface;
    use Rx\Scheduler;
    use Rx\Subject\Subject;

    class HomeController extends Controller {

        public function index() {
            return view( 'home' );
        }

        private function push( string $message ) {
            event( new OutputEvent( "[" . microtime( true ) . "] " . $message ) );
        }

        private function init() {
            $loop = Factory::create();
            /*$loop->addTimer( 5, function() use ( $loop ) {
                $this->push( 'async timer TICKED' );
                $loop->stop();
            } );*/
            Scheduler::setDefaultFactory( function() use ( $loop ) {
                return new Scheduler\EventLoopScheduler( $loop );
            } );
            register_shutdown_function( function() use ( $loop ) {
                //$this->push( 'timer stopped by register_shutdown_function' );
                $loop->stop();
            } );

            return $loop;
        }

        private function exe( \Closure $closure ) {
            $loop = $this->init();
            $closure();
            $loop->run();
        }

        public function run() {
            $this->exe( fn() => $this->publishValue());
        }

        private function publishValue() {
            $observable = Observable::fromArray( range( 0, 1000 ) )->take( 10 )->do( function( $item ) {
                $this->push( 'onNext => '. $item );
            } );

            $published = $observable->publishValue( 'not yet.' );

            $published->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                $this->push( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        private function publishLast() {
            $observable = Observable::range( 0, 20 )->take( 6 )->do( function( $item ) {
                $this->push( 'onNext => ' . $item );
            } );

            $published = $observable->publishLast();

            $published->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                $this->push( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        private function publish() {
            $observable = Observable::range( 0, 11 )->take( 5 )->do( function( $item ) {
                $this->push( 'onNext => ' . $item );
            } );

            $published = $observable->publish();

            $published->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            } );
            $published->subscribe( function( $item ) {
                $this->push( 'observer #2 => ' . $item );
            } );
            $published->connect();
        }

        private function fromIterator() {
            $generator = function() {
                for ( $i = 0; $i <= 10; $i ++ ) {
                    yield $i;
                }
            };

            Observable::fromIterator( $generator() )->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        private function fromPromise() {
            $promise = \React\Promise\resolve( 'promise' );

            Observable::fromPromise( $promise )->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        private function toPromise() {
            $promise = Observable::of( 'observable' )->toPromise();

            $promise->then( function( $item ) {
                $this->push( 'promise => ' . $item );
            } );
        }

        private function pluck() {
            Observable::fromArray( [
                [ 'id' => 1, 'name' => 'Hans', 'zip' => 65825 ],
                [ 'id' => 2, 'name' => 'ashley', 'zip' => 78945 ],
                [ 'id' => 3, 'name' => 'alicia', 'zip' => 12458 ],
            ] )->pluck( 'name' )->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        private function partition() {
            list( $evens, $odds ) = Observable::range( 0, 6 )->partition( function( $item ) {
                return $item % 2 === 0;
            } );
            $evens->subscribe( function( $item ) {
                $this->push( 'event value => ' . $item );
            } );
            $odds->subscribe( function( $item ) {
                $this->push( 'odd value => ' . $item );
            } );
        }

        private function multicast() {
            $subject = new Subject();
            $source  = Observable::range( 0, 6 )->multicast( $subject ); // a short hand for ConneactableObservable

            $subject->subscribe( function( $item ) {
                $this->push( 'observer #2 => ' . $item );
            } );
            $subject->subscribe( function( $item ) {
                $this->push( 'observer #3 => ' . $item );
            } );
            $source->connect();
        }

        private function mergeAll() {
            $source = Observable::range( 0, 6 )->map( function( $item ) {
                return Observable::of( $item )->repeat( $item < 0 ? 1 : $item );
            } );

            $observable = $source->mergeAll();
            $observable->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        private function merge() {
            $obseravble      = Observable::of( 1 )->repeat( 10 );
            $otherObseravble = Observable::of( 2 )->repeat( 10 );

            $mergedObservable = $obseravble->merge( $otherObseravble );

            $mergedObservable->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        private function min() {
            // we can define a comparer same as maxWithComparer
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->min();

            $observable->subscribe( function( $item ) {
                $this->push( 'obserer => ' . $item );
            } );
        }

        private function maxWithComparer() {
            $comparer   = function( $x, $y ) {
                return $x > $y ? 1 : ( $x < $y ? - 1 : 0 );
            };
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->max( $comparer );

            $observable->subscribe( function( $item ) {
                $this->push( 'obserer => ' . $item );
            } );
        }

        private function max() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->max();

            $observable->subscribe( function( $item ) {
                $this->push( 'obserer => ' . $item );
            } );
        }

        private function isEmpty() {
            Observable::empty()->isEmpty()->flatMap( function( $item ) {
                return Observable::of( $item == 1 ? 'Yes' : 'No' );
            } )->subscribe( function( $item ) {
                $this->push( 'observer : observable is empty? => ' . $item );
            } );
        }

        private function groupBy() {
            Observable::fromArray( [ 5, 6, 7, 8, 9 ] )->groupBy( function( $el ) {
                $this->push( 'key analyzing => ' . json_encode( $el ) );

                return $el < 7 ? 'smaller than 7' : ( $el > 7 ? 'greater than 7' : 'equal to 7' );
            }, function( $item ) {
                $this->push( 'pass to observer => ' . json_encode( $item ) );

                return $item;
            }, null )->subscribe( function( Observable $observable ) {
                $observable->subscribe( function( $item ) use ( $observable ) {
                    $this->push( 'observer => key : ' . $observable->getKey() . ' value => ' . json_encode( $item ) );
                } );
            } );
        }

        private function forkJoin() {
            $source1 = Observable::range( 1, 5 );
            $source2 = Observable::of( 20 );
            $source3 = Observable::fromArray( [ 'first', 'second', 'last' ] );

            Observable::forkJoin( [ $source1, $source2, $source3 ], function( $s1, $s2, $s3 ) {
                return [ $s1, $s2, $s3 ];
            } )->subscribe( function( $items ) {
                $this->push( "observer => source 1 : {$items[0]}, source 2 : {$items[1]}, source 3 : {$items[2]}" );
            } );
        }

        private function flatMapTo() {
            Observable::range( 1, 3 )->flatMapTo( Observable::range( 0, 2 ) )->subscribe( function( $item ) {
                $this->push( 'observer => ' . $item );
            } );
        }

        public function flatMap() {
            Observable::range( 0, 4 )->flatMap( function( $item ) {
                $this->push( 'received from observable in flatMap => ' . $item );

                return Observable::range( $item + 1, 2 );
            } )->do( function( $item ) {
                $this->push( 'observable\'s onNext => ' . $item );
            } )->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            } );
        }

        private function finally() {
            Observable::range( 0, 3 )->finally( function() {
                $this->push( 'finally operator executed.' );
            } )->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            }, null, function() {
                $this->push( 'observer onCompleted' );
            } );
        }

        private function filter() {
            Observable::range( 0, 20 )->filter( function( $item ) {
                $this->push( 'filter Operator => ' . $item );

                return $item < 15;
            } )->subscribe( function( $item ) {
                $this->push( 'observer #1 => ' . $item );
            } );
        }

        private function dictincUntilKeyChanged() {
            Observable::fromArray( [
                [ 'id' => 1 ],
                [ 'id' => 2 ],
                [ 'id' => 2 ],
                [ 'id' => 3 ],
                [ 'id' => 3 ],
                [ 'id' => 4 ],
                [ 'id' => 3 ],
            ] )->distinctUntilKeyChanged( function( $item ) {
                $this->push( 'distinctUntilKeyChanged Operator => ' . json_encode( $item ) );

                return $item[ 'id' ];
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item[ 'id' ] );
            } );
        }

        private function distincUntilChange() {
            Observable::fromArray( [ 1, 2, 2, 3, 4, 3 ] )->distinctUntilChanged()->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function distincKey() {
            Observable::fromArray( [
                [ 'id' => 1 ],
                [ 'id' => 2 ],
                [ 'id' => 2 ],
                [ 'id' => 3 ],
            ] )->distinctKey( function( $item ) {
                $this->push( "distincKey Operator => " . json_encode( $item ) );

                return $item[ 'id' ];
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item[ 'id' ] );
            } );
        }

        private function distinc() {
            Observable::fromArray( [ 1, 2, 2, 3 ] )->distinct()->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function doOnCompleted() {
            Observable::fromArray( [ 0, 1, 2, 3 ] )->doOnCompleted( function() {
                $this->push( 'in DO operator: onCompleted' );
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function do() {
            Observable::fromArray( [ 0, 1, 2, 3, [ 4 ] ] )->do( function( $item ) {
                $this->push( 'DO operator: onNext => ' . $item );
            }, function() {
                $this->push( 'DO operator: OnError' );

                return Observable::error( new \Exception( 'observable must contains one-level array' ) );
            }, function() {
                $this->push( 'DO operator: onCompleted' );
            } )->catch( function( \Exception $error ) {
                $this->push( 'received in CATCH operator => ' . $error->getMessage() );
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function delay() {
            $loop = Factory::create();
            $loop->addTimer( 10, function() use ( $loop ) {
                $this->push( 'async Loop stopped.' );
                $loop->stop();
            } );
            Scheduler::setDefaultFactory( function() use ( $loop ) {
                return new Scheduler\EventLoopScheduler( $loop );
            } );
            Observable::interval( 1000 )->do( function( $item ) {
                $this->push( 'DO operator => ' . $item );
            } )->delay( 1500 )->subscribe( function( $item ) {
                $this->push( 'OBSERVER => ' . $item );
            } );
            $loop->run();
        }

        private function defer() {
            $observable = Observable::defer( function() {
                return Observable::range( 0, rand( 3, 10 ) );
            } );

            $observable->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
            $observable->subscribe( function( $item ) {
                $this->push( 'OBSERVER #2 => ' . $item );
            } );
        }

        private function defaultEmpty() {
            Observable::range( 6, 5 )
                      ->take( 0 )
                      ->defaultIfEmpty( Observable::range( 1, 5 ) )
                      ->subscribe( function( $item ) {
                          $this->push( 'OBSERVER #1 => ' . $item );
                      } );
        }

        private function create() {
            Observable::create( function( ObserverInterface $observer ) {
                $observer->onNext( 1 );
                $observer->onNext( 2 );
                $observer->onNext( 3 );
                $observer->onCompleted();

                return new CallbackDisposable( function() {
                    $this->push( 'CallbackDisposable' );
                } );
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            }, null, function() {
                $this->push( 'OnCompleted OBSERVER #1' );
            } );
        }

        private function count() {
            Observable::range( 1, 10 )->take( 5 )->count()->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function concatMapTo() {
            $loop = Factory::create();
            $loop->addPeriodicTimer( 5, function() use ( $loop ) {
                $this->push( "[" . microtime( true ) . "] Timer's Tick" );
                $loop->stop();
            } );
            $async = new Scheduler\EventLoopScheduler( $loop );
            $obs   = Observable::interval( 300, $async )->take( 3 )->mapWithIndex( function( $item, $index ) {
                $this->push( "mapWithIndex (value:index) => {$item} : {$index}" );

                return $item;
            } );

            Observable::range( 2, 3 )->do( function( $item ) {
                $this->push( 'observable\'s onNex => ' . $item );
            } )->concatMapTo( $obs )->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );

            $loop->run();
        }

        private function concatMap() {
            $loop = Factory::create();
            $loop->addPeriodicTimer( 10, function() use ( $loop ) {
                $loop->stop();
                $this->push( "Timer's Tick" );
            } );
            Observable::range( 5, 6 )->concatMap( function( $value, $index ) use ( $loop ) {
                $async = new Scheduler\EventLoopScheduler( $loop );
                $this->push( "received from Observable (index:value) => {$index}:{$value}" );

                return Observable::interval( 900, $async )->take( $value )->map( function() use ( $value ) {
                    return $value;
                } );
            } )->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );
            $loop->run();
        }

        private function concatAll() {
            Observable::range( 0, 3 )->map( function( $item ) {
                $this->push( "MAP => {$item}" );

                return Observable::range( $item, 3 );
            } )->concatAll()->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );
        }

        private function concat() {
            $source1 = Observable::range( 1, 10 );
            $source2 = Observable::of( 15 );

            $observable = Observable::empty()->concat( $source1 )->concat( $source2 );

            $observable->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 start => {$item}" );
                usleep( rand( 5, 15 ) * 100000 );
                $this->push( "OBSERVER #1 done => {$item}" );
            } );
        }

        private function compose() {
            $process    = function( $observable ) {
                return $observable->map( function( $item ) {
                    $this->push( "process closure and MAP => {$item}" );

                    return $item;
                } );
            };
            $observable = Observable::range( 1, 100 )->take( 15 )->do( function( $item ) {
                $this->push( "returned from Take => {$item}" );
            } )->compose( $process );

            $observable->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );
        }

        private function addReadStream() {
            define( 'STDIN', fopen( "php://stdin", "r" ) );
            $loop = Factory::create();
            $file = fopen( realpath( __DIR__ . "/../../../resources/views/home.blade.php" ), 'r' );
            $loop->addReadStream( $file, function( $stream ) use ( $loop ) {
                $chunk = fread( $stream, 100 );

                if ( $chunk == '' ) {
                    $loop->removeReadStream( $stream );
                    stream_set_blocking( $stream, true );
                    fclose( $stream );

                    return;
                }
                $this->push( "" . strlen( $chunk ) . " bytes" );
            } );
            $loop->run();
        }

        private function combineLatest() {
            $loop = Factory::create();
            $loop->addTimer( 9, function() use ( $loop ) {
                $loop->stop();
                $this->push( "Loop Event : Tick!" );
            } );
            $asyncScheduler = new Scheduler\EventLoopScheduler( $loop );
            $obs1           = Observable::interval( 1000, $asyncScheduler );
            $obs2           = Observable::interval( 3000, $asyncScheduler );

            $source = $obs1->combineLatest( [ $obs2 ], function( $item1, $item2 ) {
                $this->push( "combineLatest => {$item1} - {$item2}" );

                return json_encode( [ $item1, $item2 ] );
            } )->take( 6 );

            $source->subscribe( function( $item ) use ( $loop ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );
            $this->push( "started ..." );
            $loop->run();
        }

        protected function catch() {
            Observable::fromArray( [ 0, 1, 2, 3, [ 4 ] ] )->doOnError( function() {
                $this->push( 'in DO operator: OnError' );

                return Observable::error( new \Exception( 'observable must contains one-level array' ) );
            } )->catch( function( \Exception $error ) {
                $this->push( 'CATCH operator => ' . $error->getMessage() );
            } )->subscribe( function( $item ) {
                $this->push( 'OBSERVER #1 => ' . $item );
            } );
        }

        private function bufferWithCount() {
            $observable = Observable::range( 1, 100 )
                                    ->bufferWithCount( 10 ); // getting things ready before passing to the observer

            $observable->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => " . json_encode( $item ) );
            } );
        }

        private function range() {
            $observable = Observable::range( 1, 10 )->map( function( $item ) {
                $this->push( "MAP => {$item}" );

                return $item;
            } )->average();

            $observable->subscribe( function( $item ) {
                $this->push( "OBSERVER #1 => {$item}" );
            } );
        }

        private function connectObservable() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4, 6, 7, 8, 9 ] )
                                    ->map( function( $item ) use ( &$output ) {
                                        $this->push( "MAP Operator {$item}" );

                                        return $item += 1;
                                    } )
                                    ->filter( function( $item ) use ( &$output ) {
                                        $this->push( "FILTER Operator {$item}" );

                                        return $item % 2 == 0;
                                    } );

            $observer = new ConnectableObservable( $observable );

            $observer->subscribe( function( $item ) use ( &$output ) {
                $this->push( "OBSERVER #2 {$item}" );
            }, null, function() use ( &$output ) {
                $this->push( "OBSERVER #2 completed." );
            } );

            $observer->connect();
        }

        private function twoObserverSubscribeAObservable() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4, 6, 7, 8, 9 ] )
                                    ->map( function( $item ) use ( &$output ) {
                                        $this->push( "receive at MAP Operator {$item}" );

                                        return $item += 1;
                                    } )
                                    ->filter( function( $item ) use ( &$output ) {
                                        $this->push( "receive at FILTER Operator {$item}" );

                                        return $item % 2 == 0;
                                    } );
            $observable->subscribe( function( $item ) use ( &$output ) {
                $this->push( "OBSERVER #1 {$item}" );
            }, null, function() use ( &$output ) {
                $this->push( "OBSERVER #1 completed." );
            } );

            $observable->subscribe( function( $item ) use ( &$output ) {
                $this->push( "OBSERVER #2 {$item}" );
            }, null, function() use ( &$output ) {
                $this->push( "OBSERVER #2 completed." );
            } );
        }
    }
