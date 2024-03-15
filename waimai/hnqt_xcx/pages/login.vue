<template>
	<view class="login">
		<view class="head">
			欢迎使用 圈圈校园外卖
		</view>
		<view class="dsc">
			获取您的公开信息(昵称、头像)
		</view>
		<!-- <view class="icon">
			<image src="http://waimai.com/static/img/logo.png" mode="widthFix"></image>
		</view> -->
		<view class="text">
			为提供优质服务，我们需要获取您的信息用于点餐：
		</view>
		<button class="avatar-wrapper" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
			<image class="avatar" :src="avatarUrl"></image>
		</button>
		<view class="nickname">
			<text>昵称</text>
			<input type="nickname" class="weui-input" placeholder="请输入昵称" v-model="nickname" @blur="onBlur" @click="onBlur"/>
		</view>
		<view class="" style="text-align: center;">
			<button @click="getUserInfo" class="bgr">
				授权进入圈圈校园外卖
			</button>
			<!-- <button open-type="getPhoneNumber" @getphonenumber="getPhoneNumber" class="bgr">授权手机号进入</button> -->
		</view>
	</view>
</template>

<script>
	const defaultAvatarUrl = 'https://mmbiz.qpic.cn/mmbiz/icTdbqWNOwNRna42FI242Lcia07jQodd2FJGIYQfG0LAJGFxM4FbnQP6yfMxBgJ0F3YRqJCJ1aPAK2dQagdusBZg/0'
	var that;
	export default {
		data() {
			return {
				code: '',
				avatarUrl: defaultAvatarUrl,
				nickname: ''
			}
		},
		onLoad() {
			let that = this;
			uni.login({
				provider: 'weixin',
				success: function(res) {
					// console.log('微信code', res);
					that.code = res.code
				},
			})
		},

		methods: {
			onChooseAvatar(e) {
				this.avatarUrl = e.detail.avatarUrl
			},
			onBlur(e){
				this.nickname = e.detail.value
			},
			// 获取用户头像姓名
			getUserInfo() {
				if (!this.nickname) {
					uni.showToast({
						title: '请输入昵称',
						icon: 'none',
						duration: 3000
					})
					return false
				} else {
					uni.$u.http.post('api/home/get_phone', {
						code: this.code,
						username: this.nickname,
						userimage: this.avatarUrl
					}).then(res => {
						if (res.data.code == 200) {
							uni.setStorageSync('user', res.data.data)
							uni.showToast({
								title: '正在登录',
								icon: "loading",
								duration: 500
							})
							setTimeout(function() {
								uni.navigateBack({
									delta: 1
								})
							}, 500)
			
						}
					})
				}
			},
			//授权手机号
			// getPhoneNumber(e) {
			// 	console.log(e)
			// 	let that = this;
			// 	if (e.detail.errMsg == "getPhoneNumber:ok") {
			// 		uni.$u.http.post('api/home/get_phone', {
			// 			code: e.detail.code,
			// 			// nickname: '',
			// 			// image: '',
			// 		}).then(res => {
			// 			// console.log('登录测试', res)
			// 			if (res.data.code == 200) {
			// 				uni.setStorageSync('user', res.data.data.user_id)
			// 				uni.showToast({
			// 					title: '正在登录',
			// 					icon: "loading",
			// 					duration: 2000
			// 				})
			// 				setTimeout(function() {
			// 					uni.switchTab({
			// 						url: './index'
			// 					})
			// 				}, 2000)

			// 			}
			// 		}).catch(err => {
			// 			console.log('400', err)
			// 			uni.showToast({
			// 				title: '登录接口出错',
			// 				icon: 'none'
			// 			})
			// 		})
			// 	} else {
			// 		uni.showToast({
			// 			title:'您拒绝授权,无法进入',
			// 			icon:'none',
			// 			duration:3000
			// 		})
			// 	}
			// },
		}
	}
</script>

<style lang="less">
	.login {
		position: absolute;
		top: 50%;
		left: 50%;
		width: 80%;
		transform: translate(-50%, -50%);
		.text {
			font-size: 26rpx;
			color: #252525;
			margin-bottom: 20rpx;
		}
		
		.text2 {
			color: #9E9E9E;
			font-size: 24rpx;
			margin-bottom: 40rpx;
		}
		
		.icon {
			text-align: center;
		
			&>image {
				width: 160rpx;
				margin: 80rpx 0;
			}
		}
		
		.dsc {
			font-size: 34rpx;
			color: #9E9E9E;
		}
		
		.head {
			font-size: 56rpx;
			color: #252525;
			margin-bottom: 40rpx;
			margin-top: 40rpx;
		}
		.avatar-wrapper {
			width: 200rpx;
			height: 200rpx;
			padding: 0;
			background: transparent;
			border: 0;
		
			.avatar {
				width: 100%;
				height: 100%;
			}
		}
		
		.nickname {
			display: flex;
			align-items: center;
			border-top: 1rpx solid #dddddd;
			border-bottom: 1rpx solid #dddddd;
			padding: 20rpx 0;
			margin-top: 30rpx;
			margin-bottom: 30rpx;
		
			&>text {
				margin-right: 80rpx;
			}
		}
	}

	

	// image {
	// 	width: 148rpx;
	// 	height: 148rpx;
	// 	margin: 180rpx 0 180rpx;
	// }

	.bgr {
		width: 100%;
		height: 85rpx;
		background-position: center;
		background-size: cover;
		background-image: linear-gradient(To right, #2CB174, #35C784);
		text-align: center;
		line-height: 85rpx;
		color: #fff;
		font-size: 30rpx;
		letter-spacing: 2rpx;
		margin: 0 auto 100rpx;
		border-radius: 50rpx;
	}
</style>
