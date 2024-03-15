<template>
</template>

<script>
	export default {
		created() {
			let user = uni.getStorageSync('user')
			if(!user){
				uni.navigateTo({
					url:'./login'
				})
			}else{
				uni.switchTab({
					url:'./index'
				})
			}
		}
	}
</script>

<style>
</style>