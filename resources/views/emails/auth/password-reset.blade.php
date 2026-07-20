@component('mail::message')
    # Reset your password

    We received a request to reset your Pure Wear account password. Click the button below to choose a new one.

    @component('mail::button', ['url' => $resetUrl])
        Reset Password
    @endcomponent

    This link will expire in 60 minutes. If you didn't request this, you can safely ignore this email — your password won't
    change.

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
