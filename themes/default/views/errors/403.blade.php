@extends('layouts.errors', [
    'title' => __('No Permission!'),
    'errorCode' => '403',
    'message' => __('Oh no, you do not seem to have the correct Permission to view this Page. If you think this is a mistake, please contact the Administrator'),
    'homeLink' => true,
])
