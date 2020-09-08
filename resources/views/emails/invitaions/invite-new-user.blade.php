@component('mail::message')
# Hi

You have been invited to join the team **{{ $invitation->team->name }}**

Because you are not signed up for the platform please [register]({{ $url }}) , then you can accept or reject the invitation

@component('mail::button', ['url' => $url])
Register
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
