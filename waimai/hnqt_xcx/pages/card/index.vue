<template>
	<view class="box">
		<u-navbar title="月卡、优惠卷" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<u-tabs :list="list" @click="click"></u-tabs>
		<view class="card" v-show="current==0">
			<view>
				<p style="color: white;font-size: 20px;"><span style="color: gold;">15元</span>开通月卡</p>
				<p style="color: white;margin: 15px 0;">领取<span
						style="font-size: 20px;color: gold;">5元X6张</span>通用优惠劵</p>
				<button @click="buyCard()"
					style="font-size: 16px;background: gold;color: red;height: 50px;line-height: 50px;width: 200px;border: 0;">点击开通</button>
			</view>
			<image style="width: 25%;" src="../../static/card.png" alt="" mode="widthFix"></image>
		</view>
		<view v-show="current==1">
			<view class="coupon" v-for="(item,index) in couponList" :key="index">
				<view class="top">
					<view class="left">
						<text>满{{item.full_money}}可用 ￥{{item.discount_money}}</text>
					</view>
					<text class="right" @click="collar(item)">领</text>
				</view>
				<view class="bottom">截至日期{{item.end_time}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				current:0,
				list: [{
					name: '月卡'
				}, {
					name: '可领优惠卷',
					badge: {
						value: 0
					}
				}],
				couponList:[],//优惠卷
			}
		},
		onLoad() {
			// 获取优惠卷
			uni.$u.http.post('api/home/getcoupon', {
				campus_id:uni.getStorageSync('campus_id'),
				user_id:uni.getStorageSync('user').user_id
			}).then(res => {
				// console.log(res.data.data)
				this.couponList = res.data.data
				this.list[1].badge.value = res.data.data.length
			}).catch(err => {
				console.log(err)
			})
		},
		methods: {
			click(item) {
				this.current = item.index
			},
			// 领取优惠卷
			collar(item){
				uni.$u.http.post('api/home/addcoupon', {
					coupon_id:item.id,
					user_id:uni.getStorageSync('user').user_id,
					campus_id: uni.getStorageSync('campus_id')
				}).then(res => {
					if(res.data.code==200){
						uni.showToast({
							title:'领取成功',
							icon:'none',
							duration:2000
						})
						this.coupon = false
					}else{
						uni.showToast({
							title:res.data.msg,
							icon:'none',
							duration:2000
						})
						this.coupon = false
					}
				}).catch(err => {
					console.log(err)
				})
			},
			buyCard(){
				uni.$u.http.post('api/orders/wxpay_card', {
					user_id: uni.getStorageSync('user').user_id
				}).then(res => {
					uni.requestPayment({
						provider: 'wxpay',
						timeStamp: res.data.data.timeStamp,
						nonceStr: res.data.data.nonceStr,
						package: res.data.data.package,
						signType: res.data.data.signType,
						paySign: res.data.data.paySign,
						success: function(ress) {
							uni.redirectTo({
								url: '../month'
							})
						},
						fail: function(err) {
							uni.redirectTo({
								url: ''
							})
						}
					});
				})
			}
		}
	}
</script>

<style>
	page {
		background-color: #F3F3F3;
	}
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}
	.card{
		padding: 20px;
		border-radius: 5px;
		background: linear-gradient(to right,orangered,gold);
		display: flex;
		align-items: center;
		justify-content: space-between;
		box-shadow: 0px 5px 10px 0px rgba(0,0,0,.5);
		margin: 20rpx;
	}
	.coupon{
		background: orangered;
		color: white;
		border-radius: 3px;
		padding: 14px 10px;
		margin-bottom: 4px;
		display: flex;
		flex-flow: column;
		margin: 20rpx;
	}
	.coupon .top{
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.coupon .bottom{
		font-size: 14px;
	}
	.coupon .top .left{
		display: flex;
		align-items: center;
		flex-flow: column;
	}
	.coupon .top .left text{
		font-size: 11px;
	}
	.coupon .top .left text:first-child{
		font-size: 25px;
	}
	.coupon .top .right{
		width: 28px;
		height: 28px;
		line-height: 28px;
		text-align: center;
		background: white;
		color: #88847f;
		border-radius: 50%;
		padding: 4px;
	}
</style>