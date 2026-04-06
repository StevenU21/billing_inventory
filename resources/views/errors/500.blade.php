@extends('layouts.app')
@section('title', 'Server Error')

@section('content')
    <div class="container flex flex-col items-center px-6 mx-auto">
        <svg class="w-12 h-12 mt-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M18 10c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm-8-3a1 1 0 00-1 1v2a1 1 0 002 0V8a1 1 0 00-1-1zm0 6a1 1 0 100 2 1 1 0 000-2z"
                clip-rule="evenodd"></path>
        </svg>
        <h1 class="text-6xl font-semibold text-gray-700 dark:text-gray-200">
            500
        </h1>
        <p class="text-gray-700 dark:text-gray-300 text-center">
            Oops! Something went wrong on our end. Please try again later or
            <a class="text-purple-600 hover:underline dark:text-purple-300" href="{{ url()->previous() }}">
                go back
            </a>
            .
        </p>
    </div>
@endsection
