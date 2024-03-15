<template>
	<view>
		<u-navbar title="闲置物品" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<u-tabs :list="list4" lineWidth="20" lineHeight="2" lineColor="#3c9cff"></u-tabs>
		<view class="box" v-for="(item,index) in comment" :key="index">
			<view class="u-page">
			    <view class="u-demo-block">
			      <view class="u-demo-block__content">
			        <view class="album">
			          <u-avatar :src="item.avatar" shape="shape"></u-avatar>
			          <view class="album__content">
			            <u--text :text="item.name" color="#fff" bold size="17"></u--text>
			            <u--text margin="5px 0 8px 0" color="#fff" :text="item.title"></u--text>
			            <u-album :urls="item.urls2"></u-album>
			          </view>
			        </view>
			      </view>
			    </view>
			</view>
			<view class="comment">
				<view><u-icon name="share-square" color="gray" size="25"></u-icon>123</view>
				<view><u-icon name="chat" color="gray" size="25"></u-icon>123</view>
				<view><u-icon name="thumb-up" color="gray" size="25"></u-icon>123</view>
			</view>
		</view>
		<button @click="add" class="release"><u-icon name="plus" color="#fff" size="30"></u-icon></button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				list4: [{
					name: '二手置换',
					badge: {
						isDot: false,
						value: 999,
						max: 99
					}
				}],
				comment:[{
					avatar:'https://cdn.uviewui.com/uview/album/1.jpg',
					name:'小明',
					title:'全面的组件和便捷的工具会让您信手拈来，如鱼得水',
					urls2: [
						'https://cdn.uviewui.com/uview/album/1.jpg',
						'https://cdn.uviewui.com/uview/album/2.jpg',
						'https://cdn.uviewui.com/uview/album/3.jpg',
						'https://cdn.uviewui.com/uview/album/4.jpg',
						'https://cdn.uviewui.com/uview/album/5.jpg',
						'https://cdn.uviewui.com/uview/album/6.jpg',
						'https://cdn.uviewui.com/uview/album/7.jpg',
						'https://cdn.uviewui.com/uview/album/8.jpg',
						'https://cdn.uviewui.com/uview/album/9.jpg',
						'https://cdn.uviewui.com/uview/album/10.jpg',
					],
				},{
					avatar:'https://cdn.uviewui.com/uview/album/1.jpg',
					name:'小明',
					title:'全面的组件和便捷的工具会让您信手拈来，如鱼得水',
					urls2: [
						'https://cdn.uviewui.com/uview/album/1.jpg',
						'https://cdn.uviewui.com/uview/album/2.jpg',
						'https://cdn.uviewui.com/uview/album/3.jpg',
						'https://cdn.uviewui.com/uview/album/4.jpg',
						'https://cdn.uviewui.com/uview/album/5.jpg',
						'https://cdn.uviewui.com/uview/album/6.jpg',
						'https://cdn.uviewui.com/uview/album/7.jpg',
						'https://cdn.uviewui.com/uview/album/8.jpg',
						'https://cdn.uviewui.com/uview/album/9.jpg',
						'https://cdn.uviewui.com/uview/album/10.jpg',
					],
				}]
			}
		},
		methods:{
			add(){
				uni.navigateTo({
					url: 'add'
				})
			}
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
	.box{
		margin: 30rpx;
		padding: 30rpx 30rpx 130rpx 30rpx;
		background: #c6c6c6;
		position: relative;
		border: 1px solid #c6c6c6;
		border-radius: 10px;
	}
	.album {
		@include flex;
		align-items: flex-start;

		&__avatar {
			 background-color: $u-bg-color;
			 padding: 5px;
			 border-radius: 3px;
		 }
	
		&__content {
			 margin-left: 10px;
			 flex: 1;
		 }
	}
	.comment{
		background: white;
		display: flex;
		align-items: center;
		justify-content: space-around;
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
		padding: 20rpx 0;
		border-bottom-left-radius: 10px;
		border-bottom-right-radius: 10px;
	}
	.comment view{
		display: flex;
		align-items: center;
		font-size: 30rpx;
		color: #9a9a9a;
	}
	.release{
		position: fixed;
		right: 30rpx;
		bottom: 130rpx;
		border-radius: 50%;
		background: royalblue;
		height: 100rpx;
		width: 100rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>