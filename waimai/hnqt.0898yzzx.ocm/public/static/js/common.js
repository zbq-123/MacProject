;function iframeLoaded(iframe) {
    var i = document.getElementById("content-iframe");
    iframe = iframe || i;
    if (iframe.src.length > 0) {
        if (!iframe.readyState || iframe.readyState == "complete") {
            var bHeight =
                iframe.contentWindow.document.body.scrollHeight;
            var dHeight =
                iframe.contentWindow.document.documentElement.scrollHeight;
            var height = Math.max(bHeight, dHeight);
            iframe.height = height;
            // console.log('bHeight--->'+bHeight);
            // console.log('\ndHeight--->'+dHeight);
            // console.log('\niframe.height--->'+iframe.height);
        }
    }
}
var reSetIframeHeight = function()
{
    try {
        var oIframe = parent.document.getElementById('content-iframe');
        oIframe.height = 100;
        iframeLoaded(oIframe);
    }
    catch (err)
    {
        try {
            parent.document.getElementById('content-iframe').height = 1000;
        } catch (err2) { }
    }
};
var refresh = function () {
    location.reload();
}