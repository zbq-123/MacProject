<template>
	<view class="">
		<u-navbar title="驾校" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="model_jiaxiao" :style="[{paddingBottom:windowHeight}]" v-show="isShow">
			<image :src="url1+jiaxiao.image" mode="widthFix" style="width: 100%;margin-bottom: 18rpx;"></image>
			<view class="jiaxiao">
				<text>{{jiaxiao.name}}</text>
				<view style="display: flex;align-items: center;color: #818181;font-size: 22rpx;padding-top: 14rpx;">
					<u-rate :gutter="0" :count="5" activeColor="#FDC401" size="13" v-model="jiaxiao.average_grade"
						allowHalf readonly></u-rate>
					<text style="margin-left: 14rpx;color: #FDC401;">{{jiaxiao.average_grade}}</text>
					<text style="margin-left: 36rpx;">用户评价：{{jiaxiao.count_comment}}</text>
					<text
						style="margin-left: 36rpx;">{{jiaxiao.count_experience>0?jiaxiao.count_experience+'人已体验':''}}</text>
				</view>
				<u-line length="100%" margin="6px"></u-line>
				<u--text prefixIcon="map-fill" iconStyle="font-size: 22rpx" size="11" lineHeight="20" :text="jiaxiao.address">
				</u--text>
				<u--text prefixIcon="phone-fill" iconStyle="font-size: 22rpx" size="11" :text="jiaxiao.phone"></u--text>
			</view>
			<view class="jiaxiao">
				<view class="title">
					<view>
						<image src="../static/icon4.png" mode="widthFix"></image>
						<text>选班报名</text>
					</view>
				</view>
				<!-- <view class="tab">
					<text v-for="(item,index) in tabs" :key="index" :class="curren==index?'active':''"
						@click="tab(index)">{{item.name}}</text>
				</view> -->
				<view class="coupon" v-for="(item,index) in jiaxiao.experience" :key="index">
					<view class="coupon_1">
						<image :src="url1+item.image" mode=""></image>
						<view class="">
							<text>{{item.name}}</text>
							<text>零基础入门|1对1服务</text>
							<text>￥{{item.price}}</text>
						</view>
					</view>
					<button @click="buy(item)">购 买</button>
				</view>
			</view>
			<view class="jiaxiao">
				<view class="title">
					<view>
						<image src="../static/icon3.png" mode="widthFix"></image>
						<text>教练团队</text>
					</view>
				</view>
				<u-scroll-list @right="right" @left="left" :indicator="false">
					<view class="scroll-list" style="flex-direction: row;">
						<view class="scroll-list__goods-item" v-for="(item, index) in jiaxiao.coach" :key="index"
							:class="[(index === 9) && 'scroll-list__goods-item--no-margin-right']">
							<image class="scroll-list__goods-item__image" :src="url1+item.image"></image>
							<text class="scroll-list__goods-item__text">{{ item.name }}</text>
							<text class="scroll-list__goods-item__text">{{ item.phone }}</text>
						</view>
					</view>
				</u-scroll-list>
			</view>
			<view class="jiaxiao">
				<view class="title">
					<view>
						<image src="../static/icon8.png" mode="widthFix"></image>
						<text>品牌介绍</text>
					</view>
				</view>
				<view class="content">
					<u-parse :content="jiaxiao.content"></u-parse>
				</view>
			</view>
			<view class="jiaxiao">
				<navigator url="/pages-other/train/commont" class="title">
					<view>
						<image src="../static/icon7.png" mode="widthFix"></image>
						<text>用户评价 ({{jiaxiao.count_comment}})</text>
					</view>
					<image src="../static/right.png" mode="widthFix"></image>
					<!-- <u-icon name="arrow-right"></u-icon> -->
				</navigator>
				<!-- <view class="rule">
					<text>环境很好 10</text>
					<text>j价格实惠 10</text>
					<text>环境很好 10</text>
					<text>环境很好 10</text>
					<text>环境很好 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
				</view> -->
				<view class="u-page">
					<view class="u-demo-block">
						<view class="u-demo-block__content">
							<view class="album" v-for="(item,index) in jiaxiao.comment" :key="index" v-if="index<1">
								<view class="">
									<image :src="item.user_image" mode=""
										style="width: 76rpx;height: 76rpx;border-radius: 50%;"></image>
								</view>
								<view class="album__content">
									<u--text :text="item.user_name" color="#000" bold size="13"></u--text>
									<u--text :text="item.update_time" color="#c2c2c2" size="10"></u--text>
									<u-rate :gutter="0" :count="5" size="13" activeColor="#FDC401" allowHalf
										v-model="item.grade" readonly></u-rate>
									<u-parse :content="item.content"></u-parse>
									<u-album :urls="item.content_image"></u-album>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="jiaxiao_bottom" v-show="isShow">
			<view class="pinjia" @click="pinjia">
				<image src="../static/icon5.png" mode="widthFix"></image>
				<text>评价</text>
			</view>
			<!-- <view class="pinjia">
				<image src="../static/icon6.png" mode="widthFix"></image>
				<text>领券</text>
			</view> -->
			<button class="btn2" @click="btn1">电话咨询</button>
			<!-- <button class="btn2">选班报名</button> -->
		</view>
		<u-empty mode="data" :text="empty" icon="http://cdn.uviewui.com/uview/empty/data.png" :show="!isShow"></u-empty>
	</view>
