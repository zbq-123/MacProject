<template>
	<view>
		<u-sticky>
			<u-navbar title="订单" bgColor="transparent" leftIcon=" " :fixed="true" :placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
			</u-navbar>
			<u-tabs :list="tabs" lineWidth="43" :activeStyle="{color:'#FF8E1E'}" :inactiveStyle="{color:'#908E8E'}"
				lineColor="#FF8E1E" @click="tab">
			</u-tabs>
		</u-sticky>
		<view class="bg" :style="{paddingBottom:marginBottom+'px'}">
			<view v-for="(item_1,index_1) in tabs" :key="index_1" v-show="item_1.status==status">
				<view v-for="(item,index) in item_1.select" :key="index" class="orderlist">
					<view class="store_name">
						<image :src="url1+item.logo" mode=""></image>
						<view class="name">
							<view><text>{{item.store_name}}</text>
								<u-icon name="arrow-right" size="12" color="#000"></u-icon>
							</view>
							<text>{{item.create_time}}</text>
						</view>

						<view class="pay_status" v-if="item.status==1&&item.pay_status==1">未支付</view>
						<view class="pay_status" v-else-if="item.status==2&&item.pay_status==2">已支付</view>
						<view class="pay_status" v-else-if="item.status==8&&item.pay_status==2">已退款</view>
						<view class="pay_status" v-else="item.status==14">已取消</view>
					</view>
					<view class="order_detail" @click="detail(item)">
						<view class="row">
							<view class="goods" v-for="(item_1,index_1) in item.goods" :key="index_1">
								<image :src="url1+item_1.image" style="width: 100rpx;height: 100rpx;"></image>
								<text>{{item_1.name}}</text>
							</view>
						</view>
						<view class="price">
							<text
								style="color: #000000;font-size: 48rpx;font-weight: bold;">￥{{item.total_price}}</text>
							<text style="color: #908E8E;font-size: 24rpx;">共{{item.count}}件</text>
						</view>
					</view>
					<view class="btn_4">
						<text @click="del(item)">删除订单</text>
						<view class="">
							<view class="again btn" @click="again(item)">再来一单</view>
							<view class="cancel_order btn" @click="cancel_order(item)"
								v-if="item.status==14||item.status==8||item.pay_status==2?false:true">
								取消订单</view>
							<view class="pay btn" @click="pay(item)"
								v-if="item.status==14||item.status==8||item.pay_status==2?false:true">
								去支付</view>
							<block v-if="item.pay_status==2">
								<block v-if="item.status!=8">
									<!-- {{nowTimer | formatDate}} -->
									<!-- <view class="btn" @click="right_now(item1)">立即退款</view> -->
									<view class="apply btn" @click="apply(item)">申请退款</view>
								</block>
							</block>
						</view>
					</view>
				</view>
			</view>
		</view>
		<u-loadmore :status="loading" :loading-text="loadingText" :nomoreText="nomoreText" />
		<tabBar :current="current" ref="son"></tabBar>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				current: 1,
				url1: 'http://waimai.com',
				page: 1, //当前页
				loading: '',
				loadingText: '',
				create_time: '2022-4-7 17:15:00',
				nowTimer: 0,
				status: 0,
				tabs: [{
					status: 0,
					name: '全部订单',
					select: []
				}, {
					status: 1,
					name: '未支付',
					select: []
				}, {
					status: 2,
					name: '已支付',
					select: []
				}, {
					status: 8,
					name: '已退款',
					select: []
				}, {
					status: 14,
					name: '已取消',
					select: []
				}],
				marginBottom:0
			}
		},
		onShow() {
			this.page = 1
			this.order_list()
			// uni.startPullDownRefresh();
			this.height()
		},
		// 上拉加载更多数据
		onReachBottom: function() {
			var _this = this
			_this.page = _this.page * 1 + 1
			uni.$u.http.post('api/users/getuserorderlist', {
				user_id: uni.getStorageSync('user').user_id,
				page: _this.page
			}).then(res => {
				// console.log(res.data.code)
				if (res.data.code == 200) {
					_this.tabs.forEach(el => {
						if (el.status == 1) { //未支付
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 1 && res.data.orderdata[item]
									.pay_status == 1) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 2) { //已支付
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 2 && res.data.orderdata[item]
									.pay_status == 2) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 8) { //已退款
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 8 && res.data.orderdata[item]
									.pay_status == 2) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 14) { //已取消
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 14) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else { //全部订单
							for (let item = 0; item < res.data.orderdata.length; item++) {
								el.select.push(res.data.orderdata[item])
							}
						}
					})
					_this.loading = 'loading'
					_this.loadingText = '正在加载更多订单'
				} else {
					_this.loading = 'nomore'
					_this.nomoreText = '往下已经没有咯'
				}
			}).catch(err => {
				console.log(err)
			})
		},
		created() {
			this.nowTimer = new Date(this.create_time).getTime()
		},
		filters: {
			formatDate: (nowTimer) => {
				//获取当前时间戳
				let now = Date.now();
				let seconds = Math.floor((now - parseInt(nowTimer)) / 1000);
				let minutes = Math.floor(seconds / 60);
				let hours = Math.floor(minutes / 60);
				let days = Math.floor(hours / 24);
				let months = Math.floor(days / 30);
				let years = Math.floor(months / 12);
				let oldValue = 0;
				oldValue = seconds === 0 ? (seconds = 1) : seconds;
				return oldValue
			}
		},
		methods: {
			height(){
				let _this = this
				let data = this.$refs.son.tarbarHeight()
				data.boundingClientRect(function(res) { //data - 各种参数
					_this.marginBottom = res.height
				}).exec()
			},
			order_list() {
				// uni.showLoading({
				// 	title: '加载中'
				// })
				uni.$u.http.post('api/users/getuserorderlist', {
					page: this.page,
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					this.tabs.forEach(el => {
						el.select = []
						if (el.status == 1) { //未支付
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 1 && res.data.orderdata[item]
									.pay_status == 1) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 2) { //已支付
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 2 && res.data.orderdata[item]
									.pay_status == 2) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 8) { //已退款
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 8 && res.data.orderdata[item]
									.pay_status == 2) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else if (el.status == 14) { //已取消
							for (let item = 0; item < res.data.orderdata.length; item++) {
								if (res.data.orderdata[item].status == 14) {
									el.select.push(res.data.orderdata[item])
								}
							}
						} else { //全部订单
							for (let item = 0; item < res.data.orderdata.length; item++) {
								el.select.push(res.data.orderdata[item])
							}
						}
					})
					// uni.hideLoading()
				})
			},
			tab(e) {
				this.page = 1
				this.status = e.status
				this.order_list()
			},
			// 刷新页面
			onPullDownRefresh() {
				setTimeout(function() {
					uni.stopPullDownRefresh();
				}, 1000);
			},
			// 再来一单
			again(e) {
				if (e.type == 1) {
					uni.navigateTo({
						url: './food/jinpengshitang?store_id=' + e.store_id + "&again=" + JSON.stringify(e.goods)
					})
				} else {
					uni.navigateTo({
						url: '../pages-other/electronics/dateil?store_id=' + e.store_id + "&again=" + JSON.stringify(e.goods)
					})
				}
			},
			del(e) {
				var _this = this
				uni.$u.http.post('api/orders/del_orders', {
					order_number: e.order_number,
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					uni.showToast({
						title: res.data.msg,
						icon: 'none',
						duration:1000
					})
					setTimeout(function(){
						_this.page = 1
						_this.order_list()
					},1000)
				})
			},
			//取消订单
			cancel_order(e) {
				uni.$u.http.post('api/orders/cancel_orders', {
					order_number: e.order_number,
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '已取消',
							icon: 'none'
						})
						e.status = 14
					} else {
						uni.showToast({
							title: '取消失败',
							icon: 'none'
						})
					}
				})
			},
			// 立即支付
			pay(e) {
				uni.$u.http.post('api/orders/wxpay_goods_orders', {
					order_number: e.order_number,
					user_id: uni.getStorageSync('user').user_id
				}).then(ress => {
					console.log(ress)
					uni.requestPayment({
						provider: 'wxpay',
						timeStamp: ress.data.data.timeStamp,
						nonceStr: ress.data.data.nonceStr,
						package: ress.data.data.package,
						signType: ress.data.data.signType,
						paySign: ress.data.data.paySign,
						success: function(ress) {
							console.log(ress)
							// console.log('success:' + JSON.stringify(res));
							uni.showToast({
								title: '支付成功',
								icon: 'success'
							})
						},
						fail: function(err) {
							uni.showToast({
								title: '支付失败',
								icon: 'none'
							})
						}
					});
				})
			},
			detail(e) {
				uni.navigateTo({
					url: './food/dindanDetail?data=' + JSON.stringify(e)
				})
			},
			// 立即退款
			// right_now(item1) {
			// 	uni.$u.http.post('api/order/wxpayShopRefund', {
			// 		order_id: uni.getStorageSync('order_id'),
			// 		user_id: uni.getStorageSync('user').user_id,
			// 		module: 2
			// 	}).then(res => {
			// 		console.log(res)
			// 		uni.showToast({
			// 			title: '正在退款中',
			// 			icon: 'loading'
			// 		})
			// 		item1.pay_status = 3
			// 	})
			// },
			// 申请退款
			apply(item1) {
				uni.$u.http.post('api/orders/apply_refund', {
					order_number: uni.getStorageSync('order_number'),
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					uni.showToast({
						title: '正在退款中',
						icon: 'loading'
					})
					item1.pay_status = 3
				})
			}
		}
	}
