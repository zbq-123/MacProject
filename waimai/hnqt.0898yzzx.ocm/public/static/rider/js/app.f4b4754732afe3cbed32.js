webpackJsonp([1],{"+/ry":function(t,e){},"+ed2":function(t,e){},"/SKB":function(t,e){},"0+S2":function(t,e){},"0DIp":function(t,e){},"0Udj":function(t,e){},"23gA":function(t,e){},"2vBv":function(t,e){},"5Zmm":function(t,e){},"9NA7":function(t,e){},"9S6h":function(t,e){},"9fCr":function(t,e){},"D+QW":function(t,e){},FkuO:function(t,e){},HZoF:function(t,e){},NHnr:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});a("Mcfu");var s=a("r9aq"),n=a.n(s),r=(a("GKy3"),a("4vvA")),i=a.n(r),o=(a("MHRi"),a("6xqC")),c=a.n(o),d=(a("jydU"),a("WQwN")),l=a.n(d),v=(a("4T1P"),a("gJVR")),_=a.n(v),u=(a("wvM5"),a("MyDk")),h=a.n(u),p=(a("DRBK"),a("/CSG")),m=a.n(p),f=(a("9ULi"),a("lQdh")),g=a.n(f),y=(a("6dbz"),a("9MkJ")),b=a.n(y),x=(a("YaUx"),a("kj6/")),k=a.n(x),C=(a("ykdI"),a("px83")),z=a.n(C),R=(a("H2VA"),a("0vRA")),w=a.n(R),q=(a("FDxC"),a("w+oK")),S=a.n(q),$=(a("CCOf"),a("rrcz")),I=a.n($),O=(a("3evy"),a("Irlo")),E=a.n(O),L=(a("tcuZ"),a("iMPx")),N=a.n(L),j=(a("pLKN"),a("mZJz")),A=a.n(j),D=(a("jgNZ"),a("syWm")),F=a.n(D),M=(a("dKGA"),a("kSul")),P=a.n(M),U=a("7+uW"),Q={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{attrs:{id:"app"}},[e("router-view")],1)},staticRenderFns:[]};var B=a("VU/8")({name:"App"},Q,!1,function(t){a("23gA")},null,null).exports,V=a("/ocq"),Y={name:"Index",data:function(){return{username:"",password:""}},methods:{onSubmit:function(){var t=this;this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/login",{user_name:this.username,password:this.password}).then(function(e){200==e.data.code?(localStorage.setItem("username",e.data.rider_info.user_name),localStorage.setItem("campus_name",e.data.rider_info.campus_name),localStorage.setItem("id",e.data.rider_info.id),t.$router.push("/index"),setTimeout(function(){t.$notify({type:"success",message:"欢迎来到骑手系统"})},100)):t.$notify({type:"danger",message:e.data.msg})}).catch(function(t){console.error(t)})},getAutoCodeImg:function(){}}},H={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"center"},[t._m(0),t._v(" "),a("div",{staticClass:"front"},[a("div",{staticClass:"card"},[a("div",{staticClass:"title",attrs:{slot:"header"},slot:"header"},[a("span",[t._v("\n          骑手系统\n        ")])]),t._v(" "),a("div",[a("van-form",{on:{submit:t.onSubmit}},[a("van-field",{attrs:{name:"用户名",label:"用户名",placeholder:"请输入用户名",rules:[{required:!0,message:""}]},model:{value:t.username,callback:function(e){t.username=e},expression:"username"}}),t._v(" "),a("van-field",{attrs:{type:"password",name:"密码",label:"密码",placeholder:"请输入密码",rules:[{required:!0,message:""}]},model:{value:t.password,callback:function(e){t.password=e},expression:"password"}}),t._v(" "),a("div",{staticStyle:{margin:"16px"}},[a("van-button",{attrs:{round:"",block:"",type:"info","native-type":"submit"}},[t._v("提交")])],1)],1)],1)])])])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"background"},[e("img",{staticClass:"img",attrs:{src:"https://hnqt.0898yzzx.com/static/rider/a.png"}}),this._v(" "),e("img",{staticClass:"img_1",attrs:{src:"https://hnqt.0898yzzx.com/static/rider/a.png"}}),this._v(" "),e("img",{attrs:{src:"https://hnqt.0898yzzx.com/static/rider/bg.png",width:"100%",height:"100%",alt:""}})])}]};var K=a("VU/8")(Y,H,!1,function(t){a("kDQB")},"data-v-4746ee9d",null).exports,T=a("gBtx"),G=a.n(T),W=a("Xxa5"),Z=a.n(W),J=a("exGp"),X=a.n(J),tt=a("NCfY"),et={components:{QrcodeStream:tt.QrcodeStream},data:function(){return{}},methods:{onDecode:function(t){var e=this,a=t.indexOf("=");this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/addOrder",{rider_id:localStorage.getItem("id"),order_id:t.substr(a+1)}).then(function(t){t.data.rider_order_id?e.$router.push({path:"/index",query:{active:1,rider_order_id:t.data.rider_order_id}}):(e.$toast.loading({message:"二维码失效",forbidClick:!0,loadingType:"spinner"}),e.$router.push({path:"/index",query:{active:0}}))})},onInit:function(t){var e=this;return X()(Z.a.mark(function a(){return Z.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.prev=0,e.next=3,t;case 3:e.next=8;break;case 5:e.prev=5,e.t0=e.catch(0),"NotAllowedError"===e.t0.name?alert("ERROR: 您需要授予相机访问权限"):"NotFoundError"===e.t0.name?alert("ERROR: 这个设备上没有摄像头"):"NotSupportedError"===e.t0.name?alert("ERROR: 所需的安全上下文(HTTPS、本地主机)"):"NotReadableError"===e.t0.name?alert("ERROR: 相机被占用"):"OverconstrainedError"===e.t0.name?alert("ERROR: 安装摄像头不合适"):"StreamApiNotSupportedError"===e.t0.name&&alert("ERROR: 此浏览器不支持流API");case 8:case"end":return e.stop()}},a,e,[[0,5]])}))()}}},at={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"container"},[e("qrcode-stream",{staticClass:"qrcode",on:{decode:this.onDecode,init:this.onInit}},[e("div",{staticClass:"center"},[e("span",{staticClass:"border"}),this._v(" "),e("span",{staticClass:"border"}),this._v(" "),e("span",{staticClass:"border"}),this._v(" "),e("span",{staticClass:"border"}),this._v(" "),e("div",{staticClass:"animate"})])])],1)},staticRenderFns:[]};var st=a("VU/8")(et,at,!1,function(t){a("FkuO")},"data-v-579347ea",null).exports,nt={name:"Index",components:{"vue-qrcode":st},data:function(){return{active:G()(this.$route.query.active)?G()(this.$route.query.active):0,status:1,username:localStorage.getItem("username"),list:[],rider_order_count:0,msg:"",dialog:!1,loading:!1,finished:!1,page:1}},created:function(){localStorage.setItem("rider_order_id",this.$route.query.rider_order_id),this.curren()},methods:{curren:function(t){var e=this;this.list!=[]&&(this.list=[]),localStorage.setItem("i",t),this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/getRiderOderList",{rider_id:localStorage.getItem("id"),status:localStorage.getItem("i"),page:this.page}).then(function(t){var a=t.data.rider_order;e.loading=!1,e.rider_order_count=t.data.unsent_num,null!=a&&0!==a.length?(e.finished=!1,e.list=e.list.concat(a),e.list.length>=e.rider_order_count&&(e.finished=!0)):e.finished=!0})},onLoad:function(){this.page=this.page++,this.curren(localStorage.getItem("i"))},Personal:function(){this.$router.push("/personal")},qrcode:function(){this.$router.push({path:"/qrcode"})},search:function(){this.$router.push("/search")},btn:function(t){var e=this;this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/confirmRiderOder",{rider_order_id:t.id}).then(function(t){console.log(t),200==t.data.code?e.active=2:e.$toast.fail("暂无信息")})},popup:function(){this.dialog=!0},close:function(){this.dialog=!1},btn_1:function(t){var e=this;this.dialog=!1,this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/undeliveredRiderOder",{rider_order_id:t.id,remarks:this.msg}).then(function(t){200==t.data.code?e.active=3:e.$toast.fail("暂无信息")})}}},rt={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"index"},[a("div",{staticClass:"nav"},[a("van-row",{attrs:{type:"flex",align:"center"}},[a("van-col",{attrs:{span:"2"}},[a("van-image",{attrs:{width:"23",height:"23",round:"",src:"https://cdn.jsdelivr.net/npm/@vant/assets/cat.jpeg"},on:{click:t.Personal}})],1),t._v(" "),a("van-col",{staticClass:"title",attrs:{span:"5"},on:{click:t.Personal}},[t._v(t._s(t.username))]),t._v(" "),a("van-col",{attrs:{span:"5",offset:"3"}},[t._v("送餐列表")])],1)],1),t._v(" "),a("van-tabs",{staticClass:"list",attrs:{border:!1,shrink:"","line-width":"30","line-height":"2","title-active-color":"white","title-inactive-color":"gray",background:"#0d1a39",color:"orange"},on:{click:t.curren},model:{value:t.active,callback:function(e){t.active=e},expression:"active"}},[a("van-tab",{staticClass:"saoma",attrs:{title:"收餐"}},[a("van-button",{attrs:{icon:"https://hnqt.0898yzzx.com/static/rider/saoma.png",color:"#f7d31c"},on:{click:t.qrcode}},[t._v("扫码收餐")]),a("br"),t._v(" "),a("van-button",{attrs:{icon:"https://hnqt.0898yzzx.com/static/rider/search.png",color:"#f7d31c"},on:{click:t.search}},[t._v("搜索收餐")])],1),t._v(" "),a("van-tab",{staticClass:"stepss",attrs:{title:"待送订单"+t.rider_order_count}},[a("van-list",{attrs:{finished:t.finished,"finished-text":"没有更多了"},on:{load:t.onLoad},model:{value:t.loading,callback:function(e){t.loading=e},expression:"loading"}},[t._l(t.list,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:1}},[a("p",[a("img",{attrs:{src:"https://hnqt.0898yzzx.com/static/rider/dishi.png"}}),t._v(t._s(e.create_time)),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生/女士)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单号"+t._s(e.order_number))])]),t._v(" "),a("van-step",{staticClass:"big_btn"},[a("van-button",{staticClass:"btn",attrs:{size:"large",type:"primary"},on:{click:function(a){return t.btn(e)}}},[t._v("已送达")]),t._v(" "),a("van-button",{staticClass:"btn",attrs:{size:"large",type:"danger"},on:{click:t.popup}},[t._v("未送达")])],1)],1),t._v(" "),a("div",{directives:[{name:"show",rawName:"v-show",value:t.dialog,expression:"dialog"}],staticClass:"zhao"}),t._v(" "),a("div",{directives:[{name:"show",rawName:"v-show",value:t.dialog,expression:"dialog"}],staticClass:"dialog"},[a("h2",[t._v("留言")]),t._v(" "),a("textarea",{directives:[{name:"model",rawName:"v-model",value:t.msg,expression:"msg"}],domProps:{value:t.msg},on:{input:function(e){e.target.composing||(t.msg=e.target.value)}}}),t._v(" "),a("van-button",{attrs:{size:"large",type:"default"},on:{click:t.close}},[t._v("取消")]),t._v(" "),a("van-button",{attrs:{size:"large",type:"primary"},on:{click:function(a){return t.btn_1(e)}}},[t._v("确定")])],1)]})})],2)],1),t._v(" "),a("van-tab",{staticClass:"stepss",attrs:{title:"已送达顾客"}},[a("van-list",{attrs:{finished:t.finished,"finished-text":"没有更多了"},on:{load:t.onLoad},model:{value:t.loading,callback:function(e){t.loading=e},expression:"loading"}},[t._l(t.list,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:1}},[a("p",[a("img",{attrs:{src:"https://hnqt.0898yzzx.com/static/rider/dishi.png"}}),t._v("已送达"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生/女士)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单号"+t._s(e.order_number))])])],1)]})})],2)],1),t._v(" "),a("van-tab",{staticClass:"stepss",attrs:{title:"未送达顾客"}},[a("van-list",{attrs:{finished:t.finished,"finished-text":"没有更多了"},on:{load:t.onLoad},model:{value:t.loading,callback:function(e){t.loading=e},expression:"loading"}},[t._l(t.list,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:0}},[a("p",[a("img",{attrs:{src:"https://hnqt.0898yzzx.com/static/rider/dishi.png"}}),t._v(t._s(e.create_time)),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生/女士)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单号"+t._s(e.order_number))])]),t._v(" "),a("van-step",[a("b",[t._v(t._s(e.remarks))])])],1)]})})],2)],1)],1)],1)},staticRenderFns:[]};var it=a("VU/8")(nt,rt,!1,function(t){a("QWoB")},null,null).exports,ot={name:"Personal",data:function(){return{activeNames:["0"],active:0,on:1,on1:1,username:localStorage.getItem("username"),campus_name:localStorage.getItem("campus_name"),list:{},month:"",todaylist:[],todaylist1:[],i:1}},created:function(){var t=this,e=new Date;this.month=e.getMonth()+1,this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/countRiderOrder",{rider_id:localStorage.getItem("id"),type:this.i}).then(function(e){t.list=e.data}),this.today(0),this.month1(0)},methods:{tabHandler:function(t){var e=this;this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/countRiderOrder",{rider_id:localStorage.getItem("id"),type:t+1}).then(function(t){e.list=t.data}),this.today(t),this.month1(t)},today:function(t){var e=this,a=new Date,s=a.getFullYear(),n=a.getMonth()+1,r=a.getDate();this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/getRiderOderList",{rider_id:localStorage.getItem("id"),status:0==t?2:3,starttime:s+"-"+n+"-"+r+" 00:00:00",endtime:s+"-"+n+"-"+r+" 23:59:59"}).then(function(t){e.todaylist=t.data.rider_order})},month1:function(t){var e=this,a=new Date;a.getFullYear(),a.getMonth(),a.getDate();this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/getRiderOderList",{rider_id:localStorage.getItem("id"),status:0==t?2:3}).then(function(t){e.todaylist1=t.data.rider_order})},onback:function(){this.$router.go(-1)},set:function(){this.$notify({type:"danger",message:"暂未开发"})},search:function(){this.$router.push("/search")}}},ct={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"index"},[a("div",{staticClass:"nav"},[a("van-row",{attrs:{type:"flex",align:"center"}},[a("van-col",{attrs:{span:"2"}},[a("van-image",{attrs:{width:"23",height:"23",round:"",src:"https://hnqt.0898yzzx.com/static/rider/left.png"},on:{click:t.onback}})],1),t._v(" "),a("van-col",{attrs:{span:"10",offset:"8"}},[t._v("个人中心")])],1)],1),t._v(" "),a("div",{staticClass:"b1"},[a("div",{staticClass:"b2"},[a("img",{attrs:{src:"https://cdn.jsdelivr.net/npm/@vant/assets/cat.jpeg"}}),t._v(" "),a("div",{staticClass:"b1_left"},[a("p",[t._v(t._s(t.username)+"\n          "),a("van-image",{attrs:{width:"15",height:"15",src:"https://hnqt.0898yzzx.com/static/rider/right.png"}})],1),t._v(" "),a("span",[t._v(t._s(t.campus_name))])])]),t._v(" "),a("button",{attrs:{type:"button"},on:{click:t.set}},[t._v("服务与设置")])]),t._v(" "),a("van-tabs",{attrs:{"line-width":"30","line-height":"2","title-active-color":"white","title-inactive-color":"gray",background:"#0d1a39",color:"orange"},on:{click:t.tabHandler},model:{value:t.active,callback:function(e){t.active=e},expression:"active"}},[a("van-tab",{staticClass:"today",attrs:{title:"今日订单"}},[a("div",{staticClass:"moth"},[a("h1",[a("i",[t._v("共计")]),t._v(t._s(t.list.count.zj)+" "),a("i",[t._v("单")])])]),t._v(" "),a("p",{staticClass:"p"},[t._v("订单明细")]),t._v(" "),a("van-tabs",{attrs:{"line-width":"30","line-height":"2","title-active-color":"black","title-inactive-color":"gray",color:"orange",background:"transparent"},on:{click:t.today},model:{value:t.on,callback:function(e){t.on=e},expression:"on"}},[a("van-tab",{staticClass:"steps",attrs:{title:"已送达 "+t.list.count.ysd+"单"}},[t._l(t.todaylist,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:0}},[a("p",[t._v(t._s(e.create_time)+" 收餐 ~ "+t._s(e.update_time)+" 转交"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单编号"+t._s(e.order_number))])])],1)]})})],2),t._v(" "),a("van-tab",{staticClass:"steps",attrs:{title:"未送达 "+t.list.count.wsd+"单"}},[t._l(t.todaylist,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:0}},[a("p",[t._v(t._s(e.create_time)+" 收餐 ~ "+t._s(e.update_time)+" 转交"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单编号"+t._s(e.order_number))])])],1)]})})],2)],1)],1),t._v(" "),a("van-tab",{staticClass:"steps",attrs:{title:"月订单"}},[a("h1",[t._v(t._s(t.month)+"月订单")]),t._v(" "),a("h2"),t._v(" "),a("div",{staticClass:"moth"},[a("h1",[a("i",[t._v("共计")]),t._v(t._s(t.list.count.zj)+" "),a("i",[t._v("单")])])]),t._v(" "),a("p",{staticClass:"p"},[t._v("订单明细")]),t._v(" "),a("van-tabs",{attrs:{"line-width":"30","line-height":"2","title-active-color":"black","title-inactive-color":"gray",color:"orange",background:"transparent"},on:{click:t.month1},model:{value:t.on1,callback:function(e){t.on1=e},expression:"on1"}},[a("van-tab",{staticClass:"steps",attrs:{title:"已送达 "+t.list.count.ysd+"单"}},[t._l(t.todaylist1,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:0}},[a("p",[t._v(t._s(e.create_time)+" 收餐 ~ "+t._s(e.update_time)+" 转交"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单编号"+t._s(e.order_number))])])],1)]})})],2),t._v(" "),a("van-tab",{staticClass:"steps",attrs:{title:"未送达 "+t.list.count.wsd+"单"}},[t._l(t.todaylist1,function(e,s){return t._t("default",function(){return[a("van-steps",{attrs:{direction:"vertical",active:0}},[a("p",[t._v(t._s(e.create_time)+" 收餐 ~ "+t._s(e.update_time)+" 转交"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单编号"+t._s(e.order_number))])])],1)]})})],2)],1),t._v(" "),a("p",{staticClass:"p"},[t._v("过往月订单")]),t._v(" "),a("ul",{staticClass:"agen"},t._l(t.list.before,function(e,s){return a("li",{key:s,on:{click:t.search}},[a("p",[a("span",[t._v(t._s(e.month)+"月共计送达")]),a("span",[t._v(t._s(e.zj)+"单")])]),t._v(" "),a("b",[t._v("详情 >")])])}),0)],1)],1)],1)},staticRenderFns:[]};var dt=a("VU/8")(ot,ct,!1,function(t){a("kgN4")},null,null).exports,lt={name:"search",data:function(){return{active:0,value:"",list:[]}},methods:{onback:function(){this.$router.go(-1)},onSearch:function(){var t=this;this.$axios.post("https://hnqt.0898yzzx.com/api/Rider/getRiderOderList",{rider_id:localStorage.getItem("id"),keyword:this.value}).then(function(e){console.log(e),t.list=e.data.rider_order})}}},vt={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"search"},[a("div",{staticClass:"search_1"},[a("van-image",{attrs:{width:"20",height:"20",src:"https://hnqt.0898yzzx.com/static/rider/b_left.png"},on:{click:t.onback}}),t._v(" "),a("van-search",{attrs:{"show-action":"",placeholder:"请输入/订单编号/收货人姓名/收货人手机号"},on:{search:t.onSearch},scopedSlots:t._u([{key:"action",fn:function(){return[a("div",{on:{click:t.onSearch}},[t._v("搜索")])]},proxy:!0}]),model:{value:t.value,callback:function(e){t.value=e},expression:"value"}})],1),t._v(" "),a("div",{staticStyle:{height:"54px"}}),t._v(" "),0==t.list.length?a("p",{staticClass:"none"},[t._v("暂无搜索内容")]):t._e(),t._v(" "),t._l(t.list,function(e,s){return t._t("default",function(){return[a("van-steps",{staticClass:"stepss",attrs:{direction:"vertical",active:0}},[a("p",[a("img",{attrs:{src:"https://hnqt.0898yzzx.com/static/rider/dishi.png"}}),t._v("还剩 "),a("span",[t._v("2分钟")]),t._v("\n        送达"),a("b",[t._v("#"+t._s(e.today_number))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.store_name))])]),t._v(" "),a("van-step",[a("h3",[t._v(t._s(e.order.campus_name)+"( "+t._s(e.order.delivery_address)+" )")]),t._v(" "),a("h4",[t._v(t._s(e.order.delivery_name)+"(先生)"+t._s(e.order.delivery_phone))]),t._v(" "),a("h4",[t._v("订单号"+t._s(e.order_number))])]),t._v(" "),a("van-step",[a("van-button",{staticClass:"btn",attrs:{size:"large"}},[t._v("送达至客户")])],1)],1)]})})],2)},staticRenderFns:[]};var _t=a("VU/8")(lt,vt,!1,function(t){a("2vBv")},"data-v-0b696cdb",null).exports;U.default.use(V.a);var ut=new V.a({mode:"hash",routes:[{path:"/login",component:K,meta:{isLogin:!1}},{path:"/index",component:it,meta:{isLogin:!0}},{path:"/personal",component:dt,meta:{isLogin:!0}},{path:"/search",component:_t,meta:{isLogin:!0}},{path:"/qrcode",component:st,meta:{isLogin:!0}}]}),ht=(a("sVYa"),a("mtWM")),pt=a.n(ht),mt=a("GtG6"),ft=a.n(mt);pt.a.defaults.headers.post["Content-type"]="application/json",U.default.prototype.$axios=pt.a,U.default.use(P.a),U.default.use(F.a),U.default.use(A.a),U.default.use(N.a),U.default.use(E.a),U.default.use(I.a),U.default.use(S.a),U.default.use(w.a),U.default.use(z.a),U.default.use(k.a),U.default.use(b.a),U.default.use(g.a),U.default.use(m.a),U.default.use(h.a),U.default.use(_.a),U.default.use(l.a),U.default.use(c.a),U.default.use(i.a),U.default.use(n.a),U.default.config.productionTip=!1,ut.beforeEach(function(t,e,a){ft.a.start(),window.localStorage.getItem("id")?(a(),t.meta.isLogin||a({path:"/index"})):t.meta.isLogin?a({path:"/login"}):a()}),new U.default({el:"#app",router:ut,components:{App:B},template:"<App/>"})},OEKK:function(t,e){},QTcP:function(t,e){},QWoB:function(t,e){},UR9n:function(t,e){},XqYu:function(t,e){},"Y/Gm":function(t,e){},YAYC:function(t,e){},"Z+4s":function(t,e){},Zzpz:function(t,e){},hSFT:function(t,e){},j7dL:function(t,e){},jLuM:function(t,e){},k86u:function(t,e){},kDQB:function(t,e){},kgN4:function(t,e){},nqem:function(t,e){},s1Ps:function(t,e){}},["NHnr"]);
//# sourceMappingURL=app.f4b4754732afe3cbed32.js.map