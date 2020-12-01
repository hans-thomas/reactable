<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## About Reactable

Reactable is a simple and easy to use project that lets you learning Reactive X faster.
this project's back-end implemented in laravel using Events and Broadcasts and also laravel-websockets package.
in front-end we used Vue js with axios for sending request and getting server response and show it using vue components.

- RxPHP commands with meaningful examples
- simple and fast
- SPA

## How to run
- first clone the repository and open a terminal in the root directory
- second enter ```composer install```
- third ```npm i```
- then ```cp .env.example .env```
- next ```php artisan key:generate```
- and also ```php artisan websockets:serve```
- in the end  ```php artisan serve``` for serving the project

## How to use
- in ```HomeController.php``` we have `run` method, anytime UPDATE button clicked in Home page this method execute.
we also define commands in the separate private methods and to executing a command, just need called in `run` method.
- for defining a new command, just need to create a  new private method and call it in `run` method.
- to show up a message in the home page just need to call `push` method and write what you want.
