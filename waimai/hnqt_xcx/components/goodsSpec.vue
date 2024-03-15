<template>
	<transition name="fade">
		<view class="spec-shade" v-if="isShow">
			
		</view>
		<view class="spec-cont" v-if="isShow">
			<view style="position: absolute;right: 0;z-index: 9;">
				<u-icon name="close-circle" size="28" @click.self="hiddenModal"></u-icon>
			</view>
			<view class="goods-choose-show flex-star">
				<view class="goods-choose-show-right" v-if="currentItem">
					<image :src="url1+image" mode=""></image>
					<view class="goods-choose-show-bottom">
						<view>{{name}}</view>
						<p>￥{{currentItem.price || 0}}</p>
						<view>库存{{currentItem.stock || 0}}件</view>
					</view>
				</view>
				<i class="iconfont icon-cuowu" @click.stop='hiddenModal'></i>
			</view>
			<view class="spec-linebox">
				<view class="spec-item" v-for="(skuItem,skuIndex) in skuInfo" :key="skuIndex">
					<p>{{skuItem.name}}</p>
					<view class="item-cont flex-star">
						<span v-for="(item,index) in skuItem.items" :key="index"
							:class="[item.isShow ? '' : 'noProduct',subIndex[skuIndex] == index ? 'act' : '']"
							@click="item.isShow ? specificationBtn(item.name,skuIndex,index) : msg()">{{item.name}}</span>
					</view>
				</view>
			</view>
			<view style="display: flex;justify-content: space-between;align-items: center;">
				<view>{{specShowString}}</view>
				<u-number-box v-model="value">
					<view slot="minus" class="minus">
						<u-icon name="minus" color="#FFFFFF" size="12"></u-icon>
					</view>
					<text slot="input" class="input">{{value}}</text>
					<view slot="plus" class="plus">
						<u-icon name="plus" color="#FFFFFF" size="12"></u-icon>
					</view>
				</u-number-box>
			</view>
			<button class="btn" @click="add">加入购物车</button>
		</view>
	</transition>
</template>

<script>
	export default {
		data() {
			return {
				url1: 'http://waimai.com',
				skuInfo: [],
				selectArr: [], //当前已选规格数组
				specShowString: '选择规格', //已选规格字符串展示
				currentItem: {
					goods_id:0
				}, //规格选完后的对象
				subIndex: [] ,//规格选中样式
				value:1,//步进器
				name:'',
				image:''
			};
		},
		props: {
			goodsData: {
				type: Object,
				required: true
			},
			isShow: false,
		},
		created() {
			this.name = this.goodsData.name;
			this.image = this.goodsData.image;
			this.skuInfo = this.goodsData.SKUInfo;
			this.skuInfo.forEach(item => {
				item.items.forEach(el => {
					el.isShow = true;
				})
			})
		},
		methods: {
			hiddenModal() {
				this.$emit('hiddenModal', false)
			},
			/**
			 * @param {String} specName 当前点击规格按钮的值(黑色，35)
			 * @param {Number} specIndex 选择的规格下标(例子中颜色是0，尺码是1)
			 * @param {Number} specItemIndex 选择规格值下标(例子中35下标是0)
			 */
			specificationBtn(specName, specIndex, specItemIndex) {
				if (this.selectArr[specIndex] != specName) { //判断所选规格数组中是否包含当前点击规格
					this.selectArr[specIndex] = specName; //如果没有则把当前规格添加
					this.subIndex[specIndex] = specItemIndex; //添加选中样式
				} else {
					this.selectArr[specIndex] = '';
					this.subIndex[specIndex] = -1; //去除样式
				}
				this.specShowString = this.spaceRemoveArr(this.selectArr).join('-') || '选择规格'; //所选规格页面中展示，数组为空则变为选择规格
				this.inventoryLookup(); //当规格选完后，去匹配
				this.clickPitch(); //库存判断，实现不可点击
			},
			spaceRemoveArr(arr) { //数组去除空字符串
				let tempArr = []
				arr.forEach(item => {
					if (item) {
						tempArr.push(item)
					}
				})
				return tempArr;
			},
			inventoryLookup() {
				try {
					this.goodsData.priceInfo.forEach((item, index) => {
						if (item.difference == this.specShowString) {
							this.currentItem = item;
							throw new Error();
						} else {
							this.currentItem = {
								goods_id: 0,
								id: 0,
								name: '',
								image: '',
								price: 0,
								stock: 0
							};
						}
					})
				} catch (e) {}
			},
			clickPitch() {
				let result = [];
				for (let i in this.goodsData.SKUInfo) {
					result[i] = this.selectArr[i] ? this.selectArr[i] : '';
				}

				//最难理解的大概就是这里了,这里跟着循环里打印结果，多走几遍就大致明白了  假象.jpg
				for (let i in this.goodsData.SKUInfo) {
					let last = result[i];
					// console.log(result, '****************')
					for (let k in this.goodsData.SKUInfo[i].items) {
						result[i] = this.goodsData.SKUInfo[i].items[k].name;
						// console.log(result, last)
						this.skuInfo[i].items[k].isShow = this.isMay(result)
					}
					result[i] = last;
				}
			},
			isMay(result) {
				for (let i in result) {
					if (result[i] == '') {
						return true;
					}
				}
				for (let i in this.goodsData.priceInfo) {
					if (this.goodsData.priceInfo[i].difference == result.join("-") && this.goodsData.priceInfo[i].stock >
						0) {
						return true;
					}
				}
			},
			msg() {
				uni.showToast({
					title: '别点啦！已经卖完啦！',
					icon: "none",
					duration: 3000
				})
			},
			add() {
				if(this.currentItem.goods_id==0){
					uni.showToast({
						title: '请选择规格',
						icon: "none"
					})
				}else{
					this.currentItem['goods_num'] = this.value
					this.$emit("add", this.currentItem)
				}
				
				
			}
		},
		computed: {}
	}
