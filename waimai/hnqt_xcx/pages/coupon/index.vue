<template>
	<view class="">
		<u-navbar title="优惠卷" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="coupon_i" v-for="(item,index) in couponList" :key="index">
			<view class="coupon">
				<view class="name">
					<u--text color="black" size="15" text="校园外卖满减优惠卷" align="left"></u--text>
					<u--text color="black" size="15" :text="item.start_time+'至'+item.end_time+'到期'" align="left">
					</u--text>
				</view>
				<view class="">
					<u--text mode="price" size="30" color="red" :text="item.discount_money" align="center"></u--text>
					<button @click="index()" align="center">去使用</button>
				</view>
			</view>
			<view class="line"></view>
			<view class="use" @click="hidden(index)">
				<text>使用规格</text>
				<u-icon size="10" name="arrow-down" v-if="show!=index"></u-icon>
				<u-icon size="10" name="arrow-up" v-else></u-icon>
			</view>
			<view class="use_details" v-show="show==index">{{item.name}}</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				couponList: [],
				show: 0
			}
		},
		onLoad() {
			uni.$u.http.post('api/users/getusercunponlist', {
				campus_id: uni.getStorageSync('campus_id'),
				user_id: uni.getStorageSync('user').user_id
			}).then(res => {
				// console.log(res.data.data)
				this.couponList = res.data.data
			}).catch(err => {
				console.log(err)
			})
		},
		methods: {
			index() {
				uni.switchTab({
					url: '../index'
				})
			},
			hidden(e) {
				this.show = e
			}
		}
	}
</script>

<style lang="scss">
	page {
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
		height: 100vh;
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.coupon_i {
		background: white;
		box-shadow: 0rpx 0rpx 24rpx 0rpx rgba(65, 65, 65, 0.12);
		margin: 30rpx;
		padding: 30rpx;
		padding-bottom: 0;

		.coupon {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 26rpx;
			margin-left: 14rpx;

			.name {
				display: flex;
				flex-flow: column;
				align-items: center;
				justify-content: space-between;
				height: 120rpx;
			}
		}

		.use {
			margin: 26rpx 14rpx;
			display: inline-flex;
			align-items: center;

			text {
				margin-right: 12rpx;
				font-size: 22rpx;
				font-weight: 400;
				color: #616060;
			}
		}
	}

	.line {
		width: 98%;
		margin: auto;
		border: 2rpx dashed #c8c7c7;
		position: relative;
	}

	.line::after {
		content: '';
		border-radius: 50%;
		border: 30rpx solid;
		border-top-color: #eeebe1;
		border-right-color: #eeebe1;
		border-left-color: transparent;
		border-bottom-color: transparent;
		transform: rotate(45deg);
		position: absolute;
		top: -30rpx;
		left: -66rpx;
	}

	.line::before {
		content: '';
		border: 30rpx solid;
		border-top-color: #eeebe1;
		border-right-color: #eeebe1;
		border-left-color: transparent;
		border-bottom-color: transparent;
		transform: rotate(-135deg);
		border-radius: 50%;
		position: absolute;
		top: -30rpx;
		right: -66rpx;
	}

	.use_details {
		font-size: 22rpx;
		font-family: AlibabaPuHuiTi;
		font-weight: 400;
		color: #616060;
		padding: 0 14rpx 26rpx 14rpx;
	}

	.coupon button {
		background: linear-gradient(90deg, #FFD43F, #FFA453);
		border-radius: 24rpx;
		font-size: 26rpx;
		font-family: Source Han Sans SC;
		font-weight: 500;
		color: #151515;
		width: 138rpx;
		height: 50rpx;
		line-height: 50rpx;
		text-align: center;
	}
</style>
