@extends('template')

@section('headers')
    <style rel="stylesheet">

    </style>
@endsection

@section('content')
    <div id="zf_div_GWaBfellkrT6Sgu1gQkHJk2ik74RFgcb-VAD3pBFv9o"></div>
    <script type="text/javascript">(function() {
            try{
                var f = document.createElement("iframe");
                f.src ="https://apply.tmlpstats.com/digistax/form/TMLPValidatorTeam/formperma/GWaBfellkrT6Sgu1gQkHJk2ik74RFgcb-VAD3pBFv9o?zf_rszfm=1&referrername=opportunity2020_wkd";
                f.style.border="none";
                f.style.height="4693px";
                f.style.width="90%";
                f.style.transition="all 0.5s ease";// No I18N
                var d = document.getElementById("zf_div_GWaBfellkrT6Sgu1gQkHJk2ik74RFgcb-VAD3pBFv9o");
                d.appendChild(f);
                window.addEventListener('message', function (){
                    var zf_ifrm_data = event.data.split("|");
                    var zf_perma = zf_ifrm_data[0];
                    var zf_ifrm_ht_nw = ( parseInt(zf_ifrm_data[1], 10) + 15 ) + "px";
                    var iframe = document.getElementById("zf_div_GWaBfellkrT6Sgu1gQkHJk2ik74RFgcb-VAD3pBFv9o").getElementsByTagName("iframe")[0];
                    if ( (iframe.src).indexOf('formperma') > 0 && (iframe.src).indexOf(zf_perma) > 0 ) {
                        var prevIframeHeight = iframe.style.height;
                        if ( prevIframeHeight != zf_ifrm_ht_nw ) {
                            iframe.style.height = zf_ifrm_ht_nw;
                        }
                    }
                }, false);
            }catch(e){}
        })();</script>
@endsection
