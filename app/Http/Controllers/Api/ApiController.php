<?php

    namespace App\Http\Controllers\Api;

    use App\Events\OutputEvent;
    use App\Http\Controllers\CommandsController;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use React\EventLoop\Factory;
    use Reactable\Examples\Loops;
    use Reactable\Examples\Observables;
    use Reactable\Examples\Operators;
    use Rx\Scheduler;

    class ApiController extends Controller {
        public function run() {
            $loop = self::init();
            CommandsController::exe();
            $loop->run();
        }

        /**
         * @return \React\EventLoop\LoopInterface
         * @throws \Exception
         */
        private static function init() {
            $loop = Factory::create();
            /*$loop->addTimer( 5, function() use ( $loop ) {
                $this->push( 'async timer TICKED' );
                $loop->stop();
            } );*/
            Scheduler::setDefaultFactory( function() use ( $loop ) {
                return new Scheduler\EventLoopScheduler( $loop );
            } );
            register_shutdown_function( function() use ( $loop ) {
                $loop->stop();
            } );

            return $loop;
        }
    }
