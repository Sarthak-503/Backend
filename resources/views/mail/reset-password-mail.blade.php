@php
    $url = 'http://localhost:8000/api/verify-token?token=' . $token;
@endphp

<x-mail::message>
    # Request for Password Reset
Dear User,<br>
You are receiving this email because we received a password reset request for your account.
Click on the Button below to change the password.
<x-mail::button :url="$url">
Reset Password
</x-mail::button>

If you did not send the request, no further action is required.

Thanks & Regards,<br>
{{ config('app.name') }}
</x-mail::message>
