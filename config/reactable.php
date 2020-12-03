<?php

    use Reactable\Examples\Loops;
    use Reactable\Examples\Observables;
    use Reactable\Examples\Operators;

    return [
        /*
        |--------------------------------------------------------------------------
        | Class registration
        |--------------------------------------------------------------------------
        | register your classes there to scanned
        |  ar run time
        |
        */
        'classes' => [
            Loops::class,
            Observables::class,
            Operators::class,
        ],
        /*
        |--------------------------------------------------------------------------
        | Runable classes
        |--------------------------------------------------------------------------
        | if you want to run multiple methods, set multiple key true.
        | by default it is false and if we have two marked methods,
        | that method will run witch is in lowest position.
        */
        'multiple' => true
    ];
