<template>
	<view>
		<u-navbar title=" " :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="goods">
			<navigator url="../address/wodedizhi">
				<view style="display: flex;align-items: center;justify-content: space-between;">
					<view style="display: flex;flex-flow: column;">
						<text
							style="font-size: 30rpx;font-weight: bold;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">{{address}}</text>
						<text
							style="font-size: 25rpx;margin-top: 10rpx;color: gray;">{{address_name}}{{item.gender==0?'男士':'女士'}}{{address_phone}}</text>
					</view>
					<u-icon name="arrow-right" color="#808080"></u-icon>
				</view>
			</navigator>
		</view>

		<view class="goods">
			<view @click="on_page" class="store">
				<text>{{shanjia_name}}</text>
				<u-icon name="arrow-right" color="#808080"></u-icon>
			</view>
			<view class="foot" v-for="(item,index) in shop" :key="index" v-if="item.num!=0?true:false">
				<image :src="url1+item.image"></image>
				<view class="foot_name">
					<view class="foot_1">
						<text>{{item.name}}</text>
						<text class="foot_title" v-if="item.difference">{{item.difference}}</text>
						<text class="foot_title">×{{item.goods_num}}</text>
					</view>
					<view class="foot_2" v-if="!item.salesprice">
						￥{{item.price}}</view>
					<view class="foot_2" v-else>
						￥{{item.salesprice}}<text
							style="color: #a1a1a1;text-decoration:line-through;">￥{{item.price}}</text>
					</view>
				</view>
			</view>
		</view>

		<view class="goods">
			<text
				style="font-weight: bold;display:block;;font-size: 28rpx;color: #333333;margin-bottom: 22rpx;">备注</text>
			<u--textarea autoHeight v-model="remake" placeholder="比如:加辣"></u--textarea>
		</view>

		<view class="goods">
			<!-- <view class="row">
				打包费<text>￥{{box_price}}</text>
			</view>
			<view class="row">
				配送费<text>￥{{delivery_price}}</text>
			</view> -->
			<!-- 优惠卷 -->
			<!-- <block v-if="couponList!=''">
				<view class="row">
					满减优惠券<text @click="show = true">{{select=='请选择'?select:'-'+select}}</text>
				</view>
				<u-popup :show="show" @close="close" mode="center" bgColor="transparent">
					<u-radio-group placement="column">
						<view class="coupon" v-for="(item,index) in couponList" :key="index"
							@click="checkboxChange(item)">
							<text>有效期至{{item.end_time}}</text>
							<view class="right">
								<h1>￥{{item.discount_money}}</h1>
								<text>满{{item.full_money}}可用</text>
							</view>
							<u-radio name="index" :checked="item.checked"></u-radio>
						</view>
					</u-radio-group>
				</u-popup>
			</block> -->
			<view style="text-align: right;font-weight: bold;font-size: 28rpx;color:#333333;">
				<view style="color:#333333;">小计 ￥{{manyprice}}</view>
			</view>
		</view>
		<view class="pay">
			<text class="name">支付方式</text>
			<text class="result">微信支付</text>
		</view>
		<view class="br"></view>
		<view class="submit">
			<view class="name">应支付:￥{{manyprice}}</view>
			<view class="btn" @click="submit">提交订单</view>
		</view>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				show: false,
				shop: [],
				url1: 'http://waimai.com',
				length: '',
				zongshu: '',
				delivery_price: '',
				box_price: '',
				shanjia_name: '',
				address: '',
				address_name: '',
				address_phone: '',
				gender: '',
				address_id: '',
				remake: '',
				couponList: [],
				select: '请选择',
				manyprice: ''
			};
		},
		// computed: {
		// 	newArr: function() {
		// 		return JSON.parse(JSON.stringify(this.couponList))
		// 	}
		// },
		// watch:{
		// 	newArr: {
		// 		handler: function(val, oldval){
		// 			console.log(val, oldval)
		// 		},
		// 		deep: true
		// 	}
		// },
		onLoad(options) {
			this.length = JSON.parse(Object.values(options)).length
			this.shop = JSON.parse(Object.values(options))
			this.manyprice = this.zongshu = uni.getStorageSync('total')
			this.delivery_price = JSON.parse(Object.values(options))[0].delivery_price
			this.shanjia_name = JSON.parse(Object.values(options))[0].shanjia_name
			this.box_price = JSON.parse(Object.values(options))[0].box_price
			uni.setStorageSync('shanjia_name', JSON.parse(Object.values(options))[0].shanjia_name)
			// 默认地址
			uni.$u.http.post('api/users/getuseraddresslist', {
				user_id: uni.getStorageSync('user').user_id,
			}).then(res => {
				for (let index in res.data.data) {
					if (res.data.data[index].is_default == 1) {
						this.address = res.data.data[index].delivery_address
						this.address_name = res.data.data[index].delivery_name
						this.address_phone = res.data.data[index].delivery_phone
						this.gender = res.data.data[index].gender
						this.address_id = res.data.data[index].address_id
						return
					} else {
						this.address = '请选择一个地址'
					}
				}
			})
			// 获取优惠卷
			uni.$u.http.post('api/users/getusercunponlist', {
				user_id: uni.getStorageSync('user').user_id,
				campus_id: uni.getStorageSync('campus_id')
			}).then(res => {
				// console.log(res)
				let checked = {
					checked: false
				}
				res.data.data.forEach(el => {
					Object.assign(el, checked)
				})
				// 判断是否总金额是否达标满减要求
				res.data.data.forEach(el => {
					if (this.zongshu >= parseInt(el.full_money)) {
						this.couponList.push(el)
					}
				})
			}).catch(err => {
				console.log('400', err)
				uni.showToast({
					title: '获取优惠卷接口出错',
					icon: 'none'
				})
			})
			// 获取月卡
			uni.$u.http.post('api/orders/wxpay_card', {
				user_id: uni.getStorageSync('user').user_id
			}).then(res => {
				// console.log(res)
			}).catch(err => {
				console.log('400', err)
				uni.showToast({
					title: '获取月卡接口出错',
					icon: 'none'
				})
			})
		},
		methods: {
			checkboxChange(e) {
				// console.log(e)
				this.select = e.discount_money
				this.manyprice = this.zongshu - e.discount_money
				this.show = false
				this.use_coupon = e.id
				this.couponList.forEach(el => {
					if (e.id === el.id) {
						el.checked = true
					} else {
						el.checked = false
					}
				})
			},
			//接收地址
			otherFun(object) {
				if (!!object) {
					this.address = object.delivery_address
					this.address_name = object.delivery_name
					this.address_phone = object.delivery_phone
					this.address_id = object.address_id
				}
			},
			close() {
				this.show = false
			},
			// 提交订单
			submit() {
				if (!this.address_id) {
					uni.showToast({
						title: '请选择地址',
						icon: 'none'
					})
					return
				}
				let goods_id = [];
				let goods_num = [];
				this.shop.forEach(function(item, index) {
					if (item.goods_num != 0) {
						goods_id.push(item.goods_id + ':' + (item.id ? item.id : ''))
						goods_num.push(item.goods_num)
					} else {
						goods_id.splice(index, item.goods_id)
						goods_num.splice(index, item.goods_num)
					}
				})
				// 商家订单接口
				uni.$u.http.post('api/orders/wxpay_digital_good', {
					user_id: uni.getStorageSync('user').user_id,
					buy_goods: goods_id.toString(),
					buy_number: goods_num.toString(),
					address_id: this.address_id,
					use_coupon: this.use_coupon,
					store_id: uni.getStorageSync('store_id'),
					remake: this.remake //备注
				}).then(res => {
					uni.setStorageSync('order_number', res.data.data.order_number)
					uni.requestPayment({
						provider: 'wxpay',
						timeStamp: res.data.data.timeStamp,
						nonceStr: res.data.data.nonceStr,
						package: res.data.data.package,
						signType: res.data.data.signType,
						paySign: res.data.data.paySign,
						success: function(ress) {
							uni.redirectTo({
								url: './dingdantijiao'
							})
						},
						fail: function(err) {
							uni.redirectTo({
								url: './dingdantijiao'
							})
						}
					});
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '商家订单接口出错',
						icon: 'none'
					})
				})
			},
			// 返回商家
			on_page() {
				uni.navigateBack({
					delta: 1
				})
			},
		}
	}
