<template>
	<view class="makemoney">
		<view class="content">
			<view class="box">
				<view class="tap">
					<text>0</text>
					<text>今日收入</text>
				</view>
				<view class="tap">
					<text>0</text>
					<text>本月收入</text>
				</view>
				<view class="tap">
					<text>0</text>
					<text>上月收入</text>
				</view>
			</view>
			<view class="money">
				<u--text margin="0 0 0 8px" color="#d0d000" size="13px" text="我的收益" align="left"></u--text>
				<!-- <text>累计收益￥0</text><u-icon name="arrow-right"></u-icon> -->
				<u--text suffixIcon="arrow-right" color="#d0d000" size="13px" text="累计收益￥0" align="right" @click="shouyi()"></u--text>
			</view>
		</view>
		<view class="code">
			<!-- <image src="../../static/code.png" mode="widthFix"></image> -->
		</view>
		<text class="know" @click="show = true">达人须知</text>
		<u-popup :show="show" @close="close" @open="open" mode="center">
			<view>
				<text>如何获得佣金?

				自购返佣
				达人购买分销商品，可享受自购省钱，核销后获得自购返佣;
				分享返佣
				达人分享分销商品，商品被购买并核销后，达人赚得分享返佣;

				申请提现，多久到账?

				10元起提，申请提现后，需由平台财务审核后打款至绑定账户。预计1-7个工作日到账。</text>
			</view>
		</u-popup>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show:false
			}
		},
		methods:{
			shouyi(){
				uni.navigateTo({
					url:'./details'
				})
			},
			close() {
			  this.show = false
			  // console.log('close');
			}
		}
	}
</script>

<style scoped>
	.know{
		position: absolute;
		bottom: 40px;
		left: 50%;
		transform: translate(-50%,0);
		font-size: 13px;
		color: #a6a6a6;
	}
	.code{
		position: absolute;
		top: 30%;
		left: 50%;
		transform: translate(-50%,0);
		width: 200px;
	}
	.code image{
		width: 100%;
	}
	.makemoney{
		background-color: #55aaff;
		padding-top: 50px;
	}
	.content{
		background: #383838;
		width: 80%;
		margin: 0 auto;
		border-top-left-radius: 10rpx;
		border-top-right-radius: 10rpx;
		padding: 40rpx;
	}
	.box{
		display: flex;
		justify-content: space-around;
		border-bottom: 1px solid white;
	}
	.tap{
		display: flex;
		flex-flow: column;
		align-items: center;
		color: white;
		font-size: 25rpx;
		line-height: 25px;
	}
	.money{
		margin-top: 20rpx;
		display: flex;
		align-items: center;
	}
</style>