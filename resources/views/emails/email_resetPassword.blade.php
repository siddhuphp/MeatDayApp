@extends('emails.layout')
@section('subject','Reset Your Password')
@section('content')
<p style="margin: 0 0 16px 0;">
    Dear {{ $data['username'] }},<br><br>
    We've received a request to reset your password. If you didn't make the request, just ignore this email. Otherwise, you can reset your password using this link:
    <a href="{{ $data['resetLink'] }}" style="background-color: #0d6efd; color: #FFF; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; display: block; margin: 20px auto; border-radius: 5px; text-decoration: none; text-align: center; width: fit-content;">Login</a>
</p>
<p style="margin: 0 0 16px 0;">If the above link doesn't work, please copy and paste the following URL into your web browser: {{ $data['resetLink'] }} </p>
@endsection