<template>
	<view>
		<u-navbar title="订单提交成功" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view style="width: 93%;height: 85rpx;font-size: 45rpx;font-weight: bold;position: relative;display: flex;align-items: center;justify-content: space-between;margin: auto;">
			<u-icon name="server-fill" size="20"></u-icon>
			<view @click="load()" style="display: flex;align-items: center;">
				<u-icon name="reload" size="20" label="刷新" labelSize="13"></u-icon>
			</view>
			<u-loading-page :loading="loading" loading-text="正在获取中"></u-loading-page>
		</view>
		<view style="width: 93%;height: 200rpx;background-color: #FFFFFF;margin: auto;border-radius: 20rpx;">
			<view style="font-size: 25rpx;padding:20rpx;">感谢您对圈圈校园外卖的信任，订单提交成功。</view>
			<view style="width: 93%;height: 100rpx;display: flex;justify-content: space-around;font-size: 24rpx;padding: 20rpx;margin: auto;">
				<view class="vie14" v-show="order_list.pay_status==3||order_list.pay_status==2||order_list.status==14?false:true" @click="pay">
					<u-icon name="weixin-fill" size="23" color="#55cf63" label="立即支付" labelColor="#55cf63" labelPos="bottom" labelSize="12" space="5"></u-icon>
				</view>
				<view class="vie14" v-show="order_list.pay_status==3||order_list.pay_status==2||order_list.status==14?false:true" @click="quxiao">
					<u-icon name="close" size="23" label="取消订单" labelPos="bottom" labelSize="12" space="5"></u-icon>
				</view>
				<view class="vie14" @click="again">
					<u-icon name="bag" size="23" color="#55cf63" label="再来一单" labelColor="#55cf63" labelPos="bottom" labelSize="12" space="5"></u-icon>
				</view>
				<view class="vie14" v-show="order_list.pay_status==2&&order_list.status==2?true:false" @click="apply">
					<u-icon name="close" size="23" label="申请退款" labelPos="bottom" labelSize="12" space="5"></u-icon>
				</view>
				<view class="vie14" @click="tel_store">
					<u-icon name="phone" size="23" label="致电商家" labelPos="bottom" labelSize="12" space="5"></u-icon>
				</view>
			</view>
		</view>
		<view style="width: 93%;background-color: #FFFFFF;margin: auto;border-radius: 20rpx;margin-top: 30rpx;">
			<view style="padding:20rpx 0;width: 93%;font-weight: bold;border-bottom:1rpx solid #F5F5F5;margin: auto;color: #333333;">
				{{shanjia_name}} <text style="color:#808080;padding-left: 5rpx;">></text>
			</view>
			<view v-for="(item,index) in order_list.goods" :key="index"
				style="width: 93%;display: flex;margin: 20rpx; border-bottom:1rpx solid #F5F5F5;padding-bottom: 20rpx;">
				<image :src="url1+item.image" style="width: 80rpx;height: 80rpx;"></image>
				<view style="margin-left: 15rpx;width: 93%;">
					<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
						{{item.name}}
						<view>￥{{item.price}}</view>
					</view>
					<view v-show="item.sku" style="font-size: 25rpx;margin-top: 10rpx;">
						{{item.sku}}
					</view>
					<view style="color:#808080;font-size: 25rpx;margin-top: 10rpx;">×{{item.number}}</view>
				</view>
			</view>
			<view style="width: 93%;margin: 20rpx; border-bottom:1rpx solid #F5F5F5;padding-bottom: 20rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					打包费<view style="color:#333333;margin-bottom: 20rpx;">￥{{order_list.box_price}}</view>
				</view>
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					配送费<view style="color:#333333;margin-bottom: 20rpx;">￥{{order_list.convey_price}}</view>
				</view>
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					满减优惠卷<view style="color:red;margin-bottom: 20rpx;">-￥{{order_list.discount_money}}</view>
				</view>
			</view>
			<view style="display: flex; margin: 20rpx 20rpx 20rpx 0;padding: 10rpx;align-items: center;justify-content: space-between;">
				<u-icon name="phone" color="#65ca41" size="20" label="致电商家" labelColor="#55cf63" labelSize="14" @click="tel_store"></u-icon>
				<view style="font-size: 28rpx;font-weight: bold;color:#333333;">
					合计￥{{order_list.total_price}}
				</view>
			</view>
		</view>
		<view style="width: 93%;height: 400rpx;background-color: #FFFFFF;margin: auto;border-radius: 20rpx;margin-top: 30rpx;">
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;">配送信息</view>
			</view>
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					联系人<view style="color:#333333;">{{order_list.delivery_name}}(先生)</view>
				</view>
			</view>
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					电话<view style="color:#333333;">{{order_list.delivery_phone}}</view>
				</view>
			</view>
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					收货地址<view style="color:#333333;">{{order_list.delivery_address}}</view>
				</view>
			</view>
		</view>
		<view style="width: 93%;height: 400rpx;background-color: #FFFFFF;margin: auto;border-radius: 20rpx;margin: 30rpx;">
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;">订单信息</view>
			</view>
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					订单号
					<view style="color:#333333;">
						<text>{{order_list.order_number}}</text>
						<text class="copy" @click="fuzhi()">复制</text>
					</view>
				</view>
			</view>
			<view style="width: 93%; border-bottom:1rpx solid #F5F5F5;padding: 30rpx;">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					下订时间<view style="color:#333333;">{{order_list.create_time}}</view>
				</view>
			</view>
			<view style="width: 93%; padding: 30rpx;padding-bottom: constant(safe-area-inset-bottom);padding-bottom: env(safe-area-inset-bottom);">
				<view style="font-weight: bold;font-size: 28rpx;color:#333333;display: flex;justify-content: space-between;">
					支付状态
					<view style="color:#333333;" v-if="order_list.status==1&&order_list.pay_status==1">未支付</view>
					<view style="color:#333333;" v-else-if="order_list.status==2&&order_list.pay_status==2">已支付</view>
					<view style="color:#333333;" v-else-if="order_list.status==2&&order_list.pay_status==3">退款中</view>
					<view style="color:#333333;" v-else="order_list.status==14">已取消</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				loading: '',
				fuzhineirong: '',
				order_list: {},
				url1: 'http://waimai.com',
				shanjia_name: ''
			};
		},
		onLoad() {
			uni.$u.http.post('api/orders/getorderinfo', {
				order_number: uni.getStorageSync('order_number')
			}).then(res => {
				// console.log('解绑', res.data)
				this.shanjia_name = res.data.data.order_name
				this.order_list = res.data.data
				this.fuzhineirong = res.data.data.order_number
			})
		},
		methods: {
			fuzhi() {
				uni.setClipboardData({
					data: this.fuzhineirong,
					success: function() {
						uni.showToast({
							title: '复制成功',
							icon: 'none'
						})
						console.log('success');
					}
				});
			},
			//取消订单
			quxiao() {
				uni.$u.http.post('api/orders/cancel_orders', {
					order_number: uni.getStorageSync('order_number'),
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					setInterval(() => {
						this.loading = false
					}, 500)
					this.order_list.status = 14
				})
			},
			// 再来一单
			again() {
				uni.navigateBack({
					delta: 1
				})
			},
			load() {
				this.loading = true
				setInterval(() => {
					this.loading = false
				}, 2000)
			},
			// 致电商家
			tel_store() {
				uni.makePhoneCall({
					phoneNumber: this.order_list.store_phone, //电话号码
					success: function(e) {
						console.log(e);
					},
					fail: function(e) {
						console.log(e);
					}
				})
			},
			pay() {
				uni.$u.http.post('api/orders/wxpay_goods_orders', {
					order_number: uni.getStorageSync('order_number'),
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					uni.requestPayment({
						provider: 'wxpay',
						timeStamp: res.data.data.timeStamp,
						nonceStr: res.data.data.nonceStr,
						package: res.data.data.package,
						signType: res.data.data.signType,
						paySign: res.data.data.paySign,
						success: function(ress) {
							uni.showToast({
								title: '支付成功',
								icon: 'success'
							})
						},
						fail: function(err) {
							uni.showToast({
								title: '支付失败',
								icon: 'none',
								duration:2000
							})
							setTimeout(function(){
								uni.switchTab({
									url:'/pages/dingdan'
								})
							},2000)
						}
					});
				})
			},
			// 立即退款
			apply() {
				uni.$u.http.post('api/orders/apply_refund', {
					order_number: uni.getStorageSync('order_number'),
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					uni.showToast({
						title: '正在退款中',
						icon: 'loading'
					})
					this.order_list.pay_status = 3
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #F5F5F5;
	}
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}
	.vie13 {
		width: 45rpx;
		height: 45rpx;
		margin-bottom: 10rpx;
	}

	.vie14 {
		width: 25%;
		height: 100rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.copy {
		border: 1px solid #42CA47;
		background-color: #ECFAEC;
		border-radius: 10rpx;
		color: #42CA47;
		padding: 0 9rpx;
		margin-left: 10rpx;
		font-size: 24rpx;
	}
</style>
