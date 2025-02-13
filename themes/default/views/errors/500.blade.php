@extends('layouts.errors', [
    'title' => __('General Error!'),
    'errorCode' => '500',
    'message' => __('Something went wrong! If this error persists, please report to the Staffteam'),
    'homeLink' => true,
])
