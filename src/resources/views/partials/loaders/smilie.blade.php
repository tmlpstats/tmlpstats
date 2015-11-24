<div class="leftEye"></div>
<div class="rightEye"></div>
<div class="mouth"></div>

@section('headers')
<style type="text/css">
    body .leftEye,
    body .rightEye {
        width: 5vh;
        height: 5vh;
        border-radius: 50%;
        background: #666666;
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
        -webkit-animation: leftEyeAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
        animation: leftEyeAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
    }
    body .rightEye {
        -webkit-animation: rightEyeAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
        animation: rightEyeAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
    }
    body .mouth {
        width: 10vh;
        height: 10vh;
        border-radius: 50%;
        border: solid 1.3vh #999999;
        border-right: solid 1.3vh rgba(223,223,194,0);
        border-left: solid 1.3vh rgba(223,223,194,0);
        border-bottom: solid 1.3vh rgba(223,223,194,0);
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%) rotate(360deg);
        -ms-transform: translate(-50%, -50%) rotate(360deg);
        transform: translate(-50%, -50%) rotate(360deg);
        -webkit-animation: mouthAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
        animation: mouthAnimation 3s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
    }
    @-webkit-keyframes mouthAnimation {
        0% {
            -webkit-transform: translate(-50%, -50%) rotateX(180deg);
            transform: translate(-50%, -50%) rotateX(180deg);
        }
        10% {
            -webkit-transform: translate(-50%, -50%) rotateZ(360deg);
            transform: translate(-50%, -50%) rotateZ(360deg);
        }
        40% {
            -webkit-transform: translate(-50%, -50%) rotateZ(320deg);
            transform: translate(-50%, -50%) rotateZ(320deg);
        }
        60% {
            -webkit-transform: translate(-50%, -50%) rotateZ(900deg);
            transform: translate(-50%, -50%) rotateZ(900deg);
        }
        100% {
            -webkit-transform: translate(-50%, -50%) rotateZ(900deg);
            transform: translate(-50%, -50%) rotateZ(900deg);
        }
    }
    @keyframes mouthAnimation {
        0% {
            -webkit-transform: translate(-50%, -50%) rotateX(180deg);
            transform: translate(-50%, -50%) rotateX(180deg);
        }
        10% {
            -webkit-transform: translate(-50%, -50%) rotateZ(360deg);
            transform: translate(-50%, -50%) rotateZ(360deg);
        }
        40% {
            -webkit-transform: translate(-50%, -50%) rotateZ(320deg);
            transform: translate(-50%, -50%) rotateZ(320deg);
        }
        60% {
            -webkit-transform: translate(-50%, -50%) rotateZ(900deg);
            transform: translate(-50%, -50%) rotateZ(900deg);
        }
        100% {
            -webkit-transform: translate(-50%, -50%) rotateZ(900deg);
            transform: translate(-50%, -50%) rotateZ(900deg);
        }
    }
    @-webkit-keyframes leftEyeAnimation {
        0% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        50% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        60% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(-150%, -50%);
            transform: translate(-150%, -50%);
        }
        90% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(-150%, -50%);
            transform: translate(-150%, -50%);
        }
        100% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }
    @keyframes leftEyeAnimation {
        0% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        50% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        60% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(-150%, -50%);
            transform: translate(-150%, -50%);
        }
        90% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(-150%, -50%);
            transform: translate(-150%, -50%);
        }
        100% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }
    @-webkit-keyframes rightEyeAnimation {
        0% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        50% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        60% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        70% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        75% {
            width: 2vh;
            height: 2px;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        80% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        90% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        100% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }
    @keyframes rightEyeAnimation {
        0% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        50% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        60% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        70% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        75% {
            width: 2vh;
            height: 2px;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        80% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        90% {
            width: 2vh;
            height: 2vh;
            -webkit-transform: translate(50%, -50%);
            transform: translate(50%, -50%);
        }
        100% {
            width: 5vh;
            height: 5vh;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }
</style>
@endsection
