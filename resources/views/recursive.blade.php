@foreach($items as $item)
@if($item[0] === 'identify')
<x-klaviyo::identify :item="$item">
@include('klaviyo::recursive', ['items' => $items->slice($loop->index + 1)])
</x-klaviyo::identify>
@break
@endif
<x-klaviyo::default :item="$item"/>
@endforeach
