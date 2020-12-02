<?php

    namespace Reactable\Examples;


    use Reactable\Utilities\Observer;
    use Reactable\Utilities\Output;
    use Rx\Observable;
    use Rx\Observer\CallbackObserver;

    class Operators {
        public static function combineLatest() {
            $obs1 = Observable::interval( 1000 );
            $obs2 = Observable::interval( 3000 );

            $source = $obs1->combineLatest( [ $obs2 ], function( $item1, $item2 ) {
                Output::send( "combineLatest => {$item1} - {$item2}" );

                return json_encode( [ $item1, $item2 ] );
            } )->take( 11 );

            $source->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
            Output::send( "started ..." );
        }

        public static function catch() {

            Observable::fromArray( [ 0, 1, 2, 3, [ 4 ] ] )->map( function( $item ) {
                return '(string) ' . $item;
            } )->catch( function( \Exception $error ) {
                Output::send( 'CATCH => ' . $error->getMessage() );
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function bufferWithCount() {
            $observable = Observable::range( 1, 100 )
                                    ->bufferWithCount( 10 ); // getting things ready before passing to the observer

            $observable->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => " . json_encode( $item ) );
            } );
        }

        public static function range() {
            $observable = Observable::range( 1, 10 )->do( function( $item ) {
                Output::send( "onNext => {$item}" );
            } )->average();

            $observable->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function count() {
            Observable::range( 1, 10 )->take( 5 )->count()->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function concatMapTo() {
            $observable = Observable::interval( 300 )->take( 3 )->mapWithIndex( function( $item, $index ) {
                Output::send( "mapWithIndex (value:index) => {$item} : {$index}" );

                return $item;
            } );

            Observable::range( 2, 3 )->do( function( $item ) {
                Output::send( 'observable\'s onNex => ' . $item );
            } )->concatMapTo( $observable )->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function concatMap() {
            Observable::range( 5, 6 )->concatMap( function( $value, $index ) {
                Output::send( "received from Observable (index:value) => {$index}:{$value}" );

                return Observable::interval( 900 )->take( $value )->map( function() use ( $value ) {
                    return $value;
                } );
            } )->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function concatAll() {
            Observable::range( 0, 3 )->map( function( $item ) {
                Output::send( "MAP => {$item}" );

                return Observable::range( $item, 3 );
            } )->concatAll()->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function concat() {
            $source1 = Observable::range( 1, 10 );
            $source2 = Observable::of( 15 );

            $observable = Observable::empty()->concat( $source1 )->concat( $source2 );

            $observable->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function compose() {
            $process    = function( $observable ) {
                return $observable->map( function( $item ) {
                    Output::send( "process closure => {$item}" );

                    return $item;
                } );
            };
            $observable = Observable::range( 1, 100 )->take( 15 )->do( function( $item ) {
                Output::send( "returned from Take => {$item}" );
            } )->compose( $process );

            $observable->subscribe( function( $item ) {
                Output::send( "OBSERVER #1 => {$item}" );
            } );
        }

        public static function flatMapTo() {
            Observable::range( 1, 3 )->flatMapTo( Observable::range( 0, 2 ) )->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public function flatMap() {
            Observable::range( 0, 4 )->flatMap( function( $item ) {
                Output::send( 'received from observable in flatMap => ' . $item );

                return Observable::range( $item + 1, 2 );
            } )->do( function( $item ) {
                Output::send( 'observable\'s onNext => ' . $item );
            } )->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            } );
        }

        public static function finally() {
            Observable::range( 0, 3 )->finally( function() {
                Output::send( 'finally operator executed.' );
            } )->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            }, null, function() {
                Output::send( 'observer onCompleted' );
            } );
        }

        public static function filter() {
            Observable::range( 0, 20 )->filter( function( $item ) {
                Output::send( 'filter Operator => ' . $item );

                return $item < 15;
            } )->subscribe( function( $item ) {
                Output::send( 'observer #1 => ' . $item );
            } );
        }

        public static function dictincUntilKeyChanged() {
            Observable::fromArray( [
                [ 'id' => 1 ],
                [ 'id' => 2 ],
                [ 'id' => 2 ],
                [ 'id' => 3 ],
                [ 'id' => 3 ],
                [ 'id' => 4 ],
                [ 'id' => 3 ],
            ] )->distinctUntilKeyChanged( function( $item ) {
                Output::send( 'distinctUntilKeyChanged Operator => ' . json_encode( $item ) );

                return $item[ 'id' ];
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item[ 'id' ] );
            } );
        }

        public static function distincUntilChange() {
            Observable::fromArray( [ 1, 2, 2, 3, 3, 4, 3 ] )->distinctUntilChanged()->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function distincKey() {
            Observable::fromArray( [
                [ 'id' => 1 ],
                [ 'id' => 2 ],
                [ 'id' => 2 ],
                [ 'id' => 3 ],
            ] )->distinctKey( function( $item ) {
                Output::send( "distincKey => " . json_encode( $item ) );

                return $item[ 'id' ];
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item[ 'id' ] );
            } );
        }

        public static function distinc() {
            Observable::fromArray( [ 1, 2, 2, 3 ] )->distinct()->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function doOnCompleted() {
            Observable::fromArray( [ 0, 1, 2, 3 ] )->doOnCompleted( function() {
                Output::send( 'in DO operator: onCompleted' );
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function do() {
            Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->map( function( $item ) {
                return $item;
            } )->do( function( $item ) {
                Output::send( 'DO operator: onNext => ' . $item );
            }, function() {
                Output::send( 'DO operator: OnError' );
            }, function() {
                Output::send( 'DO operator: onCompleted' );
            } )->subscribe( function( $item ) {
                Output::send( 'OBSERVER #1 => ' . $item );
            } );
        }

        public static function delay() {
            Observable::interval( 1000 )->do( function( $item ) {
                Output::send( 'DO operator => ' . $item );
            } )->delay( 1500 )->subscribe( function( $item ) {
                Output::send( 'OBSERVER => ' . $item );
            } );
        }

        public static function groupBy() {
            Observable::fromArray( [ 5, 6, 7, 8, 9 ] )->groupBy( function( $el ) {
                Output::send( 'key analyzing => ' . $el );

                return $el < 7 ? 'smaller than 7' : ( $el > 7 ? 'greater than 7' : 'equal to 7' );
            }, function( $item ) {
                Output::send( 'pass to observer => ' . $item );

                return $item;
            }, null )->subscribe( function( Observable $observable ) {
                $observable->subscribe( function( $item ) use ( $observable ) {
                    Output::send( 'observer => key : ' . $observable->getKey() . ' value => ' . $item );
                } );
            } );
        }

        public static function forkJoin() {
            $source1 = Observable::range( 1, 5 );
            $source2 = Observable::of( 20 );
            $source3 = Observable::fromArray( [ 'first', 'second', 'last' ] );

            Observable::forkJoin( [ $source1, $source2, $source3 ], function( $s1, $s2, $s3 ) {
                return [ $s1, $s2, $s3 ];
            } )->subscribe( function( $items ) {
                Output::send( "observer => source 1 : {$items[0]}, source 2 : {$items[1]}, source 3 : {$items[2]}" );
            } );
        }

        public static function mergeAll() {
            $source = Observable::range( 0, 6 )->map( function( $item ) {
                return Observable::of( $item )->repeat( $item < 0 ? 1 : $item );
            } );

            $observable = $source->mergeAll();
            $observable->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function merge() {
            $observable      = Observable::of( 1 )->repeat( 10 );
            $otherObservable = Observable::of( 2 )->repeat( 10 );

            $mergedObservable = $observable->merge( $otherObservable );

            $mergedObservable->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function min() {
            // we can define a comparer same as maxWithComparer
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->min();

            $observable->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function maxWithComparer() {
            $comparer   = function( $x, $y ) {
                return $x > $y ? 1 : ( $x < $y ? - 1 : 0 );
            };
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->max( $comparer );

            $observable->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function max() {
            $observable = Observable::fromArray( [ 0, 1, 2, 3, 4 ] )->max();

            $observable->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function pluck() {
            Observable::fromArray( [
                [ 'id' => 1, 'name' => 'Hans', 'zip' => 65825 ],
                [ 'id' => 2, 'name' => 'ashley', 'zip' => 78945 ],
                [ 'id' => 3, 'name' => 'alicia', 'zip' => 12458 ],
            ] )->pluck( 'name' )->subscribe( function( $item ) {
                Output::send( 'observer => ' . $item );
            } );
        }

        public static function partition() {
            list( $evens, $odds ) = Observable::range( 0, 6 )->partition( function( $item ) {
                return $item % 2 === 0;
            } );
            $evens->subscribe( function( $item ) {
                Output::send( 'even value => ' . $item );
            } );
            $odds->subscribe( function( $item ) {
                Output::send( 'odd value => ' . $item );
            } );
        }

        public static function race() {
            $observable = Observable::race( [
                Observable::timer( 200 )->map( function( $item ) {
                    return 'observable #1 with 200ms delay => ' . $item;
                } )->repeat( 10 ),
                Observable::timer( 100 )->map( function( $item ) {
                    return 'observable #2 with 100ms delay => ' . $item;
                } )->repeat( 5 )
            ] );
            $observable->subscribe( Observer::get( 1 ) );
            $observable->subscribe( Observer::get( 2 ) );
        }

        public static function reduce() {
            $observable = Observable::fromArray( [ 1, 2, 3 ] );
            $observable->reduce( function( $acc, $x ) {
                Output::send( 'reduce => acc : ' . $acc . ' + x : ' . $x . ' = ' . ( $acc + $x ) );

                return $acc + $x;
            } )->subscribe( Observer::get() );
        }

        public static function reduceWithSeed() {
            // act same as reduce but send 5 before array elements
            $observable = Observable::fromArray( [ 1, 2, 3 ] );
            $observable->reduce( function( $acc, $x ) {
                Output::send( 'reduce => acc : ' . $acc . ' + x : ' . $x . ' = ' . ( $acc + $x ) );

                return $acc + $x;
            }, 5 )->subscribe( Observer::get() );
        }

        public static function reply() {
            $observable = Observable::range( 300, 5 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } );

            $published = $observable->replay( function( Observable $x ) {
                return $x->take( 2 )->repeat( 3 );
            } );

            $published->subscribe( Observer::get( 1 ) );
            $published->subscribe( Observer::get( 2 ) );
        }

        public static function retry() {
            $counter    = 0;
            $observable = Observable::interval( 300 )->flatMap( function( $item ) use ( &$counter ) {
                $counter ++;
                if ( $counter < 2 ) {
                    return Observable::error( new \Exception( 'too many errors!' ) );
                }

                if ( $counter == 6 ) {
                    return Observable::error( new \Exception( 'too many errors!' ) );
                }

                return Observable::of( $item );
            } )->retry( 2 )->take( 10 );

            $observable->subscribe( Observer::get() );
        }

        public static function retryWhen() {
            $flag       = true;
            $observable = Observable::interval( 1000 )->map( function( $item ) use ( &$flag ) {
                if ( $item == 3 and $flag ) {
                    $flag = false;
                    throw new \Exception( 'item equal to 3' );
                }

                return $item;
            } )->retryWhen( function( Observable $errored ) {
                return $errored->delay( 2000 );
            } )->take( 10 );

            $observable->subscribe( Observer::get() );
        }

        public static function scan() {
            $observable = Observable::range( 1, 5 )->scan( function( $acc, $x ) {
                Output::send( 'reduce => acc : ' . $acc . ' + x : ' . $x . ' = ' . ( $acc + $x ) );

                return $acc + $x;
            } );

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

        public static function skip() {
            Observable::fromArray( [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] )->skip( 2 )->subscribe( Observer::get() );
        }

        public static function skipLast() {
            Observable::fromArray( [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] )->skipLast( 2 )->subscribe( Observer::get() );
        }

        public static function skipUntil() {
            $observable = Observable::interval( 1000 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } )->skipUntil( Observable::timer( 5000 ) )->take( 3 );

            $observable->subscribe( Observer::get() );
        }

        public static function skipWhile() {
            $observable = Observable::interval( 1000 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } )->skipWhile( function( $item ) {
                return $item < 3;
            } )->take( 6 );

            $observable->subscribe( Observer::get() );
        }

        public static function skipWhileWithIndex() {
            $observable = Observable::interval( 1000 )->do( function( $item ) {
                Output::send( 'onNext => ' . $item );
            } )->skipWhileWithIndex( function( $index, $value ) {
                return $index < 3;
            } )->take( 6 );

            $observable->subscribe( Observer::get() );
        }

    }
