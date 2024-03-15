<template>
	<view class="">
		<u-navbar title="用户评论" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		
		<view class="model_jiaxiao">
			<!-- <view class="jiaxiao">
				<view class="rule">
					<text>环境很好 10</text>
					<text>j价格实惠 10</text>
					<text>环境很好 10</text>
					<text>环境很好 10</text>
					<text>环境很好 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
					<text>很划算 10</text>
				</view>
			</view> -->
			<view class="jiaxiao" v-for="(item,index) in jiaxiao.comment" :key="index">
				<view class="u-page">
					<view class="u-demo-block">
						<view class="u-demo-block__content">
							<view class="album">
								<view class="">
									<image :src="item.user_image" mode="" style="width: 76rpx;height: 76rpx;border-radius: 50%;"></image>
								</view>
								<view class="album__content">
									<u--text :text="item.user_name" color="#000" bold size="13"></u--text>
									<u--text :text="item.update_time" color="#c2c2c2" size="10"></u--text>
									<u-rate :gutter="0" :count="5" size="13" activeColor="#FDC401" allowHalf v-model="item.grade" readonly></u-rate>
									<u-parse :content="item.content"></u-parse>
									<u-album :urls="item.content_image" multipleSize="80"></u-album>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				jiaxiao:[]
			}
		},
		onLoad() {
			uni.$u.http.post('api/train/getTraininfo', {
				campus_id: uni.getStorageSync('campus_id')
			}).then(res => {
				this.jiaxiao = res.data.data
			})
		},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.model_jiaxiao {
		padding: 20rpx;
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
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
					font-size: 20rpx;
					color: #5F5F5F;
					margin-right: 17rpx;
					margin-bottom: 14rpx;
					display: inline-block;
				}
			}
		}

		:last-child {
			margin-bottom: 0;
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
