<template>
  <div class="index">
    <div class="nav">
      <van-row type="flex" align="center">
        <van-col span="2">
          <van-image @click="onback" width="23" height="23" round
            src="https://hnqt.0898yzzx.com/static/rider/left.png" />
        </van-col>
        <van-col span="10" offset="8">个人中心</van-col>
      </van-row>
    </div>
    <!-- 外卖员信息 -->
    <div class="b1">
      <div class="b2">
        <img src="https://cdn.jsdelivr.net/npm/@vant/assets/cat.jpeg">
        <div class="b1_left">
          <p>{{username}}
            <van-image width="15" height="15" src="https://hnqt.0898yzzx.com/static/rider/right.png" />
          </p>
          <span>{{campus_name}}</span>
        </div>
      </div>
      <button type="button" @click="set">服务与设置</button>
    </div>
    <!-- 选项卡 -->
    <van-tabs v-model="active" @click="tabHandler" line-width="30" line-height="2" title-active-color="white"
      title-inactive-color="gray" background="#0d1a39" color="orange">

      <van-tab title="今日订单" class="today">
        <div class="moth">
          <h1><i>共计</i>{{list.count.zj}} <i>单</i></h1>
        </div>
        <p class="p">订单明细</p>
        <van-tabs v-model="on" @click="today" line-width="30" line-height="2" title-active-color="black"
          title-inactive-color="gray" color="orange" background="transparent">

          <van-tab :title="'已送达 '+list.count.ysd+'单'" class="steps">
            <slot v-for="(item,index) in todaylist" v-key="index">
              <van-steps direction="vertical" :active="0">
                <p>{{item.create_time}} 收餐 ~ {{item.update_time}} 转交<b>#{{item.today_number}}</b></p>
                <van-step>
                  <h3>{{item.order.store_name}}</h3>
                </van-step>
                <van-step>
                  <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                  <h4>{{item.order.delivery_name}}(先生){{item.order.delivery_phone}}</h4>
                  <h4>订单编号{{item.order_number}}</h4>
                </van-step>
              </van-steps>
            </slot>
          </van-tab>

          <van-tab :title="'未送达 '+list.count.wsd+'单'" class="steps">
            <slot v-for="(item,index) in todaylist" v-key="index">
              <van-steps direction="vertical" :active="0">
                <p>{{item.create_time}} 收餐 ~ {{item.update_time}} 转交<b>#{{item.today_number}}</b></p>
                <van-step>
                  <h3>{{item.order.store_name}}</h3>
                </van-step>
                <van-step>
                  <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                  <h4>{{item.order.delivery_name}}(先生){{item.order.delivery_phone}}</h4>
                  <h4>订单编号{{item.order_number}}</h4>
                </van-step>
              </van-steps>
            </slot>
          </van-tab>

        </van-tabs>

      </van-tab>

      <van-tab title="月订单" class="steps">
        <h1>{{month}}月订单</h1>
        <h2></h2>
        <div class="moth">
          <h1><i>共计</i>{{list.count.zj}} <i>单</i></h1>
        </div>
        <p class="p">订单明细</p>
        <van-tabs v-model="on1" @click="month1" line-width="30" line-height="2" title-active-color="black"
          title-inactive-color="gray" color="orange" background="transparent">

          <van-tab :title="'已送达 '+list.count.ysd+'单'" class="steps">
            <slot v-for="(item,index) in todaylist1" v-key="index">
              <van-steps direction="vertical" :active="0">
                <p>{{item.create_time}} 收餐 ~ {{item.update_time}} 转交<b>#{{item.today_number}}</b></p>
                <van-step>
                  <h3>{{item.order.store_name}}</h3>
                </van-step>
                <van-step>
                  <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                  <h4>{{item.order.delivery_name}}(先生){{item.order.delivery_phone}}</h4>
                  <h4>订单编号{{item.order_number}}</h4>
                </van-step>
              </van-steps>
            </slot>
          </van-tab>

          <van-tab :title="'未送达 '+list.count.wsd+'单'" class="steps">
            <slot v-for="(item,index) in todaylist1" v-key="index">
              <van-steps direction="vertical" :active="0">
                <p>{{item.create_time}} 收餐 ~ {{item.update_time}} 转交<b>#{{item.today_number}}</b></p>
                <van-step>
                  <h3>{{item.order.store_name}}</h3>
                </van-step>
                <van-step>
                  <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                  <h4>{{item.order.delivery_name}}(先生){{item.order.delivery_phone}}</h4>
                  <h4>订单编号{{item.order_number}}</h4>
                </van-step>
              </van-steps>
            </slot>
          </van-tab>

        </van-tabs>
        <p class="p">过往月订单</p>
        <ul class="agen">
          <li v-for="(item,index) in list.before" :key="index" @click='search'>
            <p><span>{{item.month}}月共计送达</span><span>{{item.zj}}单</span></p>
            <b>详情 &gt;</b>
          </li>
        </ul>
        <!-- <van-collapse v-model="activeNames" class="agen">
          <van-collapse-item v-for="(item,index) in list.before" :key="index" :title="item.month+'月共计送达'"
            :label="item.zj+'单'" :name="index" value="详情">
            暂无
          </van-collapse-item>
        </van-collapse> -->
      </van-tab>

    </van-tabs>
  </div>
