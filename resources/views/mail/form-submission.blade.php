{{ $formName }} — new submission

{{ $siteName }}

{{ str_repeat('-', 48) }}

@foreach($values as $key => $value)
{{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}
@endforeach

{{ str_repeat('-', 48) }}

Submitted via the Forms plugin.
