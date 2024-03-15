/**
 * ajax请求模块
 */
layui.define(['jquery','layer'], function (exports) {
    var $ = layui.$, layer = layui.layer;

    var http =  {
        post: function (url, data, callback) {
            $.ajax({
                url: url
                , type: "POST"
                ,dataType: "json"
                , data: data
                , success: function (res) {
                    if (res.code == 200 ) {
                        callback(res.data);
                    }else{
                        layer.msg(res.code+": "+res.message);
                    }
                }
                ,error: function (xhr,text) {
                    layer.msg(xhr.status+": "+text, {});
                }
            });
        },
        get: function (url, data, callback) {
            $.ajax({
                url: url
                , type: "GET"
                ,dataType: "json"
                , data: data
                , success: function (res) {
                    if (res.code == 200 ) {
                        callback(res.data);
                    }else{
                        layer.msg(res.code+": "+res.message);
                    }
                }
                ,error: function (xhr,text) {
                    layer.msg(xhr.status+": "+text, {});
                }
            });
        }
    };

    exports ('http', http);
});

