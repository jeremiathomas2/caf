<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/seasons', 'pages.seasons')->name('seasons');
Route::view('/groups', 'pages.groups')->name('groups');
Route::view('/programme', 'pages.programme')->name('programme');
Route::view('/impact', 'pages.impact')->name('impact');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/register', 'pages.register')->name('register');
Route::view('/terms', 'pages.terms')->name('terms');
