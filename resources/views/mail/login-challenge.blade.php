<x-mail::message>
# {{ __('auth.mail.heading') }}

{{ __('auth.mail.intro') }}

{{ __('auth.mail.code_label') }}: **{{ $code }}**

{{ __('auth.mail.link_intro') }}

<x-mail::button :url="$magicUrl">
{{ __('auth.mail.button') }}
</x-mail::button>

{{ __('auth.mail.expiry') }}

{{ __('auth.mail.ignore') }}

<x-slot:subcopy>
{{ __('mail.footer.notice', ['email' => $email, 'app' => config('app.name')]) }}
</x-slot:subcopy>
</x-mail::message>
