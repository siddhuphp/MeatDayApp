@extends('emails.layout')
@section('subject','Welcome to PressHub')
@section('content')
<p style="margin: 0 0 16px 0;">
    Dear {{ $data['username'] }},<br><br>
    Your email address has been verified. Welcome aboard! You can now access your full account by signing in with your username and password
    <a href="{{ $data['loginLink'] }}" style="background-color: #0d6efd; color: #FFF; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; display: block; margin: 20px auto; border-radius: 5px; text-decoration: none; text-align: center; width: fit-content;">Login</a>
</p>
<p style="margin: 0 0 16px 0;">If the above link doesn't work, please copy and paste the following URL into your web browser: {{ $data['loginLink'] }} </p>
@endsection