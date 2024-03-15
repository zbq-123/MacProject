<template>
	<view>
		<u-sticky>
			<u-navbar title="圈圈食堂" bgColor="transparent" leftIcon=" " :fixed="true" :placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
			</u-navbar>
			<u-popup :show="show" mode="top" @close="close" @open="open" :safeAreaInsetBottom="false"
				customStyle="width:90%;margin:40rpx auto;top:70px">
				<view class="list" v-for="(item,index) in school" :key="index" @click="updateusercampus(item)">
					<text>{{item.name}}</text>
					<text>{{item.address}}</text>
					<text>距您大约{{item.distance}}</text>
				</view>
			</u-popup>
			<!-- 定位 -->
			<view class="dinwei">
				<view style="display: flex;align-items: center;">
					<u-icon name="map-fill" color="#000"></u-icon>
					<text @click="show = true">{{dinwei}}</text>
				</view>
				<view class="temperature">温度:{{degree}}℃ 天气:{{weather}}</view>
			</view>
			<!-- 搜素 -->
			<view class="search">
				<u-search shape="round" placeholder="请输入店铺或商品" bgColor="#fff" animation="true" @custom="custom()"
					@search="custom()"></u-search>
			</view>
		</u-sticky>
		<view class="bg-color">
			<!-- 模块 -->
			<view class="model">
				<view style="text-align: center;" @click="model_s(item)" v-for="(item,index) in model" :key="index">
					<image :src="item.image" mode=""></image>
					<text>{{item.text}}</text>
				</view>
			</view>
			<swiper :indicator-dots="true" :autoplay="true" :duration="500" :circular="true" indicator-color="#fff">
				<!-- 循环渲染轮播图的 item 项 -->
				<swiper-item v-for="(item, i) in swiperList" :key="i">
					<view class="swiper-item">
						<image :src="url1+item.picture" mode=""></image>
					</view>
				</swiper-item>
			</swiper>
			<!-- 月卡入口 -->
			<!-- <navigator url="./card/index"
				style="display:flex;align-items: center;text-align: right;justify-content: space-between;padding: 0 15rpx;">
				<div class="card-submit"
					style="padding: 5px 5px;border-radius: 5px;background: linear-gradient(to right,red,orange);display: flex;justify-content: space-between;align-items: center;width: 100%;">
					<span
						style="border-radius: 5px;color: orangered;background: beige;width: 45%;height: 60px;line-height: 60px;display: inline-block;font-size: 20px;padding: 0 10px;text-align: center;">
						优惠劵、月卡
					</span>
					<p
						style="display: flex;flex-flow: column;align-items: center;font-size: 20px;color: white;padding: 0 10px;">
						点我进入<span style="font-size: 10px;color: azure;">大额优惠劵、不限各种美食等</span>
					</p>
				</div>
			</navigator> -->
			<!-- 今日特价 -->
			<view v-if="todayfoot!=''">
				<h1>今日特价</h1>
				<u-scroll-list :indicator="false" indicatorColor="#fff0f0" indicatorActiveColor="#f56c6c">
					<view class="todayFood" v-for="(item, index) in todayfoot" :key="index">
						<image style="width: 206rpx;height: 192rpx;" :src="url1+item.goodsimage" mode=""></image>
						<view class="tfoot">
							<text class="goodsname">{{item.goodsname}}</text>
							<view class="goodsdetail">
								<view class="goodsprice">
									<text>￥{{item.salesprice}}</text>
									<text>￥{{item.goodprice}}</text>
								</view>
								<view class="button" @click="rob(item)">马上抢</view>
							</view>
						</view>
					</view>
				</u-scroll-list>
			</view>
			<h1>食堂商家</h1>
			<view class="canteen">
				<view class="vie1" @click="jinpeng(item)" v-for="(item,index) in shop" :key="index">
					<image class="img3" :src="url1+item.logo"></image>
					<view class="vie2">
						<view class="h2">
							<h2>{{item.name}}</h2>
							<label :class="item.status==1?'on':'no'">{{item.status==1?'营业中':'休息中'}}</label>
						</view>
						<label>
							<u-icon size="14px" name="star-fill" color="#ffdb0f" label="5.0" space="0" labelSize="12px">
							</u-icon>
							<text style="margin-left: 58rpx;">已售{{item.sale}}单</text>
						</label>
						<label>
							起送价￥{{item.min_price}} | {{item.delivery_name}}￥{{item.delivery_price}}
						</label>
					</view>
				</view>
				
				
			</view>
			<view :style="{paddingBottom:paddingBottom+'px'}">
				<u-loadmore :status="loading" :loading-text="loadingText" :nomoreText="nomoreText" marginBottom='0'
					marginTop="0" />
			</view>
			
		</view>
		<!-- 优惠卷弹窗 -->
		<u-popup closeIconPos="bottom" :safeAreaInsetBottom="false" :show="coupon" mode="center" :round="10"
			@close="close_1" @open="open" :closeable="true" :closeOnClickOverlay="false" bgColor="transparent">
			<view style="max-height: 66vh;overflow-y: scroll;">
				<view class="coupon" v-for="(item,index) in couponList" :key="index" @click="addcoupon(item)">
					<image src="../static/honbao.png" mode="widthFix"></image>
					<view class="honbao">
						<text>满{{item.full_money}}可用</text>
						<view>截至日期{{item.end_time}}</view>
					</view>
					<text><text style="font-size: 40rpx;">￥</text>{{parseFloat(item.discount_money)}}</text>
				</view>
			</view>
		</u-popup>
		<tabBar :current="current" ref="son"></tabBar>
	</view>
