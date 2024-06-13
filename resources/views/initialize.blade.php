@if($enabled)
<script type="application/javascript" async src="https://static.klaviyo.com/onsite/js/klaviyo.js?company_id={{$publicKey}}"></script>
@include('klaviyo::object')
@if($data->isNotEmpty())
<script type="application/javascript">
@include('klaviyo::recursive', ['items' => $data])
</script>
@endif
@endif