</template>

<script>
  export default {
    name: 'Personal',
    data() {
      return {
        activeNames: ['0'],
        active: 0,
        on: 1,
        on1: 1,
        username: localStorage.getItem("username"),
        campus_name: localStorage.getItem("campus_name"),
        list: {},
        month: '',
        todaylist: [],
        todaylist1: [],
        i: 1
      }
    },
    created() {
      let date = new Date()
      this.month = date.getMonth() + 1
      this.$axios.post(process.env.API_HOST+'api/Rider/countRiderOrder', {
          rider_id: localStorage.getItem("id"),
          type: this.i,
        })
        .then(res => {
          this.list = res.data
        })
      this.today(0)
      this.month1(0)
    },
    methods: {
      tabHandler(i) {
        this.$axios.post(process.env.API_HOST+'api/Rider/countRiderOrder', {
            rider_id: localStorage.getItem("id"),
            type: i + 1,
          })
          .then(res => {
            this.list = res.data
          })
        this.today(i)
        this.month1(i)
      },
      // 今日订单送达情况
      today(i) {
        var now = new Date();
        var year = now.getFullYear(); //得到年份
        var month = now.getMonth()+1; //得到月份
        var day = now.getDate(); //得到天
        this.$axios.post(process.env.API_HOST+'api/Rider/getRiderOderList', {
            rider_id: localStorage.getItem("id"),
            status: i == 0 ? 2 : 3,
            starttime: year +'-'+ month +'-'+ day+' 00:00:00',
            endtime: year +'-'+ month +'-'+ day+' 23:59:59'
          })
          .then(res => {
            // console.log(res)
            this.todaylist = res.data.rider_order
          })
      },
      // 今月订单送达情况
      month1(i) {
        var now = new Date();
        var year = now.getFullYear(); //得到年份
        var month = now.getMonth()+1; //得到月份
        var day = now.getDate(); //得到天
        this.$axios.post(process.env.API_HOST+'api/Rider/getRiderOderList', {
            rider_id: localStorage.getItem("id"),
            status: i == 0 ? 2 : 3,
          })
          .then(res => {
            // console.log(res)
            this.todaylist1 = res.data.rider_order
          })
      },
      onback() {
        this.$router.go(-1);
      },
      set() {
        this.$notify({
          type: 'danger',
          message: '暂未开发'
        });
      },
      search(){
        this.$router.push('/search')
      }
    }
  }
</script>

