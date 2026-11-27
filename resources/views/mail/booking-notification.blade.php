New booking request from {{ $siteName }}

Service: {{ $service ?: '(not specified)' }}
Name: {{ $bookingName }}
Email: {{ $bookingEmail }}
@if($bookingPhone)
Phone: {{ $bookingPhone }}
@endif
Date: {{ $date }}
@if($time)
Time: {{ $time }}
@endif

@if($notes)
Notes:
{{ $notes }}
@endif
