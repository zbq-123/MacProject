<template>
	<view class="card">
		<u-navbar title="月卡" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<image src="../../static/card.png" alt="" mode="widthFix" @click="buyCard"></image>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				
			}
		},
		methods: {
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

<style lang="scss">
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}
	.card{
		margin: 20rpx;
		image{
			width: 100%;
			
		}
	}
</style>