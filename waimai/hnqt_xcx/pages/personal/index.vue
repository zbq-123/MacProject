<template>
	<view class="">
		<u-navbar title="个人信息" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<u-cell-group>
			<u-cell title="头像" isLink @click="updateImg">
				<image slot="value" class="avatar" :src="user.image?user.image:'http://waimai.com/static/img/student.png'" mode=""></image>
			</u-cell>
			<u-cell title="昵称" isLink>
				<u--input inputAlign="right" slot="value" :placeholder="user.nickname?user.nickname:'昵称'" border="none"
					@change="change"></u--input>
			</u-cell>
			<!-- <u-cell title="生日" isLink :value="birthday" @click="show = true"></u-cell> -->
		</u-cell-group>
		<u-datetime-picker :show="show" closeOnClickOverlay mode="date" @confirm="getDate" @close="close"
			@cancel="close"></u-datetime-picker>
		<button class="btn" @click="submit">确定</button>
		<u-notify ref="uNotify"></u-notify>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show: false,
				birthday: '',
				user: {}
			}
		},
		onLoad() {
			uni.$u.http.post('api/users/getuserallinfo', {
				user_id: uni.getStorageSync('user').user_id
			}).then(res => {
				// console.log('获取用户信息', res.data)
				this.user = res.data.data
				uni.hideLoading()
			}).catch(err => {
				// console.log('shibai', err)
				uni.hideLoading()
			})
			console.log(this.birthday)
		},
		methods: {
			// 昵称
			change(e) {
				this.user.nickname = e
			},
			// 生日
			getDate(e) {
				const timeFormat = uni.$u.timeFormat;
				this.birthday = timeFormat(e.value, 'yyyy-mm-dd');
				this.show = false
			},
			close() {
				this.show = false
			},
			updateImg() {
				uni.chooseImage({
					sourceType: ['album'], //从相册选择
					success: chooseImageRes => {
						console.log('成功', chooseImageRes);
						const tempFilePaths = chooseImageRes.tempFilePaths;
						uni.uploadFile({
							url: 'http://waimai/uploads', //仅为示例，非真实的接口地址
							filePath: tempFilePaths[0],
							name: 'file',
							header: {
								accessToken: uni.getStorageSync('accessToken'),
								platform: 2,
								type: 1
							},
							formData: {
								category: 3
							},
							success: res => {
								// console.log('上传成功', JSON.parse(res.data));
								// uploadFile上传成功后，根据和后台的约定msgCode判断接口调用状态
								let data = JSON.parse(res.data);
								// 成功：获取到头像
								if (data.msgCode == 200) {
									this.user.image = JSON.parse(res.data).data;
									// 更新当前页面数据
									this.updateUserInfo();
								}
								// 失败报错
								if (data.msgCode == 500) {
									this.myToast(data.msgContent, 'none');
								}
								// 登陆过期
								if (data.msgCode == 311 || data.msgCode == 312) {
									myToast(
										data.msgContent,
										'none',
										() => {
											const res = uni.getStorageInfoSync();
											for (let s of res.keys) {
												// 保留账号密码
												if (s === 'pwd' || s === 'lang') {
													// console.log('保留账号密码', s)
												} else {
													uni.removeStorageSync(s);
												}
											}
											uni.reLaunch({
												url: '/pages/group/index'
											});
										},
										1000
									);
								}
							}
						});
					},
					fail: err => {
						this.myToast('图片上传失败', 'none');
					}
				});
			},
			submit() {
				if (this.user.image == '' && this.user.nickname == '') {
					uni.showToast({
						title: '请修改内容',
						icon: "none"
					})
				} else {
					uni.showToast({
						title: '暂未开发',
						icon: "none"
					})
				}
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

	.avatar {
		width: 60rpx;
		height: 60rpx;
		border-radius: 50%;
	}

	.btn {
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
</style>
