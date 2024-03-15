import Vue from 'vue'
import Router from 'vue-router'
import login from '@/components/login'
import index from '@/components/index'
import personal from '@/components/personal'
import search from '@/components/search'
import qrcode from '@/components/qrcode'
import {
  createRouter,
  createWebHistory,
  createWebHashHistory
} from 'vue-router'
Vue.use(Router)

export default new Router({
  mode: "hash",
  routes: [{
      path: '/login',
      component: login,
      meta: {
        isLogin: false
      }
    },
    {
      path: '/index',
      component: index,
      meta: {
        isLogin: true
      }
    },
    {
      path: '/personal',
      component: personal,
      meta: {
        isLogin: true
      }
    },
    {
      path: '/search',
      component: search,
      meta: {
        isLogin: true
      }
    },
    {
      path: '/qrcode',
      component: qrcode,
      meta: {
        isLogin: true
      }
    }
  ]
})
