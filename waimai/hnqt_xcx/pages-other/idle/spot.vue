<template>
	<view>
		<u-navbar :title="category.name" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="bg-color" :style="[{paddingBottom:windowHeight}]">
			<!-- 搜素 -->
			<view class="search">
				<u-icon name="search"></u-icon>
				<input type="text" placeholder="请输入内容" v-model="search_name" @input="search">
			</view>
			<!-- 闲置商品 -->
			<view class="box">
				<view class="box_i" v-for="(listItem,listIndex) in list" :key="listIndex">
					<image :src="url1+listItem.goods_image" mode=""></image>
					<text>{{listItem.name}}</text>
					<view class="zan">
						<text>{{parseFloat(listItem.price)}}</text>
						<u-icon name="chat" size="20" :label="listItem.count_comment" @click="msg(listItem)"></u-icon>
						<u-icon name="thumb-up" size="20" :label="listItem.spot" @click="zan(listItem)"></u-icon>
					</view>
				</view>
			</view>
		</view>
		<u-popup :show="comment" mode="bottom" @close="close" closeable :customStyle="{bottom: inputBottom+'px'}">
			<text class="title">{{conment.length}}条评论</text>
			<view class="border_box">
				<view class="touimg" v-for="(item,index) in conment" :key="index">
					<image :src="item.user_image" mode=""></image>
					<view class="">
						<text class="name">{{item.user_name}} {{item.create_time}}</text>
						<text class="content">{{item.content}}</text>
					</view>
				</view>
			</view>
			<!-- 下发发送模块 -->
			<view class="bottom-textarea">
				<view class="textarea-container">
					<textarea v-model="content" :maxlength="-1" :auto-height="true" :show-confirm-bar="false"
						:cursor-spacing="10" :fixed="true" :adjust-position="false" @focus="focusTextarea"
						@blur="blurTextarea" placeholder="发条有爱评论~"/>
				</view>
				<button @click="send">发送</button>
			</view>
		</u-popup>
		<view class="bottom">
			<u-icon name="plus" color="#fff" size="23" @click="add()"></u-icon>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				comment: false,
				url1: 'http://waimai.com',
				swiperList: [],
				windowHeight: 0,
				baseList: [],
				list: [],
				content: '',
				conment: [],
				idlegoods_id: 0,
				records: [],
				recordInput: "",
				itemAverageHeight: 500, //每条数据平均高度，为确保能滚到底部，可以设置大一些
				scrollTop: 1000,
				inputBottom: 0,
				category: {},
				search_name: '',
				safeAreaInsets:0
			}
		},
		onLoad(options) {
			this.category = JSON.parse(options.category)
			this.goods(JSON.parse(options.category).id)
		},
		onReady() {
			setTimeout(() => {
				let _that = this;
				uni.getSystemInfo({
					success: function(res) {
						_that.safeAreaInsets = res.safeAreaInsets.bottom
						let bottom = uni.createSelectorQuery().select(".bottom");
						bottom.boundingClientRect(function(datad) { //data - 各种参数
							_that.windowHeight = datad.height + 'px';
						}).exec()
					}
				});
			}, 300)
		},
		methods: {
			search() {
				//闲置商品列表
				uni.$u.http.post('api/idlegoods/get_idle_goods', {
					name: this.search_name,
					category_id: this.category.id
				}).then(res => {
					this.list = res.data.data
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '闲置商品列表',
						icon: 'none'
					})
				})
			},
			goods(e) {
				//闲置商品列表
				uni.$u.http.post('api/idlegoods/get_idle_goods', {
					category_id: e
				}).then(res => {
					this.list = res.data.data
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '闲置商品列表',
						icon: 'none'
					})
				})
			},
			close() {
				this.comment = false
			},
			// 发布闲置
			add() {
				uni.navigateTo({
					url: 'add'
				})
			},
			spot(i, e) {
				uni.navigateTo({
					url: 'spot?category=' + JSON.stringify(i)
				})
			},
			click(name) {
				this.$refs.uToast.success(`点击了第${name}个`)
			},
			zan(e) {
				uni.$u.http.post('api/idlegoods/spot_idle_goods', {
					idlegoods_id: e.id,
					user_id: uni.getStorageSync('user').user_id,
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '感谢你的支持',
							icon: 'none'
						})
						this.goods()
					} else {
						uni.showToast({
							title: res.data.msg,
							icon: 'none'
						})
					}
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '点赞接口',
						icon: 'none'
					})
				})
			},
			msg(e) {
				this.comment = true
				this.idlegoods_id = e
				uni.$u.http.post('api/idlegoods/get_idle_goods_comment', {
					idlegoods_id: e.id
				}).then(res => {
					this.conment = res.data.data
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '评论列表',
						icon: 'none'
					})
				})
			},
			// 发送
			send() {
				uni.$u.http.post('api/idlegoods/add_idlegoods_comment', {
					idlegoods_id: this.idlegoods_id.id,
					user_id: uni.getStorageSync('user').user_id,
					user_image: uni.getStorageSync('image'),
					user_name: uni.getStorageSync('nickname'),
					content: this.content,
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '评论成功',
							icon: 'none'
						})
						this.msg(this.idlegoods_id)
						this.goods(this.category.id)
						this.content = ''
					}
				}).catch(err => {
					console.log('400', err)
					uni.showToast({
						title: '发布评论',
						icon: 'none'
					})
				})
			},
			focusTextarea(e) {
				this.inputBottom = e.detail.height - this.safeAreaInsets;
				this.scrollTop += 1; //滚到底部
			},
			blurTextarea(e) {
				this.inputBottom = 0;
				this.scrollTop += 1; //滚到底部
			},
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

	.title {
		text-align: center;
		font-size: 24rpx;
		margin: 34rpx 0;
	}

	.touimg {
		display: flex;
		align-items: center;

		view {
			display: flex;
			flex-flow: column;
			flex: 1;

			.name {
				font-size: 24rpx;
			}

			.content {
				font-size: 24rpx;
				color: #5C5C5C;
			}
		}

		image {
			width: 60rpx;
			height: 60rpx;
			margin: 20rpx 20rpx 20rpx 0;
		}
	}

	.search {
		height: 48rpx;
		line-height: 48rpx;
		display: flex;
		align-items: center;
		border-radius: 100rpx;
		padding: 8rpx;
		background: #F0ECE0;
		margin: 22rpx;
		font-size: 23rpx;

		input {
			flex: 1;
		}
	}

	.comment {
		margin: 22rpx;
		background: white;
		box-shadow: 0rpx 0rpx 36rpx 0rpx rgba(51, 51, 51, 0.11);
		border-radius: 14rpx;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 24rpx;

		.model {
			image {
				width: 100%;
			}

			text {
				font-size: 12px;
				color: #5C5C5C;
			}
		}
	}

	.border_box {
		margin: 0 40rpx;
		min-height: 620rpx;
		max-height: 620rpx;
		overflow: scroll;
	}

	.box {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		margin: 22rpx;

		.box_i {
			display: flex;
			flex-flow: column;
			width: 340rpx;
			background-color: white;
			border-radius: 8rpx;
			margin-bottom: 22rpx;
			box-shadow: 0rpx 0rpx 36rpx 0rpx rgba(51, 51, 51, 0.11);
			overflow: hidden;

			image {
				width: 100%;
				height: 248rpx;
				border-bottom-left-radius: 8rpx;
				border-bottom-right-radius: 8rpx;
			}

			>text {
				font-size: 28rpx;
				font-weight: bold;
				color: #343434;
				margin: 16rpx;
			}
		}
	}

	.zan {
		display: flex;
		align-items: flex-end;
		justify-content: space-around;
		margin-bottom: 36rpx;

		text {
			color: #BB0000;
			font-size: 34rpx;
		}
	}

	.bottom {
		width: 100%;
		position: fixed;
		bottom: 0;
		left: 0;
		height: 80rpx;
		background: white;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);

		.u-icon {
			display: flex;
			justify-content: center;
			width: 90rpx;
			height: 90rpx;
			border-radius: 50%;
			background: #FED974;
			border: 10rpx solid white;
			position: absolute;
			left: 50%;
			top: 10%;
			transform: translate(-50%, -50%);
		}
	}

	swiper {
		height: 238rpx;
		// width: 95%;
		overflow: hidden;
		margin: 22rpx;
		border-radius: 10rpx;
	}

	.swiper-item,
	image {
		width: 100%;
		height: 100%;
		text-align: center;
	}

	.bottom-textarea {
		position: relative;
		bottom: 0px;
		left: 0px;
		right: 0px;
		background-color: white;
		border-top: 2rpx solid rgba(226, 226, 226, 1);
		padding: 12rpx;
		min-height: 60rpx;
	
		.textarea-container {
			background: #e2e2e2;
			border-radius: 10rpx;
			box-sizing: border-box;
			width: 83%;
			min-height: 60rpx;
			max-height: 180rpx;
			overflow-y: scroll;
	
			>textarea {
				min-height: 60rpx;
				max-height: 180rpx;
				font-size: 32rpx;
				color: rgba(8, 8, 8, 1);
				width: auto;
				border-radius: 10rpx;
			}
		}
	
		>button {
			flex-shrink: 0;
			height: 60rpx;
			line-height: 60rpx;
			margin-left: 16rpx;
			border-radius: 40rpx;
			background: #ff2352;
			font-size: 28rpx;
			color: white;
			position: absolute;
			bottom: 12rpx;
			right: 12rpx;
		}
	}
</style>
