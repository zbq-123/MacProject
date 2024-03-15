<template>
  <div class="search">
    <div class="search_1">
      <van-image @click="onback" width="20" height="20" src="https://hnqt.0898yzzx.com/static/rider/b_left.png" />
      <van-search v-model="value" show-action placeholder="请输入/订单编号/收货人姓名/收货人手机号" @search="onSearch">
        <template #action>
          <div @click="onSearch">搜索</div>
        </template>
      </van-search>
    </div>
    <div style="height: 54px;"></div>
    <!-- 内容部分 -->
    <p class="none" v-if="list.length == 0">暂无搜索内容</p>

    <slot v-for="(item,index) in list" v-key="index">
      <van-steps direction="vertical" :active="0" class="stepss">
        <p><img src="https://hnqt.0898yzzx.com/static/rider/dishi.png">还剩 <span>2分钟</span>
          送达<b>#{{item.today_number}}</b></p>
        <van-step>
          <h3>{{item.order.store_name}}</h3>
        </van-step>
        <van-step>
          <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
          <h4>{{item.order.delivery_name}}(先生){{item.order.delivery_phone}}</h4>
          <h4>订单号{{item.order_number}}</h4>
        </van-step>
        <van-step>
          <van-button size="large" class="btn">送达至客户</van-button>
        </van-step>
      </van-steps>
    </slot>
  </div>
</template>

<script>
  export default {
    name: 'search',
    data() {
      return {
        active: 0,
        value: '',
        list: []
      }
    },
    methods: {
      onback() {
        this.$router.go(-1);
      },
      onSearch() {
        this.$axios.post(process.env.API_HOST+'api/Rider/getRiderOderList', {
            rider_id: localStorage.getItem("id"),
            keyword: this.value
          })
          .then(res => {
            console.log(res)
            this.list = res.data.rider_order
          })
      }
    }
  }
</script>

<style scoped>
  h3,
  h4,
  p {
    margin: 0;
  }

  .search {
    background: #f4f6f7;
  }

  .search_1 {
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 99;
  }

  .van-search--show-action {
    width: 90%;
  }

  .stepss {
    margin: 10px;
  }

  .stepss p {
    display: flex;
    align-items: center;
    margin: 10px 0 0;
    position: relative;
  }

  .stepss p>img {
    position: absolute;
    left: -25px;
    width: 23px;
    height: 23px;
  }

  .stepss p>span {
    color: red;
    margin: 0 7px;
  }

  .stepss p>b {
    font-size: 20px;
    position: absolute;
    right: 10px;
  }

  .stepss h4 {
    color: #656668;
    font-weight: lighter;
    font-size: 10px;
    margin-top: 5px;
  }

  .stepss .btn {
    height: 35px;
    background: #07C160;
    font-size: 13px;
    color: white;
    border-radius: 5px;
  }

  .list .stepss>.van-steps {
    margin-bottom: 10px;
  }
  .none{
    font-size: 15px;
    text-align: center;
    line-height: 100vh;
  }
</style>
