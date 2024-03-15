<template>
	<view>
		<u-sticky>
			<u-navbar title="我的" bgColor="transparent" leftIcon=" " :fixed="true" :placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
			</u-navbar>
		</u-sticky>
		<view class="" :style="{paddingBottom:marginBottom+'px'}">
			<view class="vie1">
				<u-avatar size="63"
					:src="user.image?user.image:'http://waimai.com/static/img/student.png'">
				</u-avatar>
				<view class="my">
					<text v-if="user">{{user.nickname}}</text>
					<text v-else @click="personal">去登陆</text>
					<!-- <text style="color: dimgray;font-size: 35rpx;">{{phone}}</text> -->
				</view>
			</view>
			<view class="vie2">
				<lable>消费 <lable class="lab1">{{user.saleprice?user.saleprice:'0'}}</lable>
				</lable>
				<lable>订单数 <lable class="lab1">{{user.all_order?user.all_order:'0'}}</lable>
					</h1>
				</lable>
				<lable>待处理 <lable class="lab1">{{user.pending_order?user.pending_order:'0'}}</lable>
				</lable>
			</view>
			<view class="bg-color">
				<view class="wode_card">
					<image src="../static/card_bg.png" mode=""></image>
					<view class="box">
						<view style="display: flex;justify-content: space-between;align-items: center;">
							<text class="title_icon">
								<image src="../static/title_icon.png" mode="widthFix"></image>我的会员
							</text>
							<view style="display: flex;font-size: 20rpx;color: #fff;">
								<text @click="show = true">会员权益</text>
								<u-icon name="arrow-right" color="white" size="10"></u-icon>
							</view>
						</view>
						<u-popup :show="show" @close="close" @open="open" mode="center" round="10" closeable="true"
							safeAreaInsetTop="true" customStyle="width:80%">
							<view class="popup">
								<text>本平台会员充值，充值100送30元赠送金、200送80元赠送金、300送120元赠送金。
									会员每年生日可享受一次30元内免单机会！
									一元可得1积分，积分到达3888，可从普通会员升级为黄金会员，并增加80元赠送金。
									积分到达6888，可从黄金会员升为白金会员，并增加180元赠送金。
									充值金和赠送金无日期限制。</text>
								<text>赠送金说明</text>
								<text>如何获取赠送金</text>
								<text>在圈圈平台余额账户或银行电子账户充值后可享受返赠送金活动。</text>
								下单抵现当钱花
								<text>赠送金是在圈圈平台消费时按照1：1抵扣订单金额使用。</text>
								赠送金无法提现
								<text>充值本金可提现，赠送金不可提现，充值本金一旦提现，赠送金将被清零。</text>
							</view>
						</u-popup>
						<view class="card">
							<text class="name">白银会员</text>
							<view class="">
								<text style="margin: 8rpx 0;display: block;">0/100</text>
								<u-line-progress :showText="showText" :inactiveColor="inactiveColor"
									:activeColor="activeColor" :percentage="percentage" height="6" />
								<text style="font-size: 20rpx;">成长值还差100进阶SVP1,享8折</text>
							</view>
						</view>
					</view>
				</view>
				<view class="vie4">
					<view class="title_icon">
						<image src="../static/title_icon_1.png" mode=""></image>我的权益
					</view>
					<view class="my_mb">
						<view class="mb" @click="coupon">
							<image src="../static/model.png" mode=""></image>
							<view class="title1">
								<text style="">优惠卷</text>
							</view>
							<text><text style="color: #FD664F;">{{user.usercunpun?user.usercunpun:'0'}}</text>张未使用</text>
						</view>
						<view class="mb" @click="month">
							<image src="../static/model1.png" mode=""></image>
							<view class="title1">
								<text style="">月卡</text>
							</view>
							<text><text style="color: #FD664F;">{{user.usercard?user.usercard:'0'}}</text>张未使用</text>
						</view>
						<view class="mb" @click="wallet">
							<image src="../static/model2.png" mode=""></image>
							<view class="title1">
								<text style="">钱包</text>
							</view>
							<text><text style="color: #FD664F;">0</text>余额(元)</text>
						</view>
						<view class="mb" @click="makemoney">
							<image src="../static/model3.png" mode=""></image>
							<view class="title1">
								<text style="">赚零钱</text>
							</view>
							<text><text style="color: #FD664F;">0</text>余额(元)</text>
						</view>
					</view>
				</view>
				<view class="vie4">
					<view class="title_icon">
						<image src="../static/title_icon.png" mode=""></image>我的功能
					</view>
					<view class="my_mb">
						<view class="mb" v-for="(item,index) in my_features" :key="index"
							@click="wodedizhi(item,index)">
							<image :src="item.image" mode=""></image>
							<text style="color: black;">{{item.name}}</text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<tabBar :current="current" ref="son"></tabBar>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show: false,
				current: 2,
				user: '',
				showText: false,
				inactiveColor: '#fff',
				activeColor: '#9DE4FE',
				percentage: 0,
				my_features: [{ //我的功能
					image: '../static/wode_1.png',
					name: '我的地址',
					url: './address/wodedizhi'
				}, {
					image: '../static/wode_2.png',
					name: '我的资料',
					url: './personal/index'
				}, {
					image: '../static/wode_3.png',
					name: '我的订单',
					url: './dingdan'
				}, {
					image: '../static/wode_4.png',
					name: '客服中心',
					url: './customer_service/index'
				}],
				marginBottom: 0,
				// phone:''
			}
		},
		onShow() {
			uni.$u.http.post('api/users/getuserallinfo', {
				user_id: uni.getStorageSync('user').user_id,
				campus_id: uni.getStorageSync('campus_id')
			}).then(res => {
				// console.log('获取用户信息', res.data)
				this.user = res.data.data
				// this.phone = res.data.data.phone.substring(0, 3) + '****' + res.data.data.phone.substring(7);
			})
			this.height()
		},
		methods: {
			height() {
				let _this = this
				let data = this.$refs.son.tarbarHeight()
				data.boundingClientRect(function(res) { //data - 各种参数
					_this.marginBottom = res.height
				}).exec()
			},
			open() {},
			close() {
				this.show = false
				// console.log('close');
			},
			wodedizhi(i, e) {
				if (e == 2) {
					uni.switchTab({
						url: i.url
					})
				} else {
					uni.navigateTo({
						url: i.url
					})
				}
			},
			personal(){
				uni.navigateTo({
					url:'./login'
				})
			},
			wallet() {
				uni.navigateTo({
					url: './wallet/index'
				})
			},
			makemoney() {
				uni.navigateTo({
					url: './makemoney/index'
				})
			},
			coupon() {
				uni.navigateTo({
					url: 'coupon/index'
				})
			},
			month() {
				uni.navigateTo({
					url: './card/month'
				})
			}
		}
	}
