<template>
	<view class="">
		<u-navbar title="发布闲置物品" :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
			:placeholder="true" :titleStyle="{
			'fontsize':'10',
			color:'#000000'
		}">
		</u-navbar>
		<u-notify ref="uNotify"></u-notify>
		<u-notice-bar :text="text1" bgColor="#f7dd93" color="#ff3e38"></u-notice-bar>
		<view class="add_idle">
			<input class="goods_name" type="text" v-model="name" placeholder="请输入物品名称">

			<u--textarea v-model="content" :placeholder="placeholder" count height="130" border="none"></u--textarea>

			<u-upload :fileList="fileList3" @afterRead="afterRead" @delete="deletePic" name="3" multiple :maxCount="10"
				:previewFullImage="true" width="77" height="77"></u-upload>

			<view class="grid" @click="create_show = true">
				<u-icon :name="JSON.stringify(category) == '{}'?grid:url1+category.image" size="23" labelSize="10"
					:label="JSON.stringify(category) == '{}'?'上传分类':category.name" labelPos="bottom"></u-icon>
			</view>

			<view class="price" @click="show = true">
				<u-icon name="rmb-circle" size="19" label="价格" labelColor="#000"></u-icon>
				<u-icon name="arrow-right" :label="'￥'+price" labelSize="18" labelPos="left" labelColor="#B20606">
				</u-icon>
			</view>

			<u-popup :show="show" mode="bottom" @close="close" safeAreaInsetTop>
				<view class="price_i">
					<text>价格</text>
					<input type="price" placeholder="￥0.00" v-model="price">
				</view>
				<view class="price_i">
					<text>原价</text>
					<input type="price" placeholder="￥0.00" v-model="oldprice">
				</view>
				<button class="btn" @click="show = false">确定</button>
			</u-popup>

			<u-popup :show="create_show" mode="bottom" @close="close">
				<text class="title">选择分类</text>
				<view :class="currnt==index?'active':'select'" v-for="(item,index) in create" :key="index"
					@click="select(item,index)">
					<text>{{item.name}}</text>
				</view>
				<button class="btn" @click="create_show = false">确定</button>
			</u-popup>
		</view>
		<button class="btn" @click="add">发布</button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				url1: 'http://waimai.com',
				text1: '请各位同学文明发布，否则看到会直接删除动态哦！营造美丽校园生活，就在校园圈圈外卖！',
				placeholder: '买家更关心品牌型号、入手渠道、转手原因、使用状况...',
				fileList3: [],
				content: '',
				show: false,
				create_show: false,
				create: [],
				currnt: 0,
				content_image: [],
				price: 0,
				oldprice: 0,
				name: '',
				category: {}
			}
		},
		onLoad() {
			//闲置商品分类
			uni.$u.http.post('api/idlegoods/get_idle_category', {
				campus_id: uni.getStorageSync('campus_id')
			}).then(res => {
				this.create = res.data.data
			}).catch(err => {
				console.log('400', err)
				uni.showToast({
					title: '闲置商品分类',
					icon: 'none'
				})
			})
		},
		methods: {
			close() {
				this.show = false
				this.create_show = false
			},
			add() {
				if(this.name==''){
					uni.showToast({
						title: '请输入物品名称',
						icon: "error"
					})
				}else if(this.content_image.length==0){
					uni.showToast({
						title: '请上传图片',
						icon: "error"
					})
				}else if(JSON.stringify(this.category) == '{}'){
					uni.showToast({
						title: '请选择分类',
						icon: "error"
					})
				}else if(this.price<=0){
					uni.showToast({
						title: '价格不能小于0',
						icon: "error"
					})
				}else{
					uni.$u.http.post('api/idlegoods/add_idlegoods', {
						category_id: this.category.id,
						name: this.name,
						price: this.price,
						user_id: uni.getStorageSync('user').user_id,
						user_image: uni.getStorageSync('image'),
						user_name: uni.getStorageSync('nickname'),
						content_image: this.content_image.join(","),
						content: this.content,
						oldprice: this.oldprice
					}).then(res => {
						if (res.data.code == 200) {
							uni.showToast({
								title: '发布成功',
								icon: "success",
								duration: 2000
							});
							setTimeout(function() {
								uni.redirectTo({
									url: './index'
								})
							}, 2000);
						} else {
							uni.showToast({
								title: res.data.msg,
								icon: "error"
							})
						}
					})
				}
			},
			select(i, e) {
				this.category = i
				this.category_id = i.id
				this.currnt = e
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
						url: 'http://waimai.com/api/idlegoods/upload_image', // 仅为示例，非真实的接口地址
						filePath: url,
						name: 'file',
						success: (res) => {
							this.content_image.push(JSON.parse(res.data).img)
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

	.add_idle {
		margin: 16rpx;
		padding: 25rpx;
		box-shadow: 0rpx 0rpx 36rpx 0rpx rgba(51, 51, 51, 0.11);
		border-radius: 14rpx;
		background: white;
	}

	.goods_name {
		background: #F3F3F3;
		margin-bottom: 10rpx;
		padding: 10rpx;
		font-size: 30rpx;
		border-radius: 8rpx;
	}

	.grid {
		width: 154rpx;
		height: 154rpx;
		line-height: 154rpx;

		display: flex;
		align-items: center;
		justify-content: center;
		background: #F3F3F3;
	}

	.price_i {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-bottom: 26rpx;

		text {
			font-size: 34rpx;
			color: #636363;
			margin-right: 24rpx;
		}

		input {
			width: 542rpx;
			height: 86rpx;
			background: #EAEAEA;
			border-radius: 14rpx;
			padding-left: 19rpx;
		}
	}

	.u-textarea {
		background: #F3F3F3 !important;
		margin-bottom: 20rpx;

		text {
			background: #F3F3F3 !important;
		}
	}

	.u-upload__wrap__preview:nth-child(4n),
	.u-upload__button {
		margin-right: 0 !important;
	}

	.title {
		font-size: 42rpx;
		text-align: center;
		color: #313131;
		margin: 36rpx;
	}

	.active {
		width: 600rpx;
		height: 86rpx;
		line-height: 86rpx;
		text-align: center;
		background: #FFCF42;
		font-size: 34rpx;
		border-radius: 44rpx;
		color: black;
		margin: 10rpx auto;
	}

	.select {
		width: 600rpx;
		height: 86rpx;
		line-height: 86rpx;
		text-align: center;
		background: #EAEAEA;
		font-size: 34rpx;
		border-radius: 44rpx;
		color: #C0C0C0;
		margin: 10rpx auto;
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

	.price {
		margin-top: 34rpx;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
</style>
