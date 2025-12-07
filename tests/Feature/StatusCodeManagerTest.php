<?php

namespace Tests\Http;
use JDS\Http\OldStatusCodeManager;
use ReflectionClass;

it('returns success message for status code 0', function () {
   $manager = new OldStatusCodeManager();
   $message = $manager::getMessage(0);

   expect($message)->toBe('[0] Success');

});

it('returns a general error message for status code 1', function () {
   $manager = new OldStatusCodeManager();
   $message = $manager::getMessage(1);

   expect($message)->toBe('[1] General Error');
});

it('returns a custom unknown status code message if status code does not exist', function () {
   $manager = new OldStatusCodeManager();
   $message = $manager::getMessage(999);

   expect($message)->toBe('[999] Unknown Status Code');
});

it('returns unknown error message when not status code is provided', function () {
   $manager = new OldStatusCodeManager();
   $message = $manager::getMessage();

   expect($message)->toBe('Unknown Error! No Status Code Provided.');

});




