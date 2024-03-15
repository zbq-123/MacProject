<template>
	<view class="">
		<u-navbar title="客服中心" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<u--text text="常见问题" margin="30rpx"></u--text>
		<u-collapse @change="change" @close="close" @open="open" accordion>
			<u-collapse-item title="问：月卡使用后申请退单怎么办？" name="Docs guide">
				<text class="u-collapse-content">答：下单成功后，如发现点餐不齐全、点错餐等，可在一分钟之内取消订单，使用的月卡也会随之原路返还。</text>
			</u-collapse-item>
			<u-collapse-item title="问：平台出现bug如何处理？" name="Docs guide">
				<text class="u-collapse-content">答：联系电话客服提供bug</text>
			</u-collapse-item>
			<u-collapse-item title="问：在平台购物可信吗？" name="Docs guide">
				<text class="u-collapse-content">答：平台的一切商品，货源都是正规渠道，绝无弄虚作假。本平台的入驻也是经过校方考察的，因为我们只为高校师生服务，所以请放心使用。</text>
			</u-collapse-item>
		</u-collapse>
		<view class="btn">
			<u-icon @click="phone_1" name="phone" color="#000000" size="23" labelColor="#000000" label="客服座机"></u-icon>
			<u-icon @click="phone_2" name="phone" color="#000000" size="23" labelColor="#000000" label="客服电话"></u-icon>
		</view>
	</view>
</template>

<script>
	export default {
		methods: {
			open(e) {
				// console.log('open', e)
			},
			close(e) {
				// console.log('close', e)
			},
			change(e) {
				// console.log('change', e)
			},
			phone_1() {
				uni.makePhoneCall({
					phoneNumber: '65326489', //电话号码
					success: function(e) {
						console.log(e);
					},
					fail: function(e) {
						console.log(e);
					}
				})
			},
			phone_2() {
				uni.makePhoneCall({
					phoneNumber: '13307648035', //电话号码
					success: function(e) {
						console.log(e);
					},
					fail: function(e) {
						console.log(e);
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.btn {
		display: flex;
		align-items: center;
		justify-content: space-around;
		position: fixed;
		bottom: 10%;
		width: 100%;

		view {
			width: 40%;
			border-radius: 30px;
			border: 0;
			background: linear-gradient(90deg, #FFD43F, #FFA453);
			font-size: 30rpx;
			justify-content: center;
			padding: 10rpx 0;
		}
	}
</style>
