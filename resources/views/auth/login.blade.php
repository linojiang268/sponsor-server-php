@extends('layouts.master')

@section('title', 'Login')

@section('content')
    <form action="/auth/login" method="post">
        Name: <input type="text" name="email" placeholder="email" /> <br/>
        Password: <input type="password" name="password" placeholder="Password" /> <br/>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button>Login</button>
    </form>
@endsection