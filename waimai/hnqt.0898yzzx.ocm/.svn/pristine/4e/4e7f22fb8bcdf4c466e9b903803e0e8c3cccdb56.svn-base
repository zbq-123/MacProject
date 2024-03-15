layui.define(['jquery', 'layer','upload'], function (exports) {
    var $ = layui.$, layer = layui.layer, upload = layui.upload;

    var loadOptions = {offset: "50px"};  //loading设置
    var loading;

    //放大图片
    $(function(){
       $("#content").on('click', 'img.xu-title-pic', function (e) {
           event.preventDefault();
           var src = $(this).attr("src");
           layer.open({
              type:1,
               title: false,
               area: ['700px', '450px'],
               shade: 0.3,
               closeBtn: 0,
               shadeClose: true,
               resize:false,
               content:"<img src='"+src+"' height='450' width='700'>"
           });
       })
    });


    var obj =  {
        /****layui加载框***/
        startLoading: function (type, options) {
            var opts = $.extend(loadOptions, options);
            type = type == undefined ? 0 : 1;
            if (loading == undefined ) {
                loading = layer.load(type , opts);
            }else{
                return this.loading;
            }
        },
        closeLoading: function () {
            if (loading != undefined) {
                layer.close(loading);
                loading = undefined;
            }
        },
        /****iframe自适应高度***/
        iframeLoaded: function(iframeId) {
            var iframe = "object" == typeof iframeId ? iframeId: document.getElementById(iframeId);
            //console.log(iframe);
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
        },
        reSetIframeHeight: function(iframeId) {
            try {
                var oIframe = iframeId ? parent.document.getElementById(iframeId) : parent.document.getElementById("content-iframe");
                oIframe.height = 100;
                this.iframeLoaded(oIframe);
            }catch (err){console.log(err)}

        },
        /*****ajax****/
        postHttp: function (url, data, callback, errorcallback) {
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
                        if (undefined != errorcallback ) {
                            errorcallback(res.code, res.message);
                        }
                    }
                }
                ,error: function (xhr,text) {
                    layer.msg(xhr.status+": "+text, {});
                    if (undefined != errorcallback ) {
                        errorcallback(xhr.status, text);
                    }
                }
            });
        },
        getHttp: function (url, data, callback, errorcallback) {
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
                        if (undefined != errorcallback ) {
                            errorcallback(res.code, res.message);
                        }
                    }
                }
                ,error: function (xhr,text) {
                    layer.msg(xhr.status+": "+text, {});
                    if (undefined != errorcallback ) {
                        errorcallback(xhr.status, text);
                    }
                }
            });
        },
        /***打开新闻预览页**/
        /*previewNews: function (options, url) {
            var o = {
                type: 2
                ,content: url
                ,title: "新闻预览"
                ,area: ['360px', '640px']
                ,shadeClose: true
            };
            var opts = $.extend(o, options);
            layer.open(opts);
        }*/
        //前面需要先引入ueEditor
        ueEditor: function (id, options) {
            var opt = {
                initialFrameWidth:600,
                initialFrameHeight: 400,
                enableAutoSave:false,
                autoHeightEnabled:false,
                toolbarTopOffset:300,
                enableContextMenu:false,//右键功能
                elementPathEnabled:false,//元素路径
                wordCount:false, //字数统计
                toolbars: [
                    [
                        'fullscreen', 'source', '|', 'undo', 'redo', '|',
                        'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                        'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                        'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                        'directionalityltr', 'directionalityrtl', 'indent', '|',
                        'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
                        'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
                        'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
                        'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
                        'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
                        'print', 'preview', 'searchreplace', 'drafts', 'help'
                    ]
                ]
            }
            var opts = $.extend(opt, options);
            return UE.getEditor(id, opts);
        },
        /**
         * 上传普通图片
         * @param uploadId 上传控件id
         * @param urlId 保存url的控件id
         * @param url 上传地址
         * @param callback
         */
        imageUploadInit: function (uploadId, url, callback) {
            upload.render({
                elem: uploadId
                ,url: url
                ,size: 1024
                ,done: function(res, index, upload){
                    if(res.code == 200){
                        $(uploadId).parent().parent().find('.layui-input-url').eq(0).val(res.data);
                        $(uploadId).html("<img src='"+res.data+"' width='150' height='95'>");
                        $(uploadId).css({"padding": "0"});
                        if (undefined != callback) {
                            callback(res.data);
                        }
                    }
                }
            });
        },
        /****cookie****/
        setCookie: function(name,value,time)
        {
            if(undefined != time) {
                var exp = new Date();
                exp.setTime(exp.getTime() + time);
                document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
            }else{
                document.cookie = name + "="+ escape (value);
            }
        },
        getCookie: function(name)
        {
            var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
            if(arr=document.cookie.match(reg))
                return unescape(arr[2]);
            else
                return null;
        },
        delCookie: function(name)
        {
            var exp = new Date();
            exp.setTime(exp.getTime() - 1);
            var cval=this.getCookie(name);
            if(cval!=null)
                document.cookie= name + "="+cval+";expires="+exp.toGMTString();
        }
    };

    exports ('xlp', obj);
});

