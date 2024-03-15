<script>
	export default {
		onLaunch: function() {

		},
		onShow: function() {
			uni.hideTabBar({
				animation: false
			})
			// console.log('App Show')
		},
		onHide: function() {
			// console.log('App Hide')
		}
	}
</script>

<style lang="scss">
	@import "@/uni_modules/uview-ui/index.scss";

	*,
	body,
	html,
	page {
		font-family: '黑体';
	}
</style>
