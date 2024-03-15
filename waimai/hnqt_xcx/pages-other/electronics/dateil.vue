<template>
	<view>
		<view class="navbar">
			<u-navbar title=" " :autoBack="true" bgColor="transparent" leftIcon="arrow-left" :fixed="true"
				:placeholder="true" :titleStyle="{
				'fontsize':'10',
				color:'#000000'
			}">
			</u-navbar>
		</view>
		<u-notice-bar :text="notice" mode="closable" bgColor="#9C9C9C" color="#fff" speed="50"
			v-if="shanjia.status == 2"></u-notice-bar>
		<!-- 商家公告 -->
		<view class="store_bg" :style="[{top:storetop}]"></view>
		<view id="store" :style="[{top:storetop}]">
			<view style="display: flex;">
				<image :src="url1+shanjia.image" mode=""></image>
				<view style="display: flex;flex-flow: column;justify-content: space-around;margin-left: 14rpx;">
					<text class="store_name">{{shanjia.name}}</text>
					<!-- <text class="price">起送价￥{{shanjia.min_price}} / {{shanjia.delivery_name}}￥{{shanjia.delivery_price}}</text> -->
					<view class="price">
						<text>已售{{shanjia.sale}}</text>
					</view>
				</view>
			</view>
			<!-- <text class="notice">公告：{{shanjia.notice}}</text> -->
		</view>
		<!-- 选项卡 -->
		<view class="inv-h-w tabs" :style="[{top:tabstop}]">
			<view :class="['inv-h',Inv==0?'inv-h-se':'']" @click="Inv=0">点菜</view>
			<view :class="['inv-h',Inv==1?'inv-h-se':'']" @click="Inv=1">商家</view>
		</view>
		<!-- 个人 -->
		<view v-show="Inv == 0">
			<!-- 左侧商品分类 -->
			<scroll-view scroll-y="true" :scroll-with-animation="true" :scroll-into-view="clickToId" class="left"
				:style="[{height:windowHeight,top:windowtop}]">
				<!-- 后端两组数据不统一,要减1才可以 key-1-->
				<view v-for="(abc,index) in shop" :key="index" :id="'to'+index">
					<view :class="['vie8',{active:index===currentNum}]" @click="setId(index)">
						<view class="title">{{abc.name}}</view>
					</view>
				</view>
			</scroll-view>
			<!-- 右侧商品内容 -->
			<scroll-view class="right" :scroll-into-view="clickId" @scroll="scroll" :scroll-with-animation="true"
				:scroll-y="true" :style="[{height:windowHeight,top:windowtop}]">
				<!-- 要做v-for循环-->
				<view v-for="(xiangmu,index) in shop" :key="index">
					<view class="right_title" :id="'po'+index">
						{{xiangmu.name}}
					</view>
					<view class="right_content" v-for="(c,e) in shop_details" :key="e"
						v-if="xiangmu.category_id == c.category_id">
						<image :src="url1+c.image" style="width: 33%;height: 142rpx;"></image>
						<view style="margin-left: 15rpx;width: 62%;">

							<text style="font-size: 27rpx;font-weight: bold;">{{c.name}}</text>
							<view style="font-size: 24rpx;margin-top: 14px;color: #A8A9B4;">
								已售{{c.sale}} |
								库存{{c.stock>=999?'999+':c.stock}}
							</view>
							<view class="right_number" v-show="shanjia.status!=2">
								<text style="font-size: 16px;">￥{{c.price}}</text>
								<view style="display: flex;align-items: center;" v-if="!c.spec">
									<!-- 减号 -->
									<view v-show="c.goods_num>0">
										<text class="button" @click="numJIAN($event,c)">-</text>
									</view>
									<!-- 数量 -->
									<text style="color:#a1a1a1;margin: 0 20rpx;font-size: 16px;"
										v-show="c.goods_num>0">{{c.goods_num}}</text>
									<!-- 加号 -->
									<text class="button" @click="numJIA($event,c)">+</text>
								</view>
								<!-- 规格 -->
								<view class="specifications" v-else>
									<u-button @click="specifications(c,e)" color="#FF990C" size="mini" shape="circle"
										text="规格"></u-button>
								</view>
							</view>
						</view>
					</view>
				</view>
			</scroll-view>
		</view>
		<!-- 规格 -->
		<goodsSpec v-if="goodsDate" :isShow="isShow" :goodsData="goodsDate" @add='add' @hiddenModal='hiddenModal'>
		</goodsSpec>
		<!-- 商家 -->
		<view class="current_l" v-show="Inv == 1">
			<scroll-view class="vie6" scroll-y="true" :style="[{height:windowHeight,top:windowtop}]">
				<view class="store_xinxi">
					<view style="padding: 20rpx;">
						<view style="font-size: 33rpx;font-weight: bold;margin-bottom: 10rpx;">{{shanjia.name}}
						</view>
						<view style="font-size: 28rpx; color: #666666">{{shanjia.address}}</view>
						<view style="font-size: 33rpx;font-weight: bold;margin-top: 30rpx;margin-bottom: 20rpx;">
							商家信息
						</view>
						<!-- <view style="font-size: 28rpx;color: #666666">
							所在校区：{{shanjia.campus_name}}<br>
							营业时间：{{shanjia.start_time1}}-{{shanjia.end_time1}}{{shanjia.start_time2}}-{{shanjia.end_time2}}{{shanjia.start_time3}}-{{shanjia.end_time3}}
						</view> -->
						<!-- <view style="font-size: 16px;font-weight: bold;">
							<text
								style="font-size: 33rpx;font-weight: bold;margin-top: 30rpx;margin-bottom: 20rpx;display: inline-block;">当前状态：</text>
							<text style="font-size: 28rpx;color: #666666;">{{shanjia.status==1?'营业':'休息'}}</text>
						</view> -->
					</view>
				</view>
				<view
					style="width: 93%;background-color: #eeeeee; margin: 30rpx auto;border-radius: 18rpx;overflow: hidden;overflow-wrap: anywhere;">
					<!-- <view style="padding: 20rpx;">
						<view style="font-size: 33rpx;font-weight: bold;margin-top: 30rpx;margin-bottom: 20rpx;">商家描述
						</view>
						<view style="font-size: 28rpx; color: #666666" v-html="shanjia.detail"></view>
					</view> -->
				</view>
				<view @click="tel_store()" class="tel_store">
					<view style="color: #000;font-weight: bold;font-size: 30rpx;">联系商家</view>
				</view>
			</scroll-view>
		</view>

		<!-- 购物结算弹窗 -->
		<view class="vie23" v-show="hidden" @click="card_bg"></view>
		<view v-show="hidden" class="box">
			<view class="box_1">
				<view style="padding: 20rpx;font-weight: bold;font-size: 28rpx;">已选商品</view>
				<view style="padding: 30rpx;font-weight: bold;font-size: 28rpx;color: #BFBFBF;" @click="clearAll">
					<u-icon name="trash" label="清空"></u-icon>
				</view>
			</view>
			<!-- 循环购物结算弹窗里的每个商品 -->
			<view style="max-height: 500rpx;overflow-y: scroll;">
				<view class="box_2" v-for="(item,e) in gouwucheAdd" :key="e" v-if="item.goods_num">

					<image :src="url1+item.image" style="width: 100rpx;height: 100rpx;"></image>
					<view class="box_3">
						<view>{{item.name}}</view>
						<!-- 判断是否存在规格 -->
						<view style="color: #a1a1a1;font-size: 20rpx;font-weight: lighter;">
							<text v-if="item.difference">{{item.difference}}</text>
						</view>
						<view style="display: flex;width: 100%;align-items: center;justify-content: space-between;">
							<text style="color: red;font-size: 16px;">￥{{item.price}}</text>
							<view style="display: flex;align-items: center;">
								<text class="button" @click="numJIAN($event,item)">-</text>
								<view style="margin: 0 20rpx;" v-show="item.goods_num>0">{{item.goods_num}}</view>
								<text class="button" @click="numJIA($event,item)">+</text>
							</view>
						</view>
					</view>

				</view>
			</view>

			<view class="box_name_price">
				<view>合计：</view>
				<view class="box_price">￥{{totalPrice}}</view>
			</view>
			<!-- <view style="border-bottom: 1rpx solid #f5f5f5;width: 95%;margin: auto;"></view> -->
		</view>
		<!-- 底部总额度 -->
		<view class="bottom">
			<view class="vie5" :style="{display: `${sjStatic}`}">
				<view style="position: relative;top: 0;left: -24rpx;width: 23%;" id="cart">
					<image :src='gouwuImg' style="width: 240rpx;height: 240rpx;" @click="dianjigouwuImg"></image>
					<view class="vie9" v-show="gouwucheYuandian">{{numHe2}}</view>
				</view>
				<view style="display: flex;align-items: center;width: 77%;justify-content: flex-start;width: 80%">
					<view style="color: #FFFFFF; font-size: 38rpx;">
						{{totalPrice==0?'￥0':'￥'+totalPrice}}
					</view>
					<!-- <view style="color: #FFFFFF;font-size: 24rpx;">
						另需配送费￥{{shanjia.delivery_price}}
					</view> -->
				</view>
				<block>
					<view :class="totalPrice>0?'vie24':'vie12'" @click="totalPrice>0?submit():''">
						{{totalPrice>0?'去结算':'请您挑选心仪的物品'}}
					</view>
				</block>
				<!-- <block v-if="shanjia.status==2">
					<view class="vie12">休息中</view>
				</block> -->
			</view>
		</view>
		<darke-cartsBall ref="cartsBall"
		    :ballImage="'url('+require('@/static/cart.png')+')'" 
		    :duration="2000" 
		    :endPos="{
		        x: CartLocation.left+50, 
		        y: CartLocation.bottom-70
		    }"
		    :is3dSheet="false"
		    :zIndex="9999"
		    ballColor="transparent"
		>
		</darke-cartsBall>
	</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				value: 1,
				sjStatic: 'flex',
				CartLocation: {},
				backg: '#000000',
				gouwuImg: '../../static/16.svg',
				gouwucheYuandian: false,
				hidden: false,
				//渲染数据层
				url1: 'http://waimai.com',
				start_time1: '',
				start_time2: '',
				start_time3: '',
				end_time1: '',
				end_time2: '',
				end_time3: '',
				gouwucheAdd: [],
				shop: [],
				shop_details: [],
				shanjia: {},
				Inv: 0,
				windowHeight: '0px',
				clickId: '',
				clickToId: '',
				currentNum: 0,
				topList: [],
				isLeftClick: false,
				price: 0,
				windowtop: 0,
				storetop: 0,
				tabstop: 0,
				total: 0, //价格总数
				e: 0, //存储规格索引值
				// notice: '',
				list: [],
				goodsDate: null,
				isShow: false
			};
		},
		onReady() {
			setTimeout(() => {
				let _that = this;
				let q = uni.createSelectorQuery()
				q.select('#cart').boundingClientRect(data => {
					// console.log('购物车位置', data)
					// data.left=data.left-100
					_that.CartLocation = data
				}).exec();
				uni.getSystemInfo({
					success: function(res) {
						let store = uni.createSelectorQuery().select("#store");
						let bottom = uni.createSelectorQuery().select(".bottom");
						let tabs = uni.createSelectorQuery().select(".tabs");
						let navbar = uni.createSelectorQuery().select(".navbar");
						bottom.boundingClientRect(function(dataa) { //data - 各种参数
							tabs.boundingClientRect(function(datab) { //data - 各种参数
								store.boundingClientRect(function(
									datac) { //data - 各种参数
									navbar.boundingClientRect(function(
										datad) { //data - 各种参数
										_that.storetop = datad.height +
											'px';
										_that.tabstop = datad.height +
											datac.height + 'px';
										_that.windowtop = datac
											.height + datab.height +
											datad.height + 3 + 'px';
										_that.windowHeight = res
											.windowHeight - (
												dataa.height +
												datab.height +
												datac.height +
												datad.height + 15) +
											'px';
									}).exec()
								}).exec()
							}).exec()
						}).exec()
					}
				});
			}, 300)
		},
		onLoad(options) {
			uni.showLoading({
				title: '加载中'
			})
			// 商家信息
			uni.setStorageSync('store_id', options.store_id)
			uni.$u.http.post('api/digital/get_digital_info', {
				store_id: options.store_id
			}).then(res => {
				// console.log('商家信息', res.data.data)
				// this.notice = "本店已打烊！" + "早上营业:" + res.data.data.start_time1 + "-" + res.data.data.end_time1 +
				// 	"中午营业:" + res.data.data.start_time2 + "-" + res.data.data.end_time2 + "下午营业:" + res.data.data
				// 	.start_time3 + "-" + res.data.data.end_time3
				this.shanjia = res.data.data
			})
			// 商品分类
			uni.$u.http.post('api/digital/get_digitalgoods_list', {
				campus_id: uni.getStorageSync('campus_id'),
				store_id: parseFloat(options.store_id)
			}).then(res => {
				// console.log('商品分类', res.data)
				if (res.data.code != 200) {
					uni.showToast({
						title: res.data.msg,
						icon: "error",
						duration: 2000
					})
					setTimeout(function() {
						uni.switchTab({
							url: '/pages/index'
						})
					}, 2000)
				} else {
					this.shop = res.data.data
					let shop_details = [];
					for (let j = 0; j < Object.values(res.data.data).length; j++) {
						Object.values(Object.values(res.data.data))[j].goods.map((i, k) => {
							shop_details.push(Object.assign(i, {
								goods_num: 0,
								isselect: false
							}))
						})
					}
					this.$nextTick(() => {
						setTimeout(()=>{
							for (var i = 0;i<shop_details.length;i++) {
								uni.createSelectorQuery().select('#po'+i).boundingClientRect(data => {
									if(data!=null){
										this.topList.push(data.top-this.windowtop)
									}
								}).exec();
							}
						},1000)
					})
					if (options.again != undefined) {
						shop_details.forEach(el1 => {
							JSON.parse(options.again).forEach(el => {
								if (el1.goods_id == parseFloat(el.id)) {
									
									el1.goods_num = parseFloat(el.number); //数量
									el1.isselect = true; //选中
									this.gouwucheYuandian = true; // // 购物车的小圆点出现
									this.gouwuImg = '../../static/19.svg';
									this.total += el.price * parseFloat(el.number) //此处修改页面的价格
									el1.total += el.price * parseFloat(el.number) //此处修改页面的价格
									
									this.gouwucheAdd.push({
										goods_id: parseFloat(el.id),
										id: parseFloat(el.sku_id),
										image: el.image,
										name: el.name,
										price: el.price,
										goods_num: parseFloat(el.number),
										// delivery_price: this.shanjia.delivery_price,
										shanjia_name: this.shanjia.name,
										// box_price: this.shanjia.box_price,
										difference: el.sku
									})
								}
							})
						})
					}
					if (options.good_id != undefined) {
						let goods_id = new Array();
						goods_id = options.good_id.split(',')
						shop_details.forEach(el => {
							goods_id.forEach(el2 => {
								let data = el2.split(':')
								if (el.goods_id == data[0]) {
									el.goods_num = 1; //数量
									el.isselect = true; //选中
									this.total += el.price * el.goods_num //此处修改页面的价格
									el.total += el.price * el.goods_num //此处修改自定义商品的价格

									this.gouwucheYuandian = true; // // 购物车的小圆点出现
									this.gouwuImg = '../../static/19.svg';
									this.gouwucheAdd.push({
										goods_id: el.goods_id,
										id: data[1],
										image: el.image,
										name: el.name,
										price: el.price,
										goods_num: el.goods_num,
										// delivery_price: this.shanjia.delivery_price,
										shanjia_name: this.shanjia.name,
										// box_price: this.shanjia.box_price,
										difference: el.difference
									})
								}
							})
						})
					}
					this.shop_details = shop_details
					uni.hideLoading()
				}
			})
		},
		computed: {
			// 监听计算总价
			totalPrice() {
				if (this.shop_details.length !== 0) { //必须加这条判断，否则会直接拿data里的数组
					var sum = []; //此处声明变量，data无需声明，会报错
					var total = 0;
					this.gouwucheAdd.forEach(el => {
						sum.push(parseFloat(el.price) * parseFloat(el.goods_num))
					})
					sum.forEach(el => {
						total += el
					})
					total = Math.round(total.toFixed(2) * 100) / 100
					uni.setStorageSync('total', total)
					return total; //返回并让总数保留2位小数，最后一位为0不显示
				}
			},
			// 小圆点数量
			numHe2() {
				var sum = []; //此处声明变量，data无需声明，会报错
				var total = 0;
				this.gouwucheAdd.forEach(el => {
					sum.push(el.goods_num)
				})
				sum.forEach(el => {
					total += el
				})
				if (total <= 0) { //判断当前小圆点数量小于或等于0时关闭小圆点和购物车
					this.gouwucheYuandian = false; // 购物车的小圆点隐藏
					this.gouwuImg = '../../static/16.svg'; //换灰色购物车
					this.hidden = false // 隐藏购物车
				}
				return total
			},
		},
		// 销毁清除价格总数
		destroyed() {
			uni.removeStorageSync('total')
		},
		methods: {
			// 规格
			specifications(c, e) {
				this.e = e
				this.isShow = true
				let SKUInfo = []
				let priceInfo = []
				let guige = Object.values(c.spec)
				guige.forEach((el, index) => {
					let arr = []
					let obj = {}
					for (let i = 0; i < el.name.length; i++) {
						let arrObj = {}
						arrObj['name'] = el.name[i].item
						arrObj['sort'] = i
						arrObj['value'] = el.name[i].id
						arr.push(arrObj)
					}
					obj['name'] = el.spec_name
					obj['items'] = arr
					obj['sort'] = index
					SKUInfo.push(obj)
				})
				uni.$u.http.post('api/digital/getsku', {
					goods_id: c.goods_id
				}).then(res => {
					for (let i = 0; i < res.data.data.length; i++) {
						const specname = res.data.data[i].specname.filter(function(s) {
							return s && s.trim(); //去除数组中空字符串
						})
						let obj_1 = {}
						obj_1['goods_id'] = res.data.data[i].goods_id
						obj_1['id'] = res.data.data[i].id
						obj_1['difference'] = specname.join("-") //用"-"隔开
						obj_1['price'] = parseFloat(res.data.data[i].price)
						obj_1['stock'] = res.data.data[i].store_count
						obj_1['name'] = c.name
						obj_1['image'] = c.image
						priceInfo.push(obj_1)
					}
					let goodsDate = {}
					goodsDate['SKUInfo'] = SKUInfo
					goodsDate['priceInfo'] = priceInfo
					goodsDate['name'] = c.name
					goodsDate['image'] = c.image
					this.goodsDate = goodsDate
					// console.log(this.goodsDate.priceInfo)
				})
			},
			// 关闭
			hiddenModal() {
				this.isShow = false
				this.goodsDate = null
			},
			// 把所选规格参数加入购物车
			add(val) {
				this.shop_details[this.e].goods_num = this.shop_details[this.e].goods_num + 1; // 数量
				this.gouwucheYuandian = true; // // 购物车的小圆点出现
				this.gouwuImg = '../../static/19.svg'; //换绿色购物车
				this.isShow = false
				this.goodsDate = null
				for (var h = 0; h < this.gouwucheAdd.length; h++) {
					if (this.gouwucheAdd[h].goods_id === val.goods_id && this.gouwucheAdd[h].id === val.id) {
						this.gouwucheAdd[h].goods_num += val.goods_num
						return
					}
				}
				//往购物车里添加商品
				this.gouwucheAdd.push({
					goods_id: val.goods_id,
					id: val.id,
					image: val.image,
					name: val.name,
					price: val.price,
					goods_num: val.goods_num,
					// delivery_price: this.shanjia.delivery_price,
					shanjia_name: this.shanjia.name,
					// box_price: this.shanjia.box_price,
					difference: val.difference
				})
			},
			setId(index) {
				this.clickId = "po" + index;
				this.isLeftClick = true;
				this.currentNum = index;

			},
			scroll(e) {
				if (this.isLeftClick) {
					this.isLeftClick = false;
					return;
				}
				let scrollTop = e.target.scrollTop;
				for (let i = 0; i < this.topList.length; i++) {
					let h1 = this.topList[i];
					let h2 = this.topList[i + 1];
					if (scrollTop >= h1) {
						this.currentNum = i;
						this.clickToId = 'to' + i
					}
				}
			},
			// 致电商家
			tel_store() {
				uni.makePhoneCall({
					phoneNumber: this.shanjia.phone, //电话号码
					success: function(e) {
						console.log(e);
					},
					fail: function(e) {
						console.log(e);
					}
				})
			},
			// 选项卡
			changeTab(Inv) {
				that.navIdx = Inv;
			},
			// 添加
			numJIA(e,c) {
				if (c.stock <= c.goods_num) {
					uni.showLoading({
						title: '库存不足'
					})
					setTimeout(function() {
						uni.hideLoading();
					}, 2000);
					return false
				}
				this.shop_details.forEach(el => {
					if (el.goods_id === c.goods_id) {
						el.goods_num = c.goods_num = c.goods_num + 1
						this.gouwucheYuandian = true; // // 购物车的小圆点出现
						this.gouwuImg = '../../static/19.svg'; //换绿色购物车
						el.isselect = true
						this.$refs.cartsBall.drop({
						    x: e.detail.x, y:e.detail.y
						})
					}
				})
				//往购物车里添加商品
				for (var h = 0; h < this.gouwucheAdd.length; h++) {
					if (this.gouwucheAdd[h].goods_id === c.goods_id && this.gouwucheAdd[h].difference === c.difference) {
						this.gouwucheAdd.splice(h, 1, {
							goods_id: c.goods_id,
							id: this.gouwucheAdd[h].id,
							image: c.image,
							name: c.name,
							price: c.price,
							goods_num: c.goods_num,
							// delivery_price: this.shanjia.delivery_price,
							shanjia_name: this.shanjia.name,
							// box_price: this.shanjia.box_price,
							stock: c.stock,
							difference: c.difference
						})
						return
					}
				}
				this.gouwucheAdd.push({
					goods_id: c.goods_id,
					image: c.image,
					name: c.name,
					price: c.price,
					goods_num: c.goods_num,
					// delivery_price: this.shanjia.delivery_price,
					shanjia_name: this.shanjia.name,
					// box_price: this.shanjia.box_price,
					stock: c.stock,
					difference: c.difference
				})
			},
			// 删除
			numJIAN(e,c) {
				//指定按钮的数字大于0，每减一次递减
				this.shop_details.forEach(el => {
					if (el.goods_id === c.goods_id) {
						el.goods_num = c.goods_num = c.goods_num - 1
						if (el.goods_num <= 0) {
							el.isselect = false
						}
					}
				})
				//往购物车里递减商品
				for (var h = 0; h < this.gouwucheAdd.length; h++) {
					if (this.gouwucheAdd[h].goods_id === c.goods_id && this.gouwucheAdd[h].difference === c.difference) {
						this.gouwucheAdd.splice(h, 1, {
							goods_id: c.goods_id,
							id: this.gouwucheAdd[h].id,
							image: c.image,
							name: c.name,
							price: c.price,
							goods_num: c.goods_num,
							// delivery_price: this.shanjia.delivery_price,
							shanjia_name: this.shanjia.name,
							// box_price: this.shanjia.box_price,
							stock: c.stock,
							difference: c.difference
						})
						return
					}
				}
			},
			//购物车背景
			card_bg() {
				if (this.numHe2) {
					this.hidden = false
				}
			},
			//购物车弹出
			dianjigouwuImg() {
				if (this.numHe2 > 0) {
					this.hidden = true
				} else {
					this.hidden = false
				}
			},
			// 购物车清空
			clearAll() {
				for (let i = 0; i < this.shop_details.length; i++) { //循环拿索引
					this.shop_details[i].goods_num = 0
				}
				this.gouwucheYuandian = false; // 购物车的小圆点隐藏
				this.gouwuImg = '../../static/16.svg'; //换灰色购物车
				this.hidden = false // 隐藏购物车
				this.gouwucheAdd = []
			},
			// 结算
			submit() {
				const value = [];
				this.shop_details.forEach(item => {
					if (item.isrequired == 1 && item.isselect == false) {
						value.push(item.name)
						uni.showToast({
							title: '必选' + value,
							icon: "none"
						})
					}
				})
				if (value == '') {
					uni.navigateTo({
						url: './tijiaodingdan?data=' + JSON.stringify(this.gouwucheAdd),
						success: res => {
							console.log("跳转成功");
						},
						fail: () => {
							console.log("跳转失败");
						},
					});
				}
			}
		}
	}