</script>

<style lang="scss">
	.u-tabs {
		background: white;
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.orderlist {
		width: 93%;
		margin: auto;
		padding: 16rpx;
		border-radius: 20rpx;
		margin-bottom: 12rpx;
		margin-top: 18rpx;
		background: white;
		box-shadow: 0rpx 0rpx 23rpx 0rpx rgba(0, 0, 0, 0.12);

		.store_name {
			font-weight: bold;
			color: #333333;
			position: relative;
			display: flex;
			align-items: center;
			height: 79rpx;

			.pay_status {
				position: absolute;
				top: 25%;
				right: 0%;
				font-size: 24rpx;
				color: #302E2E;
			}

			image {
				width: 79rpx;
				height: 100%;
				margin-right: 14rpx;
			}

			.name {
				display: flex;
				justify-content: space-between;
				flex-flow: column;
				height: 100%;

				view {
					display: flex;
					align-items: center;

					text {
						font-size: 34rpx;
						font-weight: 500;
						color: #000000;
					}
				}

				>text {
					font-size: 20rpx;
					font-weight: 400;
					color: #606060;
				}
			}
		}
	}

	page {
		height: 100vh;
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
	}

	.order_detail {
		display: flex;
		justify-content: space-between;

		.row {
			width: 100%;
			display: flex;
			padding: 20rpx 0 20rpx 20rpx;
			overflow-x: scroll;

			.goods {
				width: 25%;
				margin-right: 20rpx;
				display: flex;
				flex-flow: column;
				align-items: center;

				text {
					font-size: 20rpx;
					font-family: Adobe Heiti Std;
					font-weight: normal;
					color: #383737;
				}
			}

			.goods:last-child {
				margin-right: 0;
			}
		}

		.price {
			margin: 20rpx;
			display: flex;
			flex-flow: column;
			align-items: center;
			justify-content: center;
		}
	}

	.btn_4 {
		display: flex;
		margin: 8rpx;
		justify-content: space-between;
		align-items: center;

		>text {
			font-size: 20rpx;
			color: #D42619;
		}

		>view {
			display: flex;

			.btn {
				width: 98rpx;
				height: 40rpx;
				line-height: 40rpx;
				text-align: center;
				font-weight: normal;
				border-radius: 20rpx;
				font-size: 20rpx;
				margin-left: 26rpx;
			}

			.again {
				background: #FFFFFF;
				border: 1px solid #FFA842;
				color: #FFA842;
			}

			.cancel_order {
				background: #F2EFEF;
				border: 1px solid #DBD8D8;
				color: #757171;
			}

			.pay {
				background: linear-gradient(90deg, #FFD43F, #FFA453);
				color: #FFFFFF;
			}

			.apply {
				background: #FFFFFF;
				border: 1px solid #FFA842;
				color: #FFA842;
			}
		}
	}
</style>