</template>

<script>
	import qqmapsdk from '@/libs/qqmap-wx-jssdk.js'
	export default {
		data() {
			return {
				url1: 'http://waimai.com',
				jiaxiao: {},
				albumWidth: 0,
				curren: 0,
				empty: '该校区暂未添加校区',
				tabs: [{
					name: '全部',
				}, {
					name: '手动挡C1',
				}, {
					name: '自动挡C2'
				}],
				windowHeight: 0,
				address: '',
				isShow: true
			}
		},
		onShow() {
			var _this = this
			const QQMapWX = new qqmapsdk({
				key: '7Q6BZ-MWYCW-PKCRD-OSX7N-CCZMT-DBBHH'
			});
			uni.$u.http.post('api/train/getTraininfo', {
				campus_id: uni.getStorageSync('campus_id')
			}).then(res => {
				if (res.data.code == 200) {
					_this.jiaxiao = res.data.data
					QQMapWX.reverseGeocoder({
						location: {
							latitude: res.data.data.lat,
							longitude: res.data.data.lon
						},
						success: function(res) {
							_this.address = res.result.address
						},
					})
				} else {
					this.isShow = false
				}
			})
		},
		onReady() {
			setTimeout(() => {
				let _that = this;
				uni.getSystemInfo({
					success: function(res) {
						let bottom = uni.createSelectorQuery().select(".jiaxiao_bottom");
						bottom.boundingClientRect(function(datad) { //data - 各种参数
							_that.windowHeight = datad.height + 10 + 'px';
						}).exec()
					}
				});
			}, 300)
		},
		methods: {
			btn1() {
				uni.makePhoneCall({
					phoneNumber: this.jiaxiao.phone, //电话号码
					success: function(e) {
						console.log(e);
					},
					fail: function(e) {
						console.log(e);
					}
				})
			},
			left() {
				console.log('left');
			},
			right() {
				console.log('right');
			},
			tab(e) {
				this.curren = e
			},
			pinjia() {
				uni.navigateTo({
					url: './add_commont?train=' + encodeURIComponent(JSON.stringify(this.jiaxiao))
				})
			},
			buy(i) {
				uni.$u.http.post('api/train/wxpay_train_experience', {
					user_id: uni.getStorageSync('user').user_id,
					experience_id: i.id,
					train_id: this.jiaxiao.id
				}).then(res => {
					uni.requestPayment({
						provider: 'wxpay',
						timeStamp: res.data.data.timeStamp,
						nonceStr: res.data.data.nonceStr,
						package: res.data.data.package,
						signType: res.data.data.signType,
						paySign: res.data.data.paySign,
						success: function(ress) {
							uni.switchTab({
								url: '/pages/dingdan'
							})
						},
						fail: function(err) {
							uni.redirectTo({
								url: ''
							})
						}
					});
				})
			}
		}
	}
</script>

