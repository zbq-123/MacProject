<template>
	<view class="">
		<u-navbar title="兼职培训" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" leftIconColor="#000" :fixed="true" :placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="train">
			<!-- 轮播图 -->
			<u-swiper :list="lunbo" keyName="image" autoplay="false" circular radius="0" indicator indicatorMode="dot">
			</u-swiper>
			<view class="title">
				<image src="../static/icon0.png" mode="widthFix"></image>
				<text>选项</text>
			</view>
			<block v-for="(item,index) in model" :key="index">
				<image :src="item.image" mode="widthFix" @click="model_jiaxiao(item)"></image>
			</block>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				lunbo: [{
					image: 'https://cdn.uviewui.com/uview/swiper/swiper2.png',
				}, {
					image: 'https://cdn.uviewui.com/uview/swiper/swiper1.png',
				}, {
					image: 'https://cdn.uviewui.com/uview/swiper/swiper3.png',
				}],
				model:[{
					url:'./model_jiaxiao',
					image:'../static/icon.png'
				},{
					url:'',
					image:'../static/icon1.png'
				},{
					url:'',
					image:'../static/icon2.png'
				}]
			}
		},
		methods:{
			model_jiaxiao(i){
				if(!i.url){
					uni.showToast({
						title: '暂未开发',
						icon:"error"
					})
				}else{
					uni.navigateTo({
						url: i.url
					})
				}
			}
		}
	}
</script>

<style lang="scss">
	page{
		height: 100vh;
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
	}
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.train {
		padding: 24rpx;
		
		>image {
			width: 100%;
			margin-top: 18rpx;
		}
	}

	.title {
		display: flex;
		align-items: center;
		margin-top: 54rpx;

		image {
			width: 26rpx;
		}
	}
</style>