</script>

<style lang="scss" scoped>
	.fade-enter-active,
	.fade-leave-active {
		transition: opacity .5s;
	}

	.fade-enter,
	.fade-leave-to

	/* .fade-leave-active below version 2.1.8 */
		{
		opacity: 0;
	}

	.spec-shade {
		position: fixed;
		left: 0;
		top: 0;
		bottom: 0;
		right: 0;
		background: rgba(0, 0, 0, .3);
		z-index: 1;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);

		
	}
	.spec-cont {
		background: white;
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		padding-bottom: 40rpx;
		z-index: 9999;
	
		.btn {
			width: 60%;
			background: #00d22a;
			border-radius: 100rpx;
			color: white;
			line-height: 35px;
			font-size: 30rpx;
		}
	
		>view {
			padding: 20rpx;
		}
	
		.goods-choose-show {
			// margin: -40rpx 0 10rpx 0;
	
			.goods-choose-show-right {
				position: relative;
				display: flex;
				align-items: center;
	
				image {
					width: 150rpx;
					height: 150rpx;
					box-shadow: 0 0 10px rgba(0, 0, 0, .2);
					border-radius: 10rpx;
					margin-right: 20rpx;
					border: 5rpx solid #fff;
				}
	
				.goods-choose-show-bottom {
					display: flex;
					flex-flow: column;
	
					p {
						color: #FE9E01;
						font-size: 32rpx;
						font-weight: bold;
					}
	
					view {
						color: #666;
						font-size: 26rpx;
						margin-top: 5rpx;
					}
				}
	
			}
	
			i.icon-cuowu {
				position: absolute;
				top: 30rpx;
				right: 30rpx;
			}
		}
	
		.spec-item {
			// padding: 15rpx 0;
			// border-bottom: 1px solid #eee;
	
			p {
				font-weight: bold;
				line-height: 50rpx;
			}
	
			.item-cont {
				padding: 15rpx 0;
	
				span {
					display: inline-block;
					padding: 10rpx 18rpx;
					border-radius: 8rpx;
					color: #666;
					font-size: 28rpx;
					background: #F6F4F5;
					margin-right: 15rpx;
				}
	
				span.act {
					background: #FDAB27;
					color: white;
				}
	
				span.noProduct {
					color: #ccc;
				}
			}
		}
	
	
	}
	.minus {
			width: 50rpx;
			height: 50rpx;
			background-color: #FF9A0A;
			border-radius: 50%;
			/* #ifndef APP-NVUE */
			display: flex;
			/* #endif */
			justify-content: center;
			align-items: center;
		}
	
		.input {
			padding: 0 10px;
		}
	
		.plus {
			width: 50rpx;
			height: 50rpx;
			background-color: #FF9A0A;
			border-radius: 50%;
			/* #ifndef APP-NVUE */
			display: flex;
			/* #endif */
			justify-content: center;
			align-items: center;
		}
</style>
