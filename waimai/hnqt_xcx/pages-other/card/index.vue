<template>
	<view class="">
		<u-navbar title="会员储值" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="box">
			<view class="crad">
				<image src="../../static/card_bg.png" alt="" mode="widthFix">
				<text>VIP会员卡</text>
			</view>
			<h6>平台储值卡申请获取您的以下个人信息用作用户资料，完善后方可升级卡</h6>
			<u--form labelPosition="left" :model="form" :rules="rules" ref="form1">
				<u-form-item label="手机号码" prop="photo" borderBottom ref="item1" labelWidth="65">
					<u--input v-model="form.photo" border="none" placeholder="必填"></u--input>
				</u-form-item>
				<u-form-item label="姓名" prop="name" borderBottom ref="item1" labelWidth="65">
					<u--input v-model="form.name" border="none" placeholder="必填"></u--input>
				</u-form-item>
				<u-form-item label="性别" prop="sex" borderBottom @click="showSex = true; " ref="item1" labelWidth="65">
					<u--input v-model="form.sex" disabled disabledColor="#ffffff" placeholder="必填" border="none"></u--input>
					<u-icon slot="right" name="arrow-right"></u-icon>
				</u-form-item>
				<u-form-item label="生日" prop="birthday" borderBottom @click="showBirthday = true; " ref="item1" labelWidth="65">
					<u--input v-model="form.birthday" disabled disabledColor="#ffffff" placeholder="必填" border="none"></u--input>
					<u-icon slot="right" name="arrow-right"></u-icon>
				</u-form-item>
			</u--form>
			<u-action-sheet :show="showSex" :actions="actions" title="请选择性别" @close="showSex = false" @select="sexSelect"></u-action-sheet>
			<u-datetime-picker :show="showBirthday" @close="showBirthday = false" mode="date" :closeOnClickOverlay="true"></u-datetime-picker>
		</view>
		<view class="submit" @click="submit">
			<button>填写完成 申请领卡</button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				showSex: false,
				showBirthday: false,
				form: {
					name: '',
					sex: '',
				},
				actions: [{name: '男'},{name: '女'}],
				rules: {
					'photo': {
						type: 'string',
						required: true,
						message: '请填写手机号码',
						trigger: ['blur', 'change']
					},
					'name': {
						type: 'string',
						required: true,
						message: '请填写姓名',
						trigger: ['blur', 'change']
					},
					'sex': {
						type: 'string',
						max: 1,
						required: true,
						message: '请选择男或女',
						trigger: ['blur', 'change']
					},
					'birthday': {
						type: 'string',
						required: true,
						message: '请选择生日',
						trigger: ['blur', 'change']
					},
				},
			};
		},
		methods: {
			sexSelect(e) {
				console.log(e)
				this.form.sex = e.name
				this.$refs.form1.validateField('sex')
			},
			submit() {
				this.$refs.rules.validate().then(res => {
					uni.$u.toast('校验通过')
				}).catch(errors => {
					uni.$u.toast('校验失败')
				})
			}
		},
	};
</script>

<style lang="scss">
	page {
		background: linear-gradient(to bottom, #fefefd, #eeebe1);
	}
	
	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}
	.box{
		margin: 15rpx 20rpx;
		padding: 26rpx 14rpx;
		background: white;
		border-radius: 14rpx;
		box-shadow: 0px 0px 4px 0px rgba(65, 65, 65, 0.22);
		
		.crad{
			position: relative;
			width: 682rpx;
			height: 286rpx;
			image{
				width: 100%;
				height: 100%;
			}
			text{
				position: absolute;
				top: 92rpx;
				left: 86rpx;
				font-size: 84rpx;
				font-family: Source-KeynoteartHans;
				font-weight: 400;
				color: #FFFFFF;
				font-style: italic;
			}
		}
	}
	.box h6{
		color: lightgray;
		font-size: 25rpx;
		margin: 20rpx 0;
	}
	.submit{
		width: 100%;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
		box-sizing: content-box;
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
	.submit text{
		color: lightgray;
		font-size: 25rpx;
	}
</style>
