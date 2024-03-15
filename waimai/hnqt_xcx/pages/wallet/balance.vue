<template>
	<view class="balance">
		<u--text color="gray" align="center" text="余额(元)"></u--text>
		<u--text color="black" align="center" size="40" text="0.00"></u--text>
		<u--text color="black" align="center" suffixIcon="arrow-right" text="去提现"></u--text>
		<u-line margin="30rpx 0"></u-line>
		<text style="font-size: 40rpx;">余额账户充值</text>
		<view class="r_balance">
			<text :class="index==current?'active':''" v-for="(item,index) in baseList" :key="index" @click="select(index)">
				{{item.price}}{{item.price!=''?'元':'自定义金额'}}
			</text>
		</view>
		<u-toast ref="uToast" />
		<view class="pay">
			<u-checkbox-group @change="change">
				<u-checkbox label="本人阅读并同意" shape="circle" :checked="checked"></u-checkbox>
			</u-checkbox-group>
			<text style="color: deepskyblue;" @click="show = true">《校园外卖充值须知》</text>
			<u-popup :show="show" @close="close" @open="open" mode="center" round="10" closeable="true" safeAreaInsetTop="true" customStyle="width:80%">
				<view class="popup">
					<text>本平台会员充值，充值100送30元赠送金、200送80元赠送金、300送120元赠送金。
					会员每年生日可享受一次30元内免单机会！
					一元可得1积分，积分到达3888，可从普通会员升级为黄金会员，并增加80元赠送金。
					积分到达6888，可从黄金会员升为白金会员，并增加180元赠送金。
					充值金和赠送金无日期限制。</text>
				</view>
			</u-popup>
		</view>
		<u-button type="primary" shape="circle" text="开通并充值" @click="openPay"></u-button>
		<view class="pay">
			<u-icon name="weixin-fill" size="25" color="green"></u-icon>
			<text>微信支付</text>
		</view>
		<u--text color="gray" align="center" suffixIcon="arrow-right" text="该服务由圈圈科技互联网提供"></u--text>
		<u-notify ref="uNotify"></u-notify>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				checked:false,
				current:0,
				show: false,
				baseList: [{
						price: 66
					},
					{
						price: 100
					},
					{
						price: 200
					},
					{
						price: 300
					},
					{
						price: 500
					},
					{
						price: ''
					},
				]
			}
		},
		methods: {
			select(i) {
				this.current = i;
			},
			open() {
			  // console.log('open');
			},
			close() {
			  this.show = false
			  // console.log('close');
			},
			change(){
				this.checked = !this.checked
			},
			openPay(){
				if(this.checked==false){
					this.$refs.uNotify.primary('请阅读并同意')
				}else{
					this.$refs.uNotify.primary('接口')
				}
			}
		}
	}
</script>

<style>
	.popup{
		padding: 20rpx;
		overflow-y: scroll;
		max-height: 90vh;
	}
	.active{
		border: 1px solid deepskyblue!important;
		background: #aaffff!important;
	}
	.pay{
		display: flex;
		align-items: center;
		margin: 20rpx 0;
		justify-content: center;
	}
	.balance{
		padding: 30rpx;
	}
	.u-text__value{
		font-family: ui-sans-serif;
	}
	.r_balance{
		display: flex;
		flex-wrap: wrap;
		margin-top: 30rpx;
	}
	.r_balance text{
		width: 30%;
		text-align: center;
		padding: 50rpx 0;
		background: #f0f0f0;
		border-radius: 5px;
		margin: 0 29rpx 29rpx 0;
		border: 1px solid transparent;
	}
	.r_balance text:nth-child(3n + 3) {
	  margin-right: 0!important;
	}
</style>