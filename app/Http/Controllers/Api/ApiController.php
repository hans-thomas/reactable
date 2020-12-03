<?php

    namespace App\Http\Controllers\Api;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use React\EventLoop\Factory;
    use Rx\Scheduler;

    class ApiController extends Controller {
        public function run() {
            $execute = Collection::make();
            $classes = config( 'reactable.classes' );

            foreach ( $classes as $class ) {
                foreach ( get_class_methods( $class ) as $method ) {
                    if ( substr( $method, 0, 3 ) == 'run' ) {
                        $execute->push( [
                            'class'  => $class,
                            'method' => $method
                        ] );
                    }
                }
            }
            $loop = self::init();
            try {
                if ( $execute->isEmpty() ) {
                    throw new \Exception();
                }
                foreach ( $execute->reverse() as $item ) {
                    call_user_func_array( [ $item[ 'class' ], $item[ 'method' ] ], [] );
                    if (! config( 'reactable.multiple' ) ) {
                        break;
                    }
                }
            } catch ( \Throwable $e ) {
                throw new \Exception( 'please mark a method to run' );
            }
            $loop->run();
        }

        /**
         * @return \React\EventLoop\LoopInterface
         * @throws \Exception
         */
        private static function init() {
            $loop = Factory::create();
            Scheduler::setDefaultFactory( function() use ( $loop ) {
                return new Scheduler\EventLoopScheduler( $loop );
            } );
            register_shutdown_function( function() use ( $loop ) {
                $loop->stop();
            } );

            return $loop;
        }
    }
