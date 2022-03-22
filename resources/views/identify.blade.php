<?php /** @var array $identity */ ?>
@if(!empty($identity))
    <script type="application/javascript">
        var _learnq = window._learnq || [];
        _learnq.push(['identify', {{ \Illuminate\Support\Js::from($identity) }}])
    </script>
@endif
