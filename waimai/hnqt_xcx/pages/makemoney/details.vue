<template>
	<view class="">
		<view class="withdrawal">
			<view class="">
				<u--text mode="text" text="可提现余额" size="13"></u--text>
				<u--text mode="price" text="0" size="25" bold="true" lineHeight="40"></u--text>
			</view>
			<button>提现</button>
		</view>
		<u-sticky bgColor="#fff">
		    <u-tabs :list="list1" @click="currnt()" :scrollable="false"></u-tabs>
		</u-sticky>
		<!-- 今日 -->
		<block v-if="currnt1==0">
			<view class="content" v-for="(item,index) in today" :key="index">
				<text>{{item.name}}</text>
				<text>{{item.money}}元</text>
				<text>{{item.time}}</text>
			</view>
		</block>
		<!-- 本月 -->
		<block v-if="currnt1==1">
			<view class="content" v-for="(item,index) in this_month" :key="index">
				<text>{{item.name}}</text>
				<text>{{item.money}}元</text>
				<text>{{item.time}}</text>
			</view>
		</block>
		<!-- 上月 -->
		<block v-if="currnt1==2">
			<view class="content" v-for="(item,index) in last_month" :key="index">
				<text>{{item.name}}</text>
				<text>{{item.money}}元</text>
				<text>{{item.time}}</text>
			</view>
		</block>
		<!-- 累计 -->
		<block v-if="currnt1==3">
			<view class="content" v-for="(item,index) in cumulative" :key="index">
				<text>{{item.name}}</text>
				<text>{{item.money}}元</text>
				<text>{{item.time}}</text>
			</view>
		</block>
	</view>
</template>

<script>
	export default {
		data() {
			return {
                list1: [{
                    name: '今日',
                }, {
                    name: '本月',
                }, {
                    name: '上月'
                }, {
                    name: '累计'
                }],
				today:[{
					name:'好学生',money:'0',time:'2022-10-10'
				},{
					name:'好学生',money:'0',time:'2022-10-10'
				}],
				this_month:[{
					name:'郝老师',money:'0',time:'2022-10-10'
				}],
				last_month:[{
					name:'郝老师',money:'0',time:'2022-10-10'
				},{
					name:'郝老师',money:'0',time:'2022-10-10'
				}],
				cumulative:[{
					name:'郝老师',money:'0',time:'2022-10-10'
				}],
				currnt1:0
			}
		},
		methods: {
            currnt(item) {
                this.currnt1 = item.index
            }
		}
	}
</script>

<style>
	page{
		background: #f3f3f3;
	}
	.withdrawal{
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20px 30rpx;
		background: #f7dd93;
	}
	button{
		margin: 0;
		border: 1px solid black;
		background-color: transparent;
		border-radius: 30px;
		font-size: 12px;
	}
	.content{
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin: 2rpx 0;
		font-size: 14px;
		background: white;
		padding: 5px;
	}
</style>