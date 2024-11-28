@extends('layouts.errors', [
    'title' => __('Too many Requests!'),
    'errorCode' => '429',
    'message' => __('Hey slow down a bit. Youre going too fast. Try again in a few Minutes'),
    'homeLink' => true,
])
