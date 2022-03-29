@if($enabled)
<script type="application/javascript" async src="https://static.klaviyo.com/onsite/js/klaviyo.js?company_id={{$publicKey}}"></script>
@if($data->isNotEmpty())
<script type="application/javascript">
var _learnq = window._learnq || [];
@foreach($data as $item)
_learnq.push([{!! implode(', ', $item) !!}]);
@endforeach
</script>
@endif
@endif
