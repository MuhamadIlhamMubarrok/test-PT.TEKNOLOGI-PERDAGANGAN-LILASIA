<!-- resources/views/auth/login.blade.php -->
@extends('layout')

@section('content')
    <div class="w-full max-w-sm p-6 m-auto bg-white rounded-md shadow-md">
        <h1 class="text-3xl font-semibold text-center text-gray-700">Login</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mt-4">
                <label class="block text-sm">Email</label>
                <input type="email" name="email"
                    class="w-full px-4 py-2 mt-2 text-gray-700 bg-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="mt-4">
                <label class="block text-sm">Password</label>
                <input type="password" name="password"
                    class="w-full px-4 py-2 mt-2 text-gray-700 bg-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="mt-6">
                <button type="submit"
                    class="w-full px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none">Login</button>
            </div>
        </form>
        <p class="mt-8 text-xs text-center text-gray-700">Belum punya akun? <a href="{{ route('register') }}"
                class="text-blue-500 hover:underline">Register</a></p>
    </div>
@endsection
