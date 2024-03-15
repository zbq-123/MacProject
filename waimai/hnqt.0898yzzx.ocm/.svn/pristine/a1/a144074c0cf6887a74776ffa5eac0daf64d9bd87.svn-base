layui.define('layer', function (exports) {
    var $ = layui.$, layer = layui.layer;
    var loadOptions = {offset: "50px"};
    var loading;
    var obj =  {
        start: function (options) {
            var opts = $.extend(loadOptions, options);
            if (loading == undefined ) {
                loading = layer.load(0 , opts);
            }else{
                return this.loading;
            }
        },
        close: function () {
            if (loading != undefined) {
                layer.close(loading);
                loading = undefined;
            }
        }
    };

   exports ('loading', obj);
});

