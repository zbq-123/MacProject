<template>
	<view class="">
		<u-navbar title="添加评论" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<view class="model_jiaxiao">
			<view class="jiaxiao">
				<view class="comments" v-for="(item,index) in commont" :key="index" @click="selet(item,index)">
					<text>{{item.name}}</text>
					<image v-if="current==index" :src="item.img" mode="widthFix"></image>
					<image v-else :src="item.img_on" mode="widthFix"></image>
				</view>
			</view>
			<u--textarea v-model="value3" height="129" placeholder="服务是否周到？环境如何呢？有什么需要改善的呢？帮助我们改善,给您更优质的服务~">
			</u--textarea>
			<u-upload :fileList="fileList1" @afterRead="afterRead" @delete="deletePic" name="1" multiple :maxCount="9">
			</u-upload>
			<view class="open">
				<text>匿名评价</text>
				<u-switch v-model="value" activeColor="#09B907" size="20" @change="abc(value)"></u-switch>
			</view>
		</view>
		<button @click="submit">提交评论</button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				url1: 'http://waimai.com',
				value: false,
				commont: [{
					name: '差',
					img: '../static/add_dommont/icon_5.png',
					img_on: '../static/add_dommont/icon_5on.png',
					value: 1
				}, {
					name: '一般',
					img: '../static/add_dommont/icon_1.png',
					img_on: '../static/add_dommont/icon_1on.png',
					value: 2
				}, {
					name: '还不错',
					img: '../static/add_dommont/icon_2.png',
					img_on: '../static/add_dommont/icon_2on.png',
					value: 3
				}, {
					name: '很满意',
					img: '../static/add_dommont/icon_4.png',
					img_on: '../static/add_dommont/icon_4on.png',
					value: 4
				}, {
					name: '强烈推荐',
					img: '../static/add_dommont/icon_3.png',
					img_on: '../static/add_dommont/icon_3on.png',
					value: 5
				}],
				current: 3, //默认评价
				value3: '',
				fileList1: [],
				grade: 4,
				train: {},
				user_image: '',
				user_name: '',
				content_image: []
			}
		},
		onLoad(option) {
			let str = decodeURIComponent(option.train)
			this.train = JSON.parse(str) //驾校详情

			uni.$u.http.post('api/users/getuserallinfo', {
				user_id: uni.getStorageSync('user').user_id, //用户id
			}).then(res => {
				// console.log(res.data)
				this.user_image = res.data.data.image
				this.user_name = res.data.data.nickname
			})
		},
		methods: {
			selet(i, e) {
				this.current = e
				this.grade = i.value
			},
			submit() {
				uni.$u.http.post('api/train/add_comment', {
					train_id: this.train.id,
					train_name: this.train.name,
					user_id: uni.getStorageSync('user').user_id, //用户id
					user_image: this.user_image?this.user_image:'http://waimai.com/static/img/student.png',
					user_name: this.value == true ? '匿名用户' : this.user_name,
					content_image: this.content_image.join(","),
					content: this.value3, //内容
					grade: this.grade, //等级
				}).then(res => {
					if (res.data.code == 200) {
						uni.showToast({
							title: '评论成功',
							icon:"success",
							duration: 2000
						});
						setTimeout(function() {
							uni.redirectTo({
								url: './commont'
							})
						}, 2000);
					} else {
						uni.showToast({
							title: res.data.msg,
							icon: "error"
						})
					}
				})
			},
			// 删除图片
			deletePic(event) {
				this[`fileList${event.name}`].splice(event.index, 1)
			},
			// 新增图片
			async afterRead(event) {
				// 当设置 mutiple 为 true 时, file 为数组格式，否则为对象格式
				let lists = [].concat(event.file)
				let fileListLen = this[`fileList${event.name}`].length
				lists.map((item) => {
					this[`fileList${event.name}`].push({
						...item,
						status: 'uploading',
						message: '上传中'
					})
				})
				for (let i = 0; i < lists.length; i++) {
					const result = await this.uploadFilePromise(lists[i].url)
					let item = this[`fileList${event.name}`][fileListLen]
					this[`fileList${event.name}`].splice(fileListLen, 1, Object.assign(item, {
						status: 'success',
						message: '',
						url: result
					}))
					fileListLen++
				}
			},
			uploadFilePromise(url) {
				return new Promise((resolve, reject) => {
					let a = uni.uploadFile({
						url: 'http://waimai.com/api/train/upload_image',
						filePath: url,
						name: 'file',
						success: (res) => {
							this.content_image.push(this.url1 + JSON.parse(res.data).img)
							resolve(res.data.data)
						}
					});
				})
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

	button {
		width: 492rpx;
		height: 70rpx;
		line-height: 70rpx;
		text-align: center;
		background: linear-gradient(90deg, #FFD43F, #FFA453);
		border-radius: 34rpx;
		font-size: 32rpx;
		margin-top: 44rpx;
	}

	.model_jiaxiao {
		margin: 20rpx;
		background: #FFFFFF;
		box-shadow: 0rpx 0rpx 26rpx 0rpx rgba(0, 0, 0, 0.17);
		border-radius: 14rpx;
		margin-bottom: 20rpx;
		margin-bottom: constant(safe-area-inset-bottom);
		margin-bottom: env(safe-area-inset-bottom);
		padding: 26rpx;

		.jiaxiao {

			display: flex;
			align-items: center;

			.comments {
				display: flex;
				width: 20%;
				flex-flow: column;
				justify-content: space-between;
				align-items: center;

				text {
					font-size: 22rpx;
					color: #222222;
				}

				image {
					width: 68rpx;
					height: 70rpx;
					margin-top: 8rpx;
				}
			}
		}

		.u-textarea {
			background-color: #F3F3F3 !important;
			margin-top: 24rpx;
			margin-bottom: 14rpx;

			textarea {
				font-size: 20rpx !important;
			}
		}

		.open {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 26rpx;
			color: #000000;
		}

		:last-child {
			margin-bottom: 0;
		}
	}
</style>
