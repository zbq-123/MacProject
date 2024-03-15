<template>
	<view>
		<u-navbar title="收货地址" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="main">
			<view v-for="(item, index) in address_list" :key="index" :data-index="index" class="order-item"
				@touchstart="drawStart" @touchmove="drawMove" @touchend="drawEnd" :style="'right:'+item.right+'px'">
				<view class="content">
					<view @click="select_adress(index)"
						style="display: flex;flex-direction: column;justify-content: center;padding: 20rpx;">
						<view
							style="font-size: 30rpx;margin-bottom: 10rpx;font-weight: bold;width: 85%;display: flex;align-items: center;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">
							<text :class="item.is_default==1?'is_default':'none'">默认</text>
							<text>{{item.delivery_address}}</text>
						</view>
						<view style="font-size: 26rpx;" v-model="item.delivery_name">
							<text>{{item.delivery_name}}</text>&nbsp;&nbsp;{{item.gender==0?'先生':'女士'}}
							<text v-model="item.delivery_phone">{{item.delivery_phone}}</text>
						</view>
					</view>
					<view @click="edit_adress(index)"
						style="position: absolute;right:0;top: 50%;transform: translate(-50%,-50%);">
						<u-icon name="edit-pen" size="23"></u-icon>
					</view>
				</view>
				<view class="remove" @click="delData(index)">删 除</view>
			</view>
			<view class="vie0" @click="add_address">新增地址</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				address_list: [],
				//左滑默认宽度
				delBtnWidth: 50
			}
		},
		onShow() {
			uni.$u.http.post('api/users/getuseraddresslist', {
				user_id: uni.getStorageSync('user').user_id,
			}).then(res => {
				let address_list = [];
				res.data.data.map((i, k) => {
					address_list.push(Object.assign(i, {
						right: 0
					}))
				})
				this.address_list = address_list
			}).catch(err => {
				console.log(err)
			})
		},
		methods: {
			// 添加地址
			add_address() {
				uni.navigateTo({
					url: './xinzengdizhi'
				})
			},
			//编辑地址
			edit_adress(index) {
				var delivery_address = this.address_list[index].delivery_address
				var delivery_name = this.address_list[index].delivery_name
				var delivery_phone = this.address_list[index].delivery_phone
				var is_default = this.address_list[index].is_default
				var gender = this.address_list[index].gender
				var address_id = this.address_list[index].address_id
				uni.navigateTo({
					url: './xiugaidizhi?delivery_address=' + delivery_address + '&delivery_name=' +
						delivery_name + '&delivery_phone=' + delivery_phone + '&is_default=' + is_default +
						'&address_id=' + address_id + '&gender=' + gender
				})
			},
			//删除地址
			delData(index) {
				uni.$u.http.post('api/users/del_address', {
					address_id: this.address_list[index].address_id,
					user_id: uni.getStorageSync('user').user_id
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '正在删除中',
							icon: 'loading',
							duration: 2000
						});
					}
				})
			},
			// 选择地址
			select_adress(index) {
				let obj = {
					address: this.address_list[index].delivery_address,
					name: this.address_list[index].delivery_name,
					phone: this.address_list[index].delivery_phone,
					address_id: this.address_list[index].address_id,
					gender: this.address_list[index].gender
				}
				var pages = getCurrentPages();
				var prevPage = pages[pages.length - 2]; //上一个页面
				prevPage.$vm.otherFun(obj); //重点$vm
				uni.navigateBack();
			},
			//开始触摸滑动
			drawStart(e) {
				// console.log("开始触发");
				var touch = e.touches[0];
				this.startX = touch.clientX;
			},
			//触摸滑动
			drawMove(e) {
				// console.log("滑动");
				for (var index in this.address_list) {
					this.$set(this.address_list[index], 'right', 0);
				}
				var touch = e.touches[0];
				var item = this.address_list[e.currentTarget.dataset.index];
				var disX = this.startX - touch.clientX;
				if (disX >= 20) {
					if (disX > this.delBtnWidth) {
						disX = this.delBtnWidth;
					}
					this.$set(this.address_list[e.currentTarget.dataset.index], 'right', disX);
				} else {
					this.$set(this.address_list[e.currentTarget.dataset.index], 'right', 0);
				}
			},
			//触摸滑动结束
			drawEnd(e) {
				// console.log("滑动结束");
				var item = this.address_list[e.currentTarget.dataset.index];
				if (item.right >= this.delBtnWidth / 2) {
					this.$set(this.address_list[e.currentTarget.dataset.index], 'right', this.delBtnWidth);
				} else {
					this.$set(this.address_list[e.currentTarget.dataset.index], 'right', 0);
				}
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

	.is_default {
		border-radius: 13rpx;
		font-size: 21rpx;
		padding: 5rpx 10rpx;
		color: white;
		margin-right: 10rpx;
		background: #FFAE43;
	}

	.none {
		display: none;
	}

	.main {
		width: 90%;
		height: auto;
		margin: auto;
		overflow: hidden;
		padding: 0 7rpx;

		.order-item {
			margin: 20rpx auto;
			position: relative;

			.content {
				width: 100%;
				box-shadow: 0px 0px 1rpx 1rpx rgba(65, 65, 65, 0.22);
				background-color: #FFFFFF;
				border-radius: 14rpx;
				position: relative;
			}

			.remove {
				width: 100rpx;
				height: 100%;
				border-radius: 14rpx;
				background-color: red;
				color: #FFFFFF;
				position: absolute;
				top: 0;
				right: -106rpx;
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 32rpx;
			}
		}

		.vie0 {
			width: 514rpx;
			height: 72rpx;
			line-height: 72rpx;
			text-align: center;
			background: linear-gradient(90deg, #FFD43F, #FFA453);
			border-radius: 36rpx;
			font-size: 33rpx;
			font-family: Adobe Heiti Std;
			font-weight: normal;
			color: #313131;
			margin: 20rpx auto;
		}
	}
</style>
