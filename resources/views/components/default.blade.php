@switch(count($item))
@case(1)
klaviyo.push(["{{ $item[0] }}"]);
@break
@case(2)
klaviyo.push(["{{ $item[0] }}", @json($item[1])]);
@break
@case(3)
klaviyo.push(["{{ $item[0] }}", @json($item[1]), @json($item[2])]);
@break
@endswitch
