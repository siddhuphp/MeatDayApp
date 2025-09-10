@extends('emails.layout')
@section('subject','Welcome to Meatday.shop')
@section('content')
<p style="margin:0 0 16px 0; font-family:Arial, sans-serif; font-size:14px; color:#1a1a1a;">
    Hi {{ $data['username'] ?? 'there' }},
</p>
<p style="margin:0 0 16px 0; font-family:Arial, sans-serif; font-size:14px; color:#1a1a1a;">
    Welcome to <strong>Meatday.shop</strong> — your trusted source for fresh, hand‑cut meats delivered fast.
</p>
<p style="margin:0 0 16px 0; font-family:Arial, sans-serif; font-size:14px; color:#1a1a1a;">
    To secure your account and unlock exclusive member benefits, please confirm your email address.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; margin:0 0 16px 0;">
    <tr>
        <td align="center" bgcolor="#c62828" style="border-radius:8px;">
            <a href="{{ $data['verification_link'] }}" style="display:inline-block; padding:12px 22px; font-family:Arial, sans-serif; font-size:16px; color:#ffffff; text-decoration:none; border-radius:8px; background:#c62828;">Verify your email</a>
        </td>
    </tr>
</table>
<p style="margin:0; font-family:Arial, sans-serif; font-size:12px; color:#6b7280;">
    If you didn’t create this account, you can safely ignore this email.
</p>
@endsection