</script>

<style lang="scss">
	page {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.popup {
		padding: 20rpx;
		overflow-y: scroll;
		max-height: 90vh;
		font-size: 13px;
	}

	.bg-color {
		border-top-left-radius: 60rpx;
		border-top-right-radius: 60rpx;
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
		padding: 34rpx;
		margin-top: 20rpx;

		.wode_card {
			position: relative;
			box-shadow: 0px 0px 10px 0px rgba(51, 51, 51, 0.17);
			border-radius: 30rpx;
			height: 286rpx;

			image {
				width: 100%;
				height: 100%;
			}

			.box {
				position: absolute;
				top: 30rpx;
				left: 30rpx;
				width: 92%;

				.title_icon {
					width: 128rpx;
					height: 46rpx;
					line-height: 46rpx;
					background: #FFFFFF;
					border-radius: 24rpx;
					font-size: 20rpx;
					font-weight: 500;
					color: #FD684F;
					text-align: center;
				}

				.card {
					border-radius: 20rpx;
					color: white;
					width: 75%;

					.name {
						font-size: 66rpx;
						font-family: YouSheBiaoTiHei;
						font-weight: bold;
						color: #FFFFFF;
						font-style: italic;
					}
				}
			}
		}

		.vie4 {
			display: flex;
			flex-flow: column;
			background-color: #FFFFFF;
			border-radius: 30rpx;
			margin: 34rpx auto 0;
			box-shadow: 0px 0px 10px 0px rgba(51, 51, 51, 0.17);
			padding: 30rpx;

			>view {
				font-size: 26rpx;
			}

			.title_icon {
				display: flex;
				align-items: center;
				margin-right: 6rpx;
				margin-bottom: 22rpx;

				image {
					width: 26rpx;
					height: 24rpx;
					margin-right: 6rpx;
				}
			}

			.my_mb {
				display: flex;
				align-items: center;
				justify-content: space-between;

				.mb {
					display: flex;
					flex-flow: column;
					align-items: center;
					font-size: 26rpx;
					color: #808080;

					>image {
						width: 72rpx;
						height: 72rpx;
						margin-bottom: 20rpx;
					}

					.title1 {
						font-size: 22rpx;
						color: black;
						margin-bottom: 14rpx;

						image {
							width: 50rpx;
						}
					}
				}
			}
		}
	}

	.vie1 {
		/* height: 300rpx; */
		background: linear-gradient(to right, #fedb74, #ffbf80);
		display: flex;
		align-items: center;
		padding: 20rpx 5% 20rpx 5%;

		>image {
			width: 100rpx;
			height: 100rpx;
			border-radius: 50%;
		}

		.my {
			display: flex;
			flex-flow: column;

			text {
				font-size: 40rpx;
				color: #101010;
				margin-left: 20rpx;
			}
		}
	}

	.vie2 {
		display: flex;
		justify-content: space-around;
		align-items: center;
		width: 90%;
		height: 140rpx;
		margin: auto;
		background-color: rgba(255, 255, 255, 0.65);
		border-radius: 20rpx;
		font-size: 28rpx;
		color: dimgrey;

		.lab1 {
			font-size: 38rpx;
			font-weight: bold;
			color: black;
			margin-left: 8rpx;
		}
	}
</style>
