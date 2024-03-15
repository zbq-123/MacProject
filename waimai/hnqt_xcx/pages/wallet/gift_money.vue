<template>
	<view class="gift_money">
		<u--text color="gray" align="center" text="赠送金(元)"></u--text>
		<u--text color="black" align="center" size="55" text="0.00"></u--text>
		<u-button type="primary" shape="circle" text="去充值得赠送金"></u-button>
		<view class="pay">
			<u-icon name="weixin-fill" size="25" color="green"></u-icon>
			<text>微信支付</text>
		</view>
		<view class="popup">
			<text>本平台会员充值，充值100送30元赠送金、200送80元赠送金、300送120元赠送金。
			会员每年生日可享受一次30元内免单机会！
			一元可得1积分，积分到达3888，可从普通会员升级为黄金会员，并增加80元赠送金。
			积分到达6888，可从黄金会员升为白金会员，并增加180元赠送金。
			充值金和赠送金无日期限制。</text>
		</view>
	</view>
</template>

<script>
</script>

<style>
	.popup{
		padding: 20rpx;
		overflow-y: scroll;
		font-size: 28rpx;
	}
	.pay{
		display: flex;
		align-items: center;
		margin: 20rpx 0;
		justify-content: center;
	}
	.gift_money{
		padding: 30rpx;
	}
	.u-text__value{
		font-family: ui-sans-serif;
	}
</style>