</template>
<script>
	import qqmapsdk from '@/libs/qqmap-wx-jssdk.js'
	export default {
		data() {
			return {
				current: 0,
				swiperList: [],
				url1: 'http://waimai.com',
				shop: [],
				stock: [],
				show: false,
				todayfoot: [],
				school: [],
				dinwei: uni.getStorageSync('dinwei'),
				page: 1, //当前页
				loading: '',
				loadingText: '',
				nomoreText: '',
				degree: '',
				weather: '',
				coupon: false,
				couponList: [], //优惠卷
				model: [{
					image: 'http://waimai.com/static/img/type_6.png',
					text: '美食',
					src: ''
				}, {
					image: 'http://waimai.com/static/img/type_7.png',
					text: '甜品甜点',
					src: ''
				}, {
					image: 'http://waimai.com/static/img/type_8.png',
					text: '奶茶饮料',
					src: ''
				}, {
					image: 'http://waimai.com/static/img/type_9.png',
					text: '汉堡快餐',
					src: ''
				}, {
					image: 'http://waimai.com/static/img/type_10.png',
					text: '便当简餐',
					src: ''
				}, {
					image: 'http://waimai.com/static/img/type_1.png',
					text: '会员储值',
					src: ''//../pages-other/card/index
				}, {
					image: 'http://waimai.com/static/img/type_2.png',
					text: '周边服务',
					src: ''//../pages-other/supermarket/index
				}, {
					image: 'http://waimai.com/static/img/type_3.png',
					text: '数码产品',
					src: '../pages-other/electronics/index'
				}, {
					image: 'http://waimai.com/static/img/type_4.png',
					text: '闲置物品',
					src: '../pages-other/idle/index'
				}, {
					image: 'http://waimai.com/static/img/type_5.png',
					text: '兼职培训',
					src: '../pages-other/train/index'
				}],
				paddingBottom:0
			}
		},
		onLoad() {
			// 校区
			uni.$u.http.post('api/home/getcampus', {}).then(res => {
				// console.log(res.data.data)
				this.school = res.data.data
			})
			// 定位
			this.location()
			this.height()
		},
		methods: {
			height(){
				let _this = this
				let data = this.$refs.son.tarbarHeight()
				data.boundingClientRect(function(res) { //data - 各种参数
					_this.paddingBottom = res.height
				}).exec()
			},
			onReachBottom: function() {
				var _this = this
				_this.page = _this.page * 1 + 1
				uni.$u.http.post('api/home/getstore', {
					campus_id: uni.getStorageSync('campus_id'),
					page: _this.page
				}).then(res => {
					if (res.data.code == 200) {
						_this.shop = _this.shop.concat(res.data.data) //将放回结果放入content
						uni.setStorageSync('store', res.data.data)
						_this.loading = 'loading'
						_this.loadingText = '商家正在赶来'
					} else {
						_this.loading = 'nomore'
						_this.nomoreText = '往下已经没有咯'
					}
				})
			},
			// 定位
			location() {
				var _this = this
				uni.getLocation({
					type: 'gcj02',
					geocode: true,
					altitude: true,
					success: (res) => {
						// console.log("获取经纬度成功",res);
						_this.latitude = res.latitude;
						_this.longitude = res.longitude;
						_this.distanced()
					},
					fail: (error) => {
						// console.log("获取经纬度失败",error);
						uni.showModal({
							content: '部分功能暂时无法使用,是否开启定位功能?',
							confirmText: "确认",
							cancelText: "取消",
							success(res) {
								if (res.confirm) {
									wx.openSetting({
										success(res) {
											_this.location()
										}
									})
								}
							}
						})
					}
				})
			},
			distanced(){
				let _this = this
				const QQMapWX = new qqmapsdk({
					key: '7Q6BZ-MWYCW-PKCRD-OSX7N-CCZMT-DBBHH'
				});
				// 解析地址
				QQMapWX.reverseGeocoder({
					location: {
						latitude: _this.latitude,
						longitude: _this.longitude
					},
					success: function(res) {
						// 获取天气
						uni.request({
							url: 'https://wis.qq.com/weather/common',
							data: {
								source: 'xw',
								weather_type: 'observe|alarm|air|forecast_1h|forecast_24h|index|limit|tips|rise',
								province: res.result.address_component
									.province, //省
								city: res.result.address_component.city, //市
								county: res.result.address_component.district //县
							},
							success(res) {
								_this.degree = res.data.data.observe.degree //温度
								_this.weather = res.data.data.observe.weather //天气
							}
						})
						// 当前位置开始
						const latitude = res.result.location.lat + ',' + res.result.location.lng
						const lats = latitude.toString()
						// 当前位置结束
						//校区经纬度
						let array = [];
						for (let index in _this.school) {
							array.push(_this.school[index].lat + ',' + _this.school[index]
								.lon)
						}
						let longitude = array.join(';')
						//校区经纬度
						uni.request({
							url: 'https://apis.map.qq.com/ws/distance/v1/matrix', //仅为示例，并非真实接口地址。
							method: 'GET',
							data: {
								mode: 'walking',
								from: lats,
								to: longitude,
								key: '7Q6BZ-MWYCW-PKCRD-OSX7N-CCZMT-DBBHH' //获取key
							},
							success: (res) => {
								const min = res.data.result.rows[0].elements; //拿到距离(米)
								for (let i in min) {
									if (min[i].distance < 1000) {
										min[i].distance = Math.round(min[i]
											.distance) + 'm';
									} else {
										min[i].distance = (min[i].distance /
											2 / 500).toFixed(2) + 'km'
									}
									let newarr = [];
									_this.school.map((item, index) => {
										newarr.push(Object.assign(
											item, {
												distance: 0
											})) //自定义属性和值
									})
									for (let i in newarr) {
										newarr[i].distance = min[i].distance
									}
									_this.school = newarr;
								}
								let min_distance = Math.min.apply(Math, _this
									.school.map(item => {
										return parseFloat(item
											.distance)
									})) //最小距离
								for (let i in _this.school) {
									if (parseFloat(_this.school[i].distance) == min_distance) {
										// 轮播图
										_this.getcarousel(_this.school[i].campus_id)
										// 店铺
										_this.getstore(_this.school[i].campus_id)
										//今日特价
										_this.getgoodsp(_this.school[i].campus_id)
										// 获取优惠卷
										_this.getcoupon(_this.school[i].campus_id)
										_this.updateusercampus(_this.school[i].campus_id)
										if (uni.getStorageSync('campus_id') == '') {
											uni.setStorageSync('campus_id',_this.school[i].campus_id)
											uni.setStorageSync('dinwei', _this.school[i].name)
											_this.dinwei = _this.school[i].name
										}
									}
								}
							}
						});
					},
					fail: function(res) {
						// uni.showToast({
						// 	title: '定位失败',
						// 	duration: 2000,
						// 	icon: "none"
						// })
					},
				})
			},
			// 今日特价
			getgoodsp(e) {
				uni.$u.http.post('api/home/getgoodsp', {
					campus_id: uni.getStorageSync('campus_id') == '' ? e : uni.getStorageSync('campus_id')
				}).then(res => {
					this.todayfoot = res.data.data
				}).catch(err => {
					uni.showToast({
						title: '今日特价接口出错',
						icon: 'none'
					})
				})
			},
			// 店铺
			getstore(e) {
				uni.$u.http.post('api/home/getstore', {
					campus_id: uni.getStorageSync('campus_id') == '' ? e : uni.getStorageSync('campus_id')
				}).then(res => {
					this.shop = res.data.data
				}).catch(err => {
					uni.showToast({
						title: '购买店铺接口出错',
						icon: 'none'
					})
				})
			},
			// 轮播
			getcarousel(e) {
				uni.$u.http.post('api/home/getcarousel', {
					campus_id: uni.getStorageSync('campus_id') == '' ? e : uni.getStorageSync('campus_id')
				}).then(res => {
					this.swiperList = res.data.data
				}).catch(err => {
					uni.showToast({
						title: '轮播接口出错',
						icon: 'none'
					})
				})
			},
			// 获取优惠卷
			getcoupon(e) {
				uni.$u.http.post('api/home/getcoupon', {
					campus_id: uni.getStorageSync('campus_id') == '' ? e : uni.getStorageSync('campus_id'),
					user_id: uni.getStorageSync('user').user_id
				}).then(res => {
					this.couponList = res.data.data
					if (res.data.data != undefined) {
						this.coupon = true
					} else {
						this.coupon = false
					}
				}).catch(err => {
					uni.showToast({
						title: '获取优惠卷接口出错',
						icon: 'none'
					})
				})
			},
			// 校区选择
			updateusercampus(value) {
				let _this = this
				uni.$u.http.post('api/home/updateusercampus', {
					campus_id: value.campus_id == undefined ? value : value.campus_id,
					user_id: uni.getStorageSync('user').user_id
				}).then(res => {
					if (res.data.code == 200) {
						if(value.campus_id != undefined){
							uni.setStorageSync('campus_id', value.campus_id);
							uni.setStorageSync('dinwei', value.name);
							_this.dinwei = value.name;
						}
						_this.page = 1;
						_this.getstore();
						_this.getcarousel();
						_this.getgoodsp();
						_this.getcoupon();
						_this.show = false;
					}
				}).catch(err => {
					uni.showToast({
						title: '校区选择接口出错',
						icon: 'none'
					})
				})
			},
			// 今日特价
			rob(item) {
				uni.navigateTo({
					url: './food/jinpengshitang?store_id=' + item.store_id + "&good_id=" + item.good_id
				})
			},
			// 食堂商家
			jinpeng(item) {
				uni.navigateTo({
					url: './food/jinpengshitang?store_id=' + item.id
				})
			},
			// 领取优惠卷
			addcoupon(item) {
				uni.$u.http.post('api/home/addcoupon', {
					coupon_id: item.id,
					user_id: uni.getStorageSync('user').user_id,
					campus_id: uni.getStorageSync('campus_id')
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '领取成功',
							icon: 'none',
							duration: 2000
						})
						this.coupon = false
					} else {
						uni.showToast({
							title: res.data.msg,
							icon: 'none',
							duration: 2000
						})
						this.coupon = false
					}
				}).catch(err => {
					uni.showToast({
						title: '领取优惠卷接口出错',
						icon: 'none'
					})
				})
			},
			close_1() {
				this.coupon = false
			},
			// 搜索
			custom(index) {
				if(!index){
					uni.showToast({
						title:'请输入关键字',
						icon:'error'
					})
				}else{
					uni.navigateTo({
						url: './search?keyword=' + index
					})
				}
			},
			open() {
				// console.log('open');
			},
			// 关闭选择校区
			close() {
				this.show = false
			},
			model_s(e) {
				if (e.src == '') {
					uni.showToast({
						title: '功能未开通',
						icon: 'none',
						duration: 3000
					})
					return
				}
				uni.navigateTo({
					url: e.src
				})
			}
		},
	}