<style>
  h3,
  h4,
  h2,
  h1,
  p {
    margin: 0;
  }

  .b1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #0d1a39;
    padding: 0 10px 20px 10px;
    border-bottom: 1.2px solid rgba(255, 255, 255, .5);
  }

  .b1 button {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, .1);
    border-radius: 5px;
    height: 35px;
    line-height: 35px;
    color: rgba(255, 255, 255, .8);
    padding: 0 10px;
    font-size: 15px;
  }

  .b1 .b2 {
    display: flex;
    align-items: center;
  }

  .b1_left {
    display: flex;
    flex-flow: column;
    margin-left: 10px;
  }

  .b1_left p {
    color: white;
    font-size: 20px;
    font-weight: 400;
  }

  .b1_left span {
    color: rgba(255, 255, 255, .7);
    font-size: 15px;
    margin-top: 5px;
  }

  .b2>img {
    border-radius: 50%;
    width: 50px;
    height: 50px;
  }

  body {
    background-color: #f4f6f7;
    /* height: 100vh; */
  }

  .van-col--2 {
    display: contents;
  }

  .today h2 {
    padding: 0 10px;
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    margin-top: 10px;
  }

  .van-steps--vertical {
    margin: 10px;
  }

  .steps .van-tab,
  .today .van-tab {
    flex: unset;
    padding: 0 10px;
  }

  .nav {
    background-color: #0d1a39;
    padding: 20px 10px;
    color: white;
    font-size: 15px;
  }

  .steps .steps p,
  .today .steps p {
    font-size: 10px;
    font-weight: 500;
    padding: 10px 0 0;
    position: relative;
    left: -20px;
  }

  .steps .van-tabs--line .van-tabs__wrap,
  .today .van-tabs--line .van-tabs__wrap {
    height: 20px;
  }

  .steps {
    border-radius: 5px;
  }

  .agen{
    margin: 10px;
    background-color: white;
  }
  .agen li{
    border-bottom: 1px solid #e2e2e2;
    display: flex;
    justify-content: space-between;
    padding: 10px;
    align-items: center;
  }
  .agen p{
    display: flex;
    flex-flow: column;
  }
  .agen p span:last-child{
    color: #959595;
    margin-top: 5px;
  }
  .agen b{
    font-weight: lighter;
    color: #959595;
  }
  .agen li:active{
    background-color: #07c160;
    color: white;
  }
  .steps .van-cell,
  .steps .van-collapse-item__content {
    padding: 10px;
  }

  .steps .moth .van-collapse-item,
  .today .moth .van-collapse-item {
    width: 30%;
    color: rgba(0, 0, 0, .5);
    margin-right: 20px;
  }

  .steps .moth .van-cell,
  .steps .moth .van-collapse-item__content,
  .today .moth .van-cell,
  .today .moth .van-collapse-item__content {
    padding: 0;
    font-size: 12px;
  }

  .moth .van-hairline--top-bottom {
    display: flex;
    margin: 5px 0 0;
  }

  .moth .van-collapse-item--border::after,
  .moth [class*=van-hairline]::after {
    border: 0;
  }

  .steps .p,
  .today .p {
    display: flex;
    align-items: center;
    margin: 10px 0;
    position: relative;
    padding: 0 10px;
  }

  .steps h1,
  .today>h1 {
    font-size: 14px;
    font-weight: 500;
    margin-top: 10px;
    display: flex;
    align-items: flex-end;
    padding: 0 10px;
  }

  .steps h1 span,
  .today h1 span {
    font-size: 11px;
    margin-left: 8px;
    color: rgba(0, 0, 0, .5);
  }

  .steps h2,
  .today>h2 {
    color: orange;
    font-size: 12px;
    margin-top: 0;
    margin-bottom: 10px;
    font-weight: lighter;
    padding: 0 10px;
  }

  .steps p>img {
    position: absolute;
    left: -25px;
    width: 23px;
    height: 23px;
  }

  .steps p>b {
    font-size: 20px;
    position: absolute;
    right: -11px;
    top: 5px;
  }

  .steps h4 {
    color: #242526;
    font-weight: lighter;
    font-size: 12px;
    margin-top: 5px;
  }

  .steps .moth,
  .today .moth {
    background: white;
    padding: 10px;
  }

  .steps .moth>p,
  .today .moth>p {
    font-size: 12px;
    color: rgba(0, 0, 0, .5);
    margin: 0 0 2px 0;
    padding: 0;
  }

  .steps .moth>h1,
  .today .moth>h1 {
    font-size: 25px;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: baseline;
    font-weight: 500;
  }

  .steps .moth h1>i,
  .today .moth h1>i {
    font-size: 12px;
    font-style: normal;
    margin-left: 5px;
  }
</style>
