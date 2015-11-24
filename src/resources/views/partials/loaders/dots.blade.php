<div class="duo duo1">
<div class="dot dot-a"></div>
<div class="dot dot-b"></div>
</div>
<div class="duo duo2">
<div class="dot dot-a"></div>
<div class="dot dot-b"></div>
</div>

@section('headers')
<style type="text/css">
    .duo {
        height: 20px;
        width: 50px;
        background: hsla(0, 0%, 0%, 0.0);
        position: absolute;
    }
    .duo, .dot {
        animation-duration: 0.8s;
        animation-timing-function: ease-in-out;
        animation-iteration-count: infinite;
    }
    .duo1 {
        left: 0;
        animation-name: spin;
    }
    .duo2 {
        left: 30px;
        animation-name: spin;
        animation-direction: reverse;
    }
    .dot {
        width: 20px;
        height: 20px;
        border-radius: 10px;
        background: #333;
        position: absolute;
    }
    .dot-a {
        left: 0px;
    }
    .dot-b {
        right: 0px;
    }
    .duo2 .dot-b {
        animation-name: onOff;
    }
    .duo1 .dot-a {
        opacity: 0;
        animation-name: onOff;
        animation-direction: reverse;
    }
    @keyframes spin {
        0% { transform: rotate(0deg) }
        50% { transform: rotate(180deg) }
        100% { transform: rotate(180deg) }
    }
    @keyframes onOff {
        0% { opacity: 0; }
        49% { opacity: 0; }
        50% { opacity: 1; }
        100% { opacity: 1; }
    }
</style>
@endsection
