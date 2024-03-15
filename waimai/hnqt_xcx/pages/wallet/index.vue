<template>
	<view class="wallet">
		<view class="wallet_box">
			<text>总资产(元) 0.00</text>
			<view class="right" @click="detailed">
				<u-icon name="list-dot" color="#fff"></u-icon>
				<text>全部明细</text>
			</view>
		</view>
		<view v-show="show" class="zhandan">
			<view class="detailed" v-for="(item,index) in detail" :key="index">
				<text>{{item.price}}</text>
				<text>{{item.creationTime}}</text>
			</view>
		</view>
		<view class="recharge">
			<view class="r_recharge">
				<view class="balance">
					<u--text color="#8b8b8b" text="账户余额(元)" suffixIcon="arrow-right" @click="balance"></u--text>
					<u--text mode="text" text="0.00" size="30" bold lineHeight="50"></u--text>
				</view>
				<view class="balance">
					<u--text color="#8b8b8b" text="赠送金(元)" suffixIcon="arrow-right" @click="gift_money"></u--text>
					<u--text mode="text" text="0.00" size="30" bold lineHeight="50"></u--text>
				</view>
			</view>
			<button>开通并充值</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
		    return {
		        show:false,
				detail:[{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				},{
					price:0.00,creationTime:'2022-10-10'
				}]
		    }
		},
		methods: {
			balance(){
				uni.navigateTo({
					url: './balance',
				})
			},
			gift_money(){
				uni.navigateTo({
					url: './gift_money'
				})
			},
			detailed(){
				this.show = !this.show
			}
		}
	}
</script>

<style scoped>
	.zhandan{
		overflow-y: scroll;
		max-height: 60vh;
		animation: bounceInLeft 1s ease infinite;
		animation-iteration-count:1;
		animation-fill-mode:forwards;
	}
	.detailed{
		width: 100%;
		background: #f0f0f0;
		display: flex;
		align-items: center;
		justify-content: space-around;
		line-height: 30px;
		color: black;
	}
	@keyframes bounceInLeft {
	  from {
	    height: 0;
	  }
	  to {
	    height: 60vh;
	  }
	}
	.detailed:last-child{
		padding-bottom: 10px;
	}
	.wallet{
		background: linear-gradient(to bottom,#f7dd93,white);
		padding: 40rpx;
	}
	.wallet_box{
		display: flex;
		align-items: center;
		justify-content: space-between;
		color: white;
		padding: 40rpx;
		background: #55aaff;
		border-top-left-radius: 20rpx;
		border-top-right-radius: 20rpx;
	}
	.right{
		display: flex;
		align-items: center;
	}
	.recharge{
		border-radius: 20rpx;
		padding: 40rpx;
		background: white;
		margin-top: -10px;
	}
	.recharge button{
		background: #55aaff;
		color: white;
		border-radius: 15px;
		font-size: 15px;
		width: 50%;
		margin-top: 30px;
		display: inline-block;
	}
	.r_recharge{		
		display: flex;
		align-items: center;
	}
	.balance{
		width: 50%;
		border-right: 1px solid #b9b9b9;
		margin-right: 20rpx;
	}
	.balance:last-child{
		border: 0;
	}
</style>