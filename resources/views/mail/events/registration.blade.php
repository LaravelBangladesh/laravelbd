<x-mail::message>
# {{ __($key.'.heading') }}

{{ __($key.'.intro', ['name' => $name, 'event' => $title]) }}

<x-mail::detail :label="__('events.mail.event')">{{ $title }}</x-mail::detail>

<x-mail::detail :label="__('events.mail.when')">{{ $when }} ({{ __('events.mail.timezone') }})</x-mail::detail>

@if ($venueName)
<x-mail::detail :label="__('events.venue')">{{ $venueName }}@if ($venueAddress), {{ $venueAddress }}@endif @if ($venueMapUrl)<br><a href="{{ $venueMapUrl }}">{{ __('events.mail.map') }}</a>@endif</x-mail::detail>
@endif

@if ($onlineUrl)
<x-mail::detail :label="__('events.online')"><a href="{{ $onlineUrl }}">{{ $onlineUrl }}</a></x-mail::detail>
@endif

<x-mail::button :url="$eventUrl">
{{ __('events.mail.button') }}
</x-mail::button>

{{ __('events.mail.cancel_hint') }}

<x-slot:subcopy>
{{ __('events.mail.notice', ['email' => $email, 'event' => $title, 'app' => config('app.name')]) }}
</x-slot:subcopy>
</x-mail::message>