</script>

<style lang="scss">
	page {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.u-loadmore {
		padding-bottom: 24rpx;
	}

	.bg-color {
		border-top-left-radius: 60rpx;
		border-top-right-radius: 60rpx;
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
		.model {
			display: flex;
			align-items: center;
			padding-bottom: 46rpx;
			justify-content: space-between;
			flex-wrap: wrap;
		
			view {
				width: 20%;
				display: flex;
				flex-flow: column;
				align-items: center;
				margin-top: 46rpx;
			}
		
			text {
				margin-top: 8rpx;
				font-size: 23rpx;
				display: inline-block;
			}
		
			image {
				width: 80rpx;
				height: 80rpx;
			}
		}
		.canteen {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
		}
		h1 {
			font-weight: bold;
			margin: 25rpx 38rpx;
			display: inline-block;
			position: relative;
			padding-left: 34rpx;
			font-size: 26rpx;
			color: #101010;
		}
		
		h1::before {
			content: '';
			position: absolute;
			left: 0;
			width: 30rpx;
			height: 30rpx;
			background: #000000;
			border-radius: 50%;
		}
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.u-popup__content__close {
		bottom: -26px;
		left: 45%;
	}

	.u-popup__content__close text {
		color: white !important;
	}

	.coupon {
		width: 476rpx;
		color: white;
		display: flex;
		flex-flow: column;
		position: relative;
		margin-bottom: 10rpx;

		image {
			width: 100%;
		}

		.honbao {
			position: absolute;
			top: 25rpx;
			left: 20rpx;
			display: flex;
			flex-flow: column;

			text {
				font-size: 38rpx;
				color: #FCEDD0;
				margin-bottom: 10rpx;
			}

			view {
				font-size: 18rpx;
				color: #FCEDD0;
				margin-bottom: 10rpx;
			}


		}

		>text {
			position: absolute;
			color: #DF0018;
			font-size: 145rpx;
			top: 50%;
			right: 10%;
			transform: translate(0, -50%);
		}
	}

	.u-scroll-list {
		margin-left: 38rpx;
		padding-bottom: 0 !important;
	}

	.todayFood {
		margin: 8rpx;
		box-shadow: 0rpx 0rpx 6rpx 0rpx rgba(0, 0, 0, 0.23);
		border-radius: 10rpx;
	}

	.tfoot {
		padding: 6rpx;
		background: white;
		display: flex;
		flex-flow: column;

		.goodsname {
			font-size: 25rpx;
			font-weight: 400;
			color: #292929;
		}

		.goodsdetail {
			display: flex;
			justify-content: space-between;

			.goodsprice {
				display: flex;
				align-items: flex-end;
				justify-content: space-between;

				text:first-child {
					font-size: 30rpx;
					font-weight: bold;
					color: #CB0200;
					margin-right: 4rpx;
					font-family: '黑体';
				}

				text:last-child {
					font-size: 25rpx;
					font-weight: 500;
					text-decoration: line-through;
					color: #A2A2A2;
					font-family: '黑体';
				}
			}

			.button {
				width: 66rpx;
				height: 32rpx;
				line-height: 32rpx;
				background: #CB0200;
				border-radius: 6rpx;
				font-size: 16rpx;
				font-weight: 400;
				color: #FFFFFF;
				text-align: center;
			}
		}
	}

	.list {
		display: flex;
		flex-flow: column;
		background: linear-gradient(to right, burlywood, orangered);
		color: white;
		padding: 40rpx;
		font-size: 25rpx;
		border-bottom: 2rpx solid rgba(0, 0, 0, .2);
	}

	.dinwei {
		background: linear-gradient(to right, #fedb74, #ffbf80);
		font-size: 26rpx;
		font-weight: bold;
		color: #101010;

		padding: 0 15rpx 15rpx 15rpx;
		display: flex;
		align-items: center;
		justify-content: space-between;

		text {
			font-size: 29rpx;
		}
		.temperature {
			font-size: 26rpx;
			font-weight: bold;
			color: #101010;
		}
	}

	.search {
		padding: 0 15rpx 15rpx 15rpx;
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.img2 {
		width: 100rpx;
		height: 100rpx;
	}

	.on {
		width: 60rpx;
		height: 28rpx;
		background: #FFEF9D;
		border: 2rpx solid #FF8C00;
		border-radius: 6rpx;
		font-size: 14rpx;
		font-family: Source Han Sans SC;
		font-weight: bold;
		color: #FF8C00;
		line-height: 28rpx;
		text-align: center;
	}

	.no {
		width: 60rpx;
		height: 28rpx;
		border: 2rpx solid #9B9B9B;
		border-radius: 6rpx;
		font-size: 14rpx;
		font-family: Source Han Sans SC;
		font-weight: bold;
		color: #9B9B9B;
		line-height: 28rpx;
		text-align: center;
	}

	ul {
		display: flex;
		align-items: center;
		justify-content: space-around;
	}

	li {
		display: flex;
		flex-direction: column;
		text-align: center;
		font-size: 28rpx;
	}

	.vie1 {
		background: white;
		border-radius: 10rpx;
		overflow: hidden;
		width: 43.5%;
		margin-bottom: 24rpx;
		margin-left: 38rpx;
		box-shadow: 0 0 16rpx 0 rgba(0, 0, 0, 0.15);
		.img3 {
			width: 100%;
			height: 190rpx;
		}
	}

	.vie1:nth-child(2n) {
		margin-left: 0;
		margin-right: 38rpx;
	}

	.vie2 {
		display: flex;
		flex-direction: column;
		padding: 16rpx;

		>label {
			margin-top: 14rpx;
			font-size: 20rpx;
			color: #ADADAD;
			display: flex;
			align-items: center;
		}
	}


	.vie22 {
		margin-bottom: 130rpx;
	}

	.h2 {
		font-size: 30rpx;
		font-weight: bold;
		color: #101010;
		display: flex;
		align-items: center;
		justify-content: space-between;

		h2 {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			flex: 1;
		}
	}

	swiper {
		height: 238rpx;
		overflow: hidden;
		margin: auto;
		border-radius: 30rpx;
		margin: 0 40rpx;
	}

	swiper-item{
		height: 240rpx!important;
	}
	.swiper-item{
		width: 100%;
		height: 100%;
	}
	image {
		width: 100%;
		height: 100%;
		text-align: center;
		border-radius: 10rpx;
	}
</style>
