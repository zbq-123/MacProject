<template>
	<view class="supermarket">
		<u-navbar title="商超服务" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="vie1" @click="dateil(index)" v-for="(item,index) in shop" :key="index">
			<image class="img3" :src="url1+item.logo"></image>
			<view class="vie2">
				<h2>
					<lable class="lab">
						<lable style='position: absolute;right:-540rpx;top:-11rpx; color:#606266;'>...</lable>
					</lable>{{item.name}}
				</h2>
				<lable style="margin-top: 8rpx; font-size: 22rpx; color: #606266;">已售{{item.sale}}单</lable>
				<lable style="margin-top: 8rpx; font-size: 22rpx; color: #606266;">起送价￥{{item.min_price}} |
					{{item.delivery_name}}￥{{item.delivery_price}}</lable>
				<lable :class="item.status==1?'on':'no'">
					{{item.status==1?'营业中':'休息中'}}</lable>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				url1: 'http://waimai.com',
				shop: [],
				show: false,
			}
		},
		onLoad() {
			uni.showLoading({
				title:'加载中'
			})
			// 店铺
			uni.$u.http.post('api/home/getstore', {
				campus_id:uni.getStorageSync('campus_id')
			}).then(res => {
				// console.log('解绑', res.data)
				this.shop = res.data.data
				uni.setStorageSync('store',res.data.data)
				uni.hideLoading()
			}).catch(err => {
				console.log(err)
			})
		},
		methods: {
			dateil(index) {
				uni.navigateTo({
					url:'./dateil?store_id='+uni.getStorageSync('store')[index].id+'&store_index='+index
				})
			},
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
	.vie1 {
		display: flex;
		align-items: center;
	}
	.vie2 {
		display: flex;
		flex-direction: column;
	}
	.img3 {
		width: 170rpx;
		height: 170rpx;
		border-radius: 20rpx;
		margin: 15rpx;
	}
	.lab {
		position: relative;
	}
	.on{
		border:3rpx solid #ffaa00;
		color: #ffaa00;
		border-radius: 10rpx;
		margin-top: 10rpx;
		text-align: center;
		font-size: 22rpx;
		display: block;
		width: 100rpx;
		font-weight: bold;
	}
	.no{
		border:3rpx solid #000000;
		color: #88847f;
		border-radius: 10rpx;
		margin-top: 10rpx;
		text-align: center;
		font-size: 22rpx;
		display: block;
		width: 100rpx;
		font-weight: bold;
	}
</style>