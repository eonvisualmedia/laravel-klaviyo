<?php /** @var \EonVisualMedia\LaravelKlaviyo\Contracts\ViewedProduct $product */ ?>
<script type="application/javascript">
    var _learnq = window._learnq || [];
    _learnq.push(['track', 'Viewed Product', {{ \Illuminate\Support\Js::from($product->getViewedProductProperties()) }}])
    _learnq.push(['trackViewedItem', {{ \Illuminate\Support\Js::from($product->getViewedItemProperties()) }}])
</script>
