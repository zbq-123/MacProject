<template>
	<view class="">
		<u-navbar title="数码产品" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="supermarket">
			<view class="vie1" @click="dateil(index)" v-for="(item,index) in shop" :key="index">
				<image class="img3" :src="url1+item.image"></image>
				<view class="vie2">
					<view class="h2">
						<h2>{{item.name}}</h2>
						<!-- <label :class="item.status==1?'on':'no'">{{item.status==1?'营业中':'休息中'}}</label> -->
						<text style="margin-left: 58rpx;">已售{{item.sale}}单</text>
					</view>
					<!-- <label>
						<u-icon size="14px" name="star-fill" color="#ffdb0f" label="5.0" space="0" labelSize="12px">
						</u-icon>
						<text style="margin-left: 58rpx;">已售{{item.sale}}单</text>
					</label> -->
					<!-- <label>
						起送价￥{{item.min_price}} | {{item.delivery_name}}￥{{item.delivery_price}}
					</label> -->
				</view>
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
			uni.$u.http.post('api/digital/get_digital', {
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
	.supermarket {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		padding-top: 30rpx;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
	}
	.vie1 {
		background: white;
		border-radius: 10rpx;
		overflow: hidden;
		width: 43.5%;
		margin-bottom: 24rpx;
		margin-left: 38rpx;
		box-shadow: 0 0 16rpx 0 rgba(0, 0, 0, 0.15);
	}
	
	.vie1:nth-child(2n) {
		margin-left: 0;
		margin-right: 38rpx;
	}
	.vie2 {
		display: flex;
		flex-direction: column;
		padding: 16rpx;
	}
	
	.vie2>label {
		margin-top: 14rpx;
		font-size: 20rpx;
		color: #ADADAD;
		display: flex;
		align-items: center;
	}
	.h2 {
		font-size: 30rpx;
		font-weight: bold;
		color: #101010;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	
	.h2 h2 {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	.img3 {
		width: 100%;
		height: 190rpx;
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
</style>