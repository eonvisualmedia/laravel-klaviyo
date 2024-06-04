@if($slot->isEmpty())
klaviyo.identify(@json($item[1]));
@else
klaviyo.identify(@json($item[1]), function () {
{{ $slot }}
});
@endif
