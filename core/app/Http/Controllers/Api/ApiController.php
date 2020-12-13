<?php

    namespace App\Http\Controllers\Api;

    use App\Exceptions\NoMethodSelectedException;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use React\EventLoop\Factory;
    use Reactable\Utilities\Output;
    use Rx\Scheduler;

    class ApiController extends Controller {
        /**
         * @throws \Exception
         */
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
                    throw new NoMethodSelectedException( 'please mark a method to run!' );
                }
                foreach ( $execute->reverse() as $item ) {
                    if ( config( 'reactable.multiple' ) ) {
                        Output::send( substr( $item[ 'method' ], 3, strlen( $item[ 'method' ] ) ) . '() started. -----------' );
                    }
                    call_user_func_array( [ $item[ 'class' ], $item[ 'method' ] ], [] );
                    if ( ! config( 'reactable.multiple' ) ) {
                        break;
                    }
                }
            } catch ( NoMethodSelectedException $e ) {
                throw new \Exception( $e->getMessage() );
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
