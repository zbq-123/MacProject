export default {
	data() {
		return {
			//设置默认的分享参数
			//如果页面不设置share，就触发这个默认的分享
			share: {
				title: '圈圈食堂外卖',
				path: '/pages/index',  //默认跳转首页
				imageUrl: '/static/logo.png',  //可设置默认分享图，不设置默认截取头部5:4
			}
		}
	},
	onShareAppMessage(res) { //发送给朋友
		return {
			title: this.share.title,
			path: this.share.path,
			imageUrl: this.share.imageUrl,
			success(res) {
				// console.log('success(res)==', res);
				uni.showToast({
					title: '分享成功'
				})
			},
			fail(res) {
				// console.log('fail(res)==', res);
				uni.showToast({
					title: '分享失败',
					icon: 'none'
				})
			}
		}
	},
	onShareTimeline(res) { //分享到朋友圈
		return {
			title: this.share.title,
			path: this.share.path,
			imageUrl: this.share.imageUrl,
			success(res) {
				// console.log('success(res)==', res);
				uni.showToast({
					title: '分享成功'
				})
			},
			fail(res) {
				// console.log('fail(res)==', res);
				uni.showToast({
					title: '分享失败',
					icon: 'none'
				})
			}
		}
	},
	// 右上角收藏
	onAddToFavorites(){},
}