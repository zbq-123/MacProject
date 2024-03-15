<template>
	<view class="">
		<u-navbar title="搜索结果" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="vie1" @click="jinpeng(item)" v-for="(item,index) in shop" :key="index">
			<image class="image" :src="url1+item.logo"></image>
			<view class="vie2">
				<h2>
					<lable class="lab">
						<lable style='position: absolute;right:-270px;top:-5px; color:#606266;'>...</lable>
					</lable>{{item.name}}
				</h2>
				<lable style="margin-top: 8rpx; font-size: 22rpx; color: #606266;">已售{{item.sale}}单</lable>
				<lable style="margin-top: 8rpx; font-size: 22rpx; color: #606266;">起送价￥{{item.min_price}} |
					{{item.delivery_name}}￥{{item.delivery_price}}
				</lable>
				<lable :class="item.status==1?'on':'no'">
					{{item.status==1?'营业中':'休息中'}}
				</lable>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				shop: [],
				url1: 'http://waimai.com',
			}
		},
		onLoad(options) {
			uni.$u.http.post('api/home/getstore', {
				campus_id: uni.getStorageSync('campus_id'),
				name: options.keyword
			}).then(res => {
				this.shop = res.data.data
			}).catch(err => {
				console.log(err)
			})
		},
		methods: {
			jinpeng(item) {
				uni.navigateTo({
					url: './food/jinpengshitang?store_id=' + item.id
				})
			},
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #F3F3F3;
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.on {
		border: 3rpx solid #ffaa00;
		color: #ffaa00;
		border-radius: 10rpx;
		margin-top: 10rpx;
		text-align: center;
		font-size: 22rpx;
		display: block;
		width: 100rpx;
		font-weight: bold;
	}

	.no {
		border: 3rpx solid #000000;
		color: #88847f;
		border-radius: 10rpx;
		margin-top: 10rpx;
		text-align: center;
		font-size: 22rpx;
		display: block;
		width: 100rpx;
		font-weight: bold;
	}

	.vie1 {
		display: flex;
		align-items: center;
		margin: 10rpx;
		background: white;
		border-radius: 15rpx;
		padding: 10rpx;

		.image {
			width: 150rpx;
			height: 150rpx;
			border-radius: 20rpx;
			margin-right: 15rpx;
		}

		.vie2 {
			display: flex;
			flex-direction: column;

			.lab {
				position: relative;
			}
		}
	}
</style>
