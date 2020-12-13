# Reactable

## About Reactable

Reactable is a simple and easy to use project that lets you learning Reactive X faster.
this project's back-end implemented in laravel using Events and Broadcasts and also laravel-websockets package.
in front-end we used Vue js with axios for sending request and getting server response and show it using vue components.

- RxPHP commands with meaningful examples
- simple and fast
- SPA
- Progressive

## How to run
- first clone the repository and open a terminal in the root directory
- second enter ```composer install```
- third ```npm i```
- then ```cp .env.example .env```
- next ```php artisan key:generate```
- and also ```php artisan websockets:serve```
- in the end  ```php artisan serve``` for serving the project

## How to use
- to executing a method just need to type `run` before the method's name. for example if we have `foo` method
just change the name to `runfoo`.
- for showing up a message in result page, you can use `Output` class and `send` method.
- examples defined in three types (Loops, Observables and Operators). you can call each method statically.
- all the examples placed in `reactable/Examples` directory. feel free to insert a new method or manipulate methods.
