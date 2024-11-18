@extends('layouts.errors', [
    'title' => __('Not Found!'),
    'errorCode' => '404',
    'message' => __('Where did you try to go? Couldn`t find the requested Page. Quickly, lets go back'),
    'homeLink' => true,
])