</script>
<style lang="scss" vars="{ backg }">
	.tel_store {
		width: 492rpx;
		height: 70rpx;
		background: linear-gradient(to right, #fedb74, #ffbf80);
		margin: 30rpx auto;
		border-radius: 34rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.store_bg {
		width: 100%;
		height: 48rpx;
		background: linear-gradient(to right, #fedb74, #ffbf80);
		position: fixed;
	}

	.u-notice-bar {
		position: relative;
		z-index: 99;
	}

	.u-navbar--fixed {
		background: linear-gradient(to right, #fedb74, #ffbf80);
	}

	.specifications {
		width: 100rpx;
		color: black;
	}

	.inv-h-w {
		background-color: transparent;
		position: fixed;
		height: 68rpx;
		display: flex;
		margin: 0 24rpx;
	}

	.inv-h {
		font-size: 34rpx;
		text-align: center;
		height: 52rpx;
		line-height: 68rpx;
		margin-right: 60rpx;
	}

	.inv-h-se {
		font-weight: bold;
		border-bottom: 6rpx solid #FF9A0A;
	}

	page {
		background-color: #F3F3F3;
	}

	.left {
		width: 23%;
		background-color: #F5F5F5;
		text-align: center;
		position: fixed;
		left: 0;
	}

	.right {
		width: 77%;
		background-color: #FFFFFF;
		// margin-left: 174rpx;
		// margin-top: 265rpx;
		position: fixed;
		right: 0;
	}

	.right_title {
		font-size: 28rpx;
		height: 70rpx;
		line-height: 70rpx;
		margin-left: 10rpx;
		font-weight: bold;
	}

	.right_content {
		display: flex;
		align-items: center;
		padding: 10rpx;
	}

	.right_number {
		font-weight: bold;
		font-size: 35rpx;
		color: #DB0000;
		display: flex;
		align-items: flex-end;
		justify-content: space-between;
	}
	.button {
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
		color: white;
		background: #FF9A0A;
		font-size: 50rpx;
		font-weight: lighter;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.box {
		width: 100%;
		// height: 60%;
		background-color: #FFFFFF;
		position: fixed;
		bottom: 100rpx;
		border-radius: 25rpx 25rpx 0 0;
		overflow-y: scroll;
		z-index: 9;
	}

	.box_1 {
		width: 100%;
		height: 90rpx;
		line-height: 90rpx;
		display: flex;
		justify-content: space-between;
		// position: fixed;
		background: white;
		top: 31.7%;
		z-index: 99;
		border-radius: 24rpx;
		align-items: center;
	}

	.box_2 {
		display: flex;
		padding-left: 30rpx;
		position: relative;
		padding: 0 17rpx 17rpx 17rpx;
		border-bottom: 1rpx solid #f5f5f5;
		margin-bottom: 17rpx;
	}

	.box_3 {
		display: flex;
		flex-direction: column;
		font-size: 25rpx;
		font-weight: bold;
		margin-left: 15rpx;
		height: 100rpx;
		justify-content: space-between;
		width: 83%;
	}

	#store {
		background-color: #FFFFFF;
		position: fixed;
		// top: 0;
		z-index: 1;
		width: 100%;
		border-bottom: 2rpx solid #F5F5F5;
		width: 684rpx;
		padding: 14rpx;
		left: 50%;
		transform: translate(-50%);
	}

	.box_name_price {
		display: flex;
		font-size: 28rpx;
		justify-content: space-between;
		padding: 0 17rpx 17rpx 17rpx;
		background: white;
		height: 90rpx;
		width: 95%;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
	}

	.box_price {
		color: red;
		font-weight: bold;
	}

	#store image {
		width: 138rpx;
		height: 138rpx;
	}

	#store .notice {
		font-size: 20rpx;
		font-weight: 400;
		color: #BDBDBD;
	}

	#store .price {
		font-size: 20rpx;
		font-weight: 400;
		color: #0F0F0F;
		display: flex;
		align-items: center;

		image {
			width: 30rpx;
		}
	}

	.store_name {
		font-size: 36rpx;
		font-weight: bold;
		color: #000000;
	}

	.current_l {
		margin-top: 265rpx;
		background-color: #F5F5F5;
	}

	.store_xinxi {
		width: 93%;
		background-color: #eeeeee;
		margin: 30rpx auto;
		border-radius: 18rpx;
	}

	.active {
		background: #ffffff;
		font-weight: bold;
	}

	.vie6 {
		width: 100%;
		position: fixed;
		overflow-y: scroll;
		background: #ffffff;
	}

	.vie8 {
		font-size: 28rpx;
		height: 70rpx;
		// line-height: 90rpx;
		// font-weight: bold;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.vie9 {
		width: 35rpx;
		height: 35rpx;
		background-color: red;
		border-radius: 50%;
		z-index: 2;
		margin-top: -22rpx;
		margin-right: 12rpx;
		color: #FFFFFF;
		text-align: center;
		font-size: 28rpx;
		line-height: 35rpx;
		position: absolute;
		top: 70rpx;
		right: -35rpx;
	}

	.vie12 {
		position: fixed;
		width: 30%;
		height: 100rpx;
		text-align: center;
		line-height: 100rpx;
		color: #FFFFFF;
		margin-right: 12rpx;
	}

	.vie23 {
		width: 100%;
		height: 100vh;
		background-color: rgba(0, 0, 0, 0.2);
		position: fixed;
		top: 0;
		z-index: 5;
	}

	.vie24 {
		width: 142rpx;
		text-align: center;
		font-weight: bold;
		font-size: 26rpx;
		color: #000;
		background: linear-gradient(to right, #FFD43F, #FFA453);
		border-radius: 50rpx;
		margin-right: 12rpx;
		padding: 24rpx 30rpx;
	}

	.title {
		overflow: hidden;
		-webkit-line-clamp: 2;
		display: -webkit-box;
		-webkit-box-orient: vertical;
	}

	.t_title {
		display: flex;
		margin-bottom: 40rpx;
	}

	.t_title image {
		width: 150rpx;
		height: 150rpx;
		margin-right: 20rpx;
	}

	.bottom {
		background: white;
		position: fixed;
		bottom: 0;
		z-index: 999;
		width: 100%;
		height: 100rpx;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);

		.vie5 {
			width: 702rpx;
			height: 100rpx;
			border-radius: 50rpx;
			background-color: rgb(051, 051, 051);
			position: fixed;
			align-items: center;
			left: 50%;
			transform: translate(-50%);
			z-index: 999;
			bottom: 12rpx;
			border-top: none;
			justify-content: flex-end;
			margin-bottom: constant(safe-area-inset-bottom);
			margin-bottom: env(safe-area-inset-bottom);
		}
	}

	.minus {
		width: 44rpx;
		height: 44rpx;
		border-width: 2rpx;
		border: 2rpx solid gainsboro;
		border-top-left-radius: 200rpx;
		border-top-right-radius: 200rpx;
		border-bottom-left-radius: 200rpx;
		border-bottom-right-radius: 200rpx;
		@include flex;
		justify-content: center;
		align-items: center;
	}

	.plus {
		width: 44rpx;
		height: 44rpx;
		background-color: #00d22a;
		border-radius: 50%;
		/* #ifndef APP-NVUE */
		display: flex;
		/* #endif */
		justify-content: center;
		align-items: center;
	}

	.label {
		padding: 10rpx;
		margin-right: 20rpx;
		background: #f5f5f5;
		border-radius: 4rpx;
		font-size: 25rpx;
	}

	.label.active {
		background: #2f8eed;
		color: #FFFFFF;
	}
</style>
