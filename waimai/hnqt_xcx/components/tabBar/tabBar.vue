<template>
	<view class="tarbar">
		<view :class="item.small" v-for="(item,index) in list" :key="index" @click="change1(item,index)" @touchstart="touchstart(item)" @touchend="touchend(item)">
			<image :src="current==index?item.selectedIconPath:item.iconPath" mode="">
			</image>
			<text :class="current==index?'active':'normal'">{{item.text}}</text>
		</view>
	</view>
</template>
<script>
	export default {
		props: {
			current: {
				type: [Number, String],
				default: 0
			},
		},
		data() {
			return {
				list: [{
					pagePath: '/pages/index',
					iconPath: '../../static/tarbar/home.png',
					selectedIconPath: '../../static/tarbar/home_on.png',
					text: '首页',
					small:''
				}, {
					pagePath: '/pages/dingdan',
					iconPath: '../../static/tarbar/order.png',
					selectedIconPath: '../../static/tarbar/order_on.png',
					text: '订单',
					small:''
				}, {
					pagePath: '/pages/wode',
					iconPath: '../../static/tarbar/my.png',
					selectedIconPath: '../../static/tarbar/my_on.png',
					text: '我的',
					small:''
				}]
			}
		},
		methods: {
			tarbarHeight(a) {
				let tarbar = uni.createSelectorQuery().in(this).select(".tarbar");
				return tarbar
			},
			change1(item, index) {
				uni.switchTab({
					url: item.pagePath
				})
			},
			touchstart(item){
				item.small = 'small'
			},
			touchend(item){
				item.small = ''
			}
		}
	}
</script>
<style scoped lang="scss">
	.tarbar {
		position: fixed;
		bottom: 0;
		display: flex;
		align-items: center;
		width: 100%;
		justify-content: space-around;
		background: white;

		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
		border-top: 1px solid #d9d9d9;

		view {
			display: flex;
			flex-flow: column;
			align-items: center;
			padding: 20rpx 0;
			width: 20%;

			image {
				width: 42rpx;
				height: 42rpx;
				margin-bottom: 4rpx;
			}
		}
	}

	.small {
		transform: scale(.9);
	}

	.active {
		font-size: 18rpx;
		font-weight: 500;
		color: #EF7600;
	}
	.normal{
		font-size: 18rpx;
		font-weight: 500;
		color: #757575;
	}
</style>