</script>

<style lang="scss">
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	page {
		background-color: #f5f5f5;
	}

	.goods {
		background-color: #FFFFFF;
		margin: auto;
		border-radius: 20rpx;
		margin: 30rpx;
		padding: 30rpx;

		.store {
			border-bottom: 1rpx solid #F5F5F5;
			display: flex;
			justify-content: space-between;
			margin-bottom: 22rpx;
			padding-bottom: 22rpx;

			text {
				font-size: 30rpx;
				font-weight: bold;
				color: #333333;
			}
		}

		.foot {
			display: flex;
			margin-bottom: 22rpx;

			image {
				width: 80rpx;
				height: 80rpx;
			}

			.foot_name {
				margin-left: 15rpx;
				display: flex;
				justify-content: space-between;
				flex: 1;

				.foot_1 {
					font-weight: bold;
					font-size: 28rpx;
					color: #333333;
					display: flex;
					flex-flow: column;

					.foot_title {
						color: #808080;
						font-size: 25rpx;
						margin-top: 10rpx;
					}
				}

				.foot_2 {
					font-weight: bold;
					font-size: 14px;
					color: #333333;
					display: flex;
					justify-content: space-between;
				}
			}
		}
	}

	.row {
		font-weight: bold;
		font-size: 28rpx;
		color: #333333;
		display: flex;
		justify-content: space-between;
		margin-bottom: 22rpx;

		text {
			color: #333333;
		}
	}

	.br {
		height: 90rpx;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
	}

	.coupon {
		background: linear-gradient(to right, red, orange);
		display: flex;
		align-items: center;
		padding: 10rpx;
		// border-radius: 3px;
		margin: 2px;
	}

	.coupon>text {
		background: #f7dd93;
		color: red;
		// border-radius: 3px;
		padding: 40rpx 20rpx;
	}

	.coupon .right {
		display: flex;
		flex-flow: column;
		align-items: center;
		color: white;
		margin: 0 10px;
	}

	.coupon .right h1 {
		font-size: 20px;
	}

	.coupon .right text {
		font-size: 11px;
	}

	.submit {
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
		position: fixed;
		bottom: 0rpx;
		display: flex;
		width: 100%;
		height: 90rpx;
		background-color: #333333;
		color: #FFFFFF;
		justify-content: space-between;
		align-items: center;
		font-weight: bold;

		.name {
			margin-left: 20rpx;
		}

		.btn {
			background-color: #42CA47;
			width: 30%;
			height: 100%;
			line-height: 90rpx;
			text-align: center;
		}
	}

	.pay {
		width: 93%;
		height: 80rpx;
		background-color: #FFFFFF;
		margin: 30rpx auto;
		border-radius: 20rpx;
		display: flex;
		align-items: center;
		justify-content: space-between;

		.name {
			font-weight: bold;
			font-size: 28rpx;
			color: #333333;
			padding-left: 30rpx;
		}

		.result {
			color: #333333;
			color: #999999;
			padding-right: 30rpx;
			font-size: 28rpx;
		}
	}
</style>
