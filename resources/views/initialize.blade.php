<script type="application/javascript" async src="https://static.klaviyo.com/onsite/js/klaviyo.js?company_id={{app(\EonVisualMedia\LaravelKlaviyo\KlaviyoClient::class)->getPublicKey()}}"></script>
@if(!empty($data))
<script type="application/javascript">
var _learnq = window._learnq || [];
@foreach($data as $item)
_learnq.push([{!! implode(', ', $item) !!}])
@endforeach
</script>
@endif
