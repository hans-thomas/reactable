<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Reactable\Examples\Loops;
    use Reactable\Examples\Observables;
    use Reactable\Examples\Operators;

    class CommandsController extends Controller {

        public static function exe() {
            Operators::skipWhileWithIndex();
        }
    }
