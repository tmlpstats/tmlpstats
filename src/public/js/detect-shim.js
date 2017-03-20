(function(document) {
    var needsShim = 'n';
    if (!Object.assign || !String.prototype.startsWith) {
        needsShim = 'y';
        var s = document.createElement('script');
        s.src = '/vendor/js/shim.min.js';
        var firstScript = document.getElementsByTagName('script')[0];
        firstScript.parentNode.insertBefore(s, firstScript);
    }
    $.post('/api/UserProfile.needsShim', {v: needsShim}, null, 'json');
})(document);
