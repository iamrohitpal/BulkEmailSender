@php
    $hasHtml = strip_tags($body) !== $body;
@endphp
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.5; color: #222222; margin: 0; padding: 0; text-align: left; direction: ltr;">
    @if($hasHtml)
        {!! $body !!}
    @else
        {!! nl2br(e($body)) !!}
    @endif
</div>
