// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import 'lib-flexible/flexible'
import axios from 'axios'
import NProgress from 'vue-nprogress'
import {
  Col,
  Row,
  Image,
  NavBar,
  Tab,
  Tabs,
  Button,
  Step,
  Steps,
  Collapse,
  CollapseItem,
  Search,
  Form,
  Field,
  Notify,
  Icon,
  Dialog,
  Toast,
  List
} from 'vant'
axios.defaults.headers.post["Content-type"] = "application/json";
Vue.prototype.$axios = axios;
Vue.use(Col);
Vue.use(Row);
Vue.use(Image);
Vue.use(NavBar);
Vue.use(Tab);
Vue.use(Tabs);
Vue.use(Button);
Vue.use(Step);
Vue.use(Steps);
Vue.use(Collapse);
Vue.use(CollapseItem);
Vue.use(Search);
Vue.use(Form);
Vue.use(Field);
Vue.use(Notify);
Vue.use(Icon);
Vue.use(Dialog);
Vue.use(Toast);
Vue.use(List);
Vue.config.productionTip = false
/* eslint-disable no-new */

//路由守卫
router.beforeEach((to, from, next) => {
  NProgress.start();//NProgress实现显示加载进度条效果
	// 获取用户登录成功后存储的登录标志
	let isLogin = window.localStorage.getItem('id');
	// 如果登录标志存在且为isLogin,即用户已登录
	if (isLogin) {
		next()
		// 如果已登录，进行登录注册页面，则定向会首页
		if (!to.meta.isLogin) {
			next({ path: '/index' })
		}
	// 如果登录标志不存在，即未登录
	} else {
		// 用户想进入需要登录的页面，则定向回登录页面
		if (to.meta.isLogin) {
			next({ path: '/login' })
		// 用户进入无需登录的界面，则跳转继续
		} else {
			next()
		}
	}
})


new Vue({
  el: '#app',
  router,
  components: {
    App
  },
  template: '<App/>'
})
