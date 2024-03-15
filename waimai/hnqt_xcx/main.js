import App from './App'
import uView from '@/uni_modules/uview-ui'
// import tabBar from 'components/tabBar.vue'
import goodsSpec from 'components/goodsSpec.vue'
import share from "./utils/share.js"

// Vue.component('tabBar',tabBar)
Vue.component('goodsSpec',goodsSpec)
Vue.use(uView)
Vue.mixin(share)
uni.$u.http.setConfig((config) => {
    /* config 为默认全局配置*/
    // config.baseURL = `https://hnqt.0898yzzx.com/`; /* 根域名 */
	config.baseURL = `http://waimai.com`; /* 根域名 */
    return config
})
// Vue.prototype.isLogin = function test() {
// 	uni.$u.http.post('api/member/isLogin', {
// 		key:uni.getStorageSync('user')?uni.getStorageSync('user').key:''
// 	}).then(res => {
// 		console.log('验证登录', res.data)
// 		if(res.data.code!=200){
// 			uni.removeStorageSync('user')
// 		}else{
// 			uni.setStorageSync('user',res.data.data)
// 		} 
// 	}).catch(err => {
// 		console.log('shibai', err)
// 	})
// }
// #ifndef VUE3
import Vue from 'vue'
Vue.config.productionTip = false
App.mpType = 'app'
const app = new Vue({
    ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif