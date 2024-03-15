<template>
	<view>
		<u-navbar title="新增地址" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="vie2">
			<ul class='u0'>
				<li>
					<view style='display: flex;align-items: center; font-size: 30rpx;'>
						<text class="left">联系人</text>
						<input type="text" placeholder="姓名" v-model="name">
					</view>
				</li>
				<li>
					<view style='display: flex;align-items: center;font-size: 30rpx;text-align: center;'>
						<text class="left"></text>
						<view style="display: flex;align-items: center;">
							<label @click="sex(0)" :class="gender==0?'btn1':'btn'">
								<!-- {{gender==0?'✔ ':'● '}} -->
								<radio value="0" :checked="gender==0" />先生
							</label>
							<label @click="sex(1)" :class="gender==1?'btn1':'btn'">
								<!-- {{gender==1?'✔ ':'● '}} -->
								<radio value="1" :checked="gender==1" />女士
							</label>
						</view>
					</view>
				</li>
				<li>
					<view style='display: flex;align-items: center;font-size: 30rpx;'>
						<text class="left">电话</text>
						<input type="number" placeholder="手机号码" v-model="tel">
					</view>
				</li>
				<li>
					<view style='display: flex;align-items: center;font-size: 30rpx;'>
						<text class="left">地址</text>
						<input type="text" placeholder="收货地址:包含门牌号" v-model="dizhi" />
					</view>
				</li>
				<li>
					<view style='display: flex;align-items: center;font-size: 30rpx;text-align: center;'>
						<text class="left">默认地址</text>
						<view style="display: flex;align-items: center;">
							<label @click="radio(0)" :class="is_default==0?'btn1':'btn'">
								<!-- {{is_default==0?'✔ ':'● '}} -->
								<radio value="0" :checked="is_default==0" />普通
							</label>
							<label @click="radio(1)" :class="is_default==1?'btn1':'btn'">
								<!-- {{is_default==1?'✔ ':'● '}} -->
								<radio value="1" :checked="is_default==1" />默认
							</label>
						</view>
					</view>
				</li>
			</ul>
		</view>
		<view style="position: relative;">
			<view class="vie5" :style="{display: `${displayStatic}`}" @click="submit">
				<button>保存</button>
			</view>
			<view class="vie5" :style="{display: `${displayStatic1}`}">
				<button>提交中</button>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				name: '',
				dizhi: '',
				tel: '',
				gender: 0, //是否男女
				is_default: 0, //是否默认
				displayStatic: 'block',
				displayStatic1: 'none',
			};
		},
		methods: {
			submit() {
				// 手机正则
				var phone = this.tel;
				if (this.name == '') {
					uni.showToast({
						title: '姓名不能为空',
						icon: 'none'
					})
					return false;
				} else if (phone == '') {
					uni.showToast({
						title: '手机号码不能为空',
						icon: 'none'
					})
					return false;
				} else if (!(/^1(3|4|5|6|7|8|9)\d{9}$/.test(phone))) {
					uni.showToast({
						title: '手机号码格式不正确',
						icon: 'none'
					})
					return false;
				} else if (this.dizhi == '') {
					uni.showToast({
						title: '地址不能为空',
						icon: 'none'
					})
					return false;
				}
				uni.$u.http.post('api/users/add_address', {
					user_id: uni.getStorageSync('user').user_id,
					delivery_name: this.name,
					delivery_phone: phone,
					delivery_address: this.dizhi,
					is_default: this.is_default, //是否默认收货地址 0否 1是
					gender: this.gender, //男或女
				}).then(res => {
					if (res.data.code == 200) {
						uni.navigateBack({
							delta: 1
						})
					}else{
						uni.showToast({
							title: res.data.msg,
							icon: 'loading',
							duration:2000
						})
						setTimeout(()=>{
							uni.navigateTo({
								url:'/pages/login'
							})
						},2000)
					}
				})
				this.displayStatic = 'none'
				this.displayStatic1 = 'block'
			},
			sex(e) {
				this.gender = e
			},
			radio(e) {
				this.is_default = e
				// console.log(this.is_default)
			}
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
	.left {
		width: 140rpx;
		text-align: left;
	}

	.vie2 {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 30rpx 20rpx;
	}

	.u0 {
		background-color: #FFFFFF;
		width: 100%;
		border-radius: 20rpx;
		box-shadow: 0px 0px 4px 0px rgba(65, 65, 65, 0.22);
	}

	.u0 li {
		border-bottom: 1rpx solid #F5F5F5;
		padding: 30rpx 28rpx;
	}

	page {
		background-color: #F5F5F5;
	}

	.vie01 {
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 30rpx;
		width: 100rpx;
		height: 50rpx;
		line-height: 50rpx;
		border-radius: 10rpx;
	}

	.vie02 {
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 30rpx;
		width: 100rpx;
		height: 50rpx;
		line-height: 50rpx;
		justify-content: center;
		margin-left: 20rpx;
		border-radius: 10rpx;
	}

	.vie5 {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		button{
			width: 514rpx;
			height: 72rpx;
			line-height: 72rpx;
			text-align: center;
			background: linear-gradient(90deg, #FFD43F, #FFA453);
			border-radius: 36rpx;
			font-size: 38rpx;
			font-family: Adobe Heiti Std;
			font-weight: normal;
			color: #313131;
			margin: auto;
		}
	}

	radio {
		display: none;
	}

	.btn {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 22rpx;
		width: 98rpx;
		height: 40rpx;
		line-height: 40rpx;
		background: #FFFFFF;
		border: 2rpx solid #9F9F9F;
		border-radius: 20rpx;
		font-size: 20rpx;
		font-family: Adobe Heiti Std;
		font-weight: normal;
		color: #9F9F9F;
	}

	.btn1 {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 22rpx;
		width: 98rpx;
		height: 40rpx;
		line-height: 40rpx;
		background: #FFFFFF;
		border: 2rpx solid #FFA842;
		border-radius: 20rpx;
		font-size: 20rpx;
		font-family: Adobe Heiti Std;
		font-weight: normal;
		color: #FFA842;
	}
</style>
