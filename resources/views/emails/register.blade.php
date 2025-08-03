@extends('emails.layout')
@section('subject','Welcome to PressHub')
@section('content')
<p style="margin: 0 0 16px 0;">
    Dear {{ $data['username'] }},<br><br>
    Thank you for registering with us. To complete your registration, please verify your email address by clicking
    <a href="{{ $data['verification_link'] }}" style="background-color: #0d6efd; color: #FFF; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; display: block; margin: 20px auto; border-radius: 5px; text-decoration: none; text-align: center; width: fit-content;">Verify Email</a>
    <br>
</p>
@endsection