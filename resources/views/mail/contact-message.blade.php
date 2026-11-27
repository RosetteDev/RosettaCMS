New contact form message from {{ $siteName }}

Name: {{ $senderName }}
Email: {{ $senderEmail }}
Subject: {{ $messageSubject }}

Message:
{{ $messageBody }}
@if(!empty($customFields))

Additional details:
@foreach($customFields as $key => $value)
{{ ucwords(str_replace('_', ' ', $key)) }}: {{ is_scalar($value) ? $value : json_encode($value) }}
@endforeach
@endif