<style lang="scss">
	.tab {
		display: flex;
		align-items: center;
		margin-bottom: 30rpx;

		text {
			padding: 10rpx 20rpx;
			text-align: center;
			border: 2rpx solid #777777;
			border-radius: 6rpx;
			font-size: 22rpx;
			color: #777777;
			margin-right: 10rpx;
		}

		.active {
			border: 2rpx solid #FFB514;
			color: #FFB514;
		}
	}
	.u-scroll-list{
		padding: 0!important;
	}
	.scroll-list {
		@include flex(column);

		.scroll-list__goods-item {
			width: 20%;
			display: flex;
			flex-flow: column;
			align-items: center;
			border-radius: 6rpx;
			box-shadow: 0px 0px 6rpx 0px rgba(0, 0, 0, 0.23);
		}

		&__goods-item {
			margin: 10rpx;
			padding: 12rpx;

			&__image {
				width: 120rpx;
				height: 120rpx;
				border-radius: 8rpx;
			}

			&__text {
				color: #000;
				text-align: center;
				font-size: 12px;
				margin-top: 5px;
			}
		}

		&__show-more {
			background-color: #fff0f0;
			border-radius: 3px;
			padding: 3px 6px;
			@include flex(column);
			align-items: center;

			&__text {
				font-size: 12px;
				width: 12px;
				color: #f56c6c;
				line-height: 16px;
			}
		}
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.jiaxiao_bottom {
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
		width: 100%;
		background: #FFFFFF;
		box-shadow: 0rpx 0rpx 36rpx 0rpx rgba(51, 51, 51, 0.11);
		display: flex;
		align-items: center;
		justify-content: space-evenly;
		position: fixed;
		bottom: 0;
		height: 100rpx;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);

		.pinjia {
			display: flex;
			flex-flow: column;
			align-items: center;

			image {
				width: 48rpx;
			}

			text {
				font-size: 20rpx;
				font-family: Adobe Heiti Std;
				font-weight: normal;
				color: #FFC72F;
			}
		}

		button {
			width: 212rpx;
			height: 70rpx;
			line-height: 70rpx;
			margin: 0;
			font-size: 32rpx;
			border-radius: 34rpx;
		}

		.btn1 {
			background: #D4D4D4;
			color: #FFFFFF;
		}

		.btn2 {
			background: linear-gradient(90deg, #FFD43F, #FFA453);
			color: #313131;
		}
	}

	.model_jiaxiao {
		padding: 20rpx;
		margin-bottom: constant(safe-area-inset-bottom);
		margin-bottom: env(safe-area-inset-bottom);

		.jiaxiao {
			padding: 18rpx;
			background: #FFFFFF;
			box-shadow: 0rpx 0rpx 26rpx 0rpx rgba(0, 0, 0, 0.17);
			border-radius: 14rpx;
			margin-bottom: 20rpx;

			.rule {
				text {
					padding: 10rpx 14rpx;
					background: #FFF2D3;
					border-radius: 6rpx;
					font-size: 16rpx;
					color: #5F5F5F;
					margin-right: 17rpx;
					margin-bottom: 14rpx;
					display: inline-block;
				}
			}

			.coupon {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 12rpx;

				.coupon_1 {
					display: flex;
					align-items: center;
					justify-content: space-between;

					image {
						width: 136rpx;
						height: 130rpx;
						margin-right: 22rpx;
					}

					view {
						display: flex;
						flex-flow: column;
						justify-content: space-between;
						line-height: 43rpx;

						text:nth-child(1) {
							font-size: 26rpx;
							color: #000000;
						}

						text:nth-child(2) {
							font-size: 20rpx;
							color: #868686;
						}

						text:nth-child(3) {
							font-size: 30rpx;
							color: #EE3E3F;
						}
					}
				}

				button {
					background: #FF5B29;
					border-radius: 40rpx;
					font-size: 26rpx;
					color: #FFFFFF;
					margin: 0;
					height: 46rpx;
					line-height: 46rpx;
				}
			}

			.title {
				view {
					display: flex;
					align-items: center;
				}

				display: flex;
				align-items: center;
				position: relative;
				margin-bottom: 14rpx;
				justify-content: space-between;

				image {
					width: 30rpx;
					margin-right: 8rpx;
				}

				text {
					font-size: 26rpx;
					font-family: Adobe Heiti Std;
					font-weight: normal;
					color: #101010;
				}
			}

			.rule {
				text {
					padding: 10rpx 14rpx;
					background: #FFF2D3;
					border-radius: 6rpx;
					font-size: 20rpx;
					color: #5F5F5F;
					margin-right: 17rpx;
					margin-bottom: 14rpx;
					display: inline-block;
				}
			}

			.content {
				font-size: 23rpx;
				color: #232323;
			}
		}
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
</style>
