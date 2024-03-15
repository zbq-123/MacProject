<template>
  <div class="index">
    <div class="nav">
      <van-row type="flex" align="center">
        <van-col span="2">
          <van-image @click="Personal" width="23" height="23" round
            src="https://cdn.jsdelivr.net/npm/@vant/assets/cat.jpeg" />
        </van-col>
        <van-col @click="Personal" class="title" span="5">{{username}}</van-col>
        <van-col span="5" offset="3">送餐列表</van-col>
      </van-row>
    </div>
    <!-- 选项卡 -->
    <van-tabs class="list" @click="curren" :border="false" v-model="active" shrink line-width="30" line-height="2"
      title-active-color="white" title-inactive-color="gray" background="#0d1a39" color="orange">

      <van-tab title="收餐" class="saoma">
        <van-button @click="qrcode" icon="https://hnqt.0898yzzx.com/static/rider/saoma.png" color="#f7d31c">扫码收餐</van-button><br>
        <van-button @click="search" icon="https://hnqt.0898yzzx.com/static/rider/search.png" color="#f7d31c">搜索收餐</van-button>
      </van-tab>

      <van-tab :title="'待送订单'+rider_order_count" class="stepss">
        <van-list v-model="loading" :finished="finished" finished-text="没有更多了" @load="onLoad">
          <slot v-for="(item,index) in list" v-key="index">
            <van-steps direction="vertical" :active="1">
              <p><img src="https://hnqt.0898yzzx.com/static/rider/dishi.png">{{item.create_time}}<b>#{{item.today_number}}</b></p>
              <van-step>
                <h3>{{item.order.store_name}}</h3>
              </van-step>
              <van-step>
                <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                <h4>{{item.order.delivery_name}}(先生/女士){{item.order.delivery_phone}}</h4>
                <h4>订单号{{item.order_number}}</h4>
              </van-step>
              <van-step class="big_btn">
                <van-button size="large" type="primary" class="btn" @click="btn(item)">已送达</van-button>
                <van-button size="large" type="danger" class="btn" @click="popup">未送达</van-button>
              </van-step>
            </van-steps>
            <div class="zhao" v-show="dialog"></div>
            <div class="dialog" v-show="dialog">
              <h2>留言</h2>
              <textarea v-model="msg"></textarea>
              <van-button size="large" type="default" @click="close">取消</van-button>
              <van-button size="large" type="primary" @click="btn_1(item)">确定</van-button>
            </div>
          </slot>
        </van-list>
      </van-tab>

      <van-tab title="已送达顾客" class="stepss">
        <van-list v-model="loading" :finished="finished" finished-text="没有更多了" @load="onLoad">
          <slot v-for="(item,index) in list" v-key="index">
            <van-steps direction="vertical" :active="1">
              <p><img src="https://hnqt.0898yzzx.com/static/rider/dishi.png">已送达<b>#{{item.today_number}}</b></p>
              <van-step>
                <h3>{{item.order.store_name}}</h3>
              </van-step>
              <van-step>
                <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                <h4>{{item.order.delivery_name}}(先生/女士){{item.order.delivery_phone}}</h4>
                <h4>订单号{{item.order_number}}</h4>
              </van-step>
            </van-steps>
          </slot>
        </van-list>
      </van-tab>

      <van-tab title="未送达顾客" class="stepss">
        <van-list v-model="loading" :finished="finished" finished-text="没有更多了" @load="onLoad">
          <slot v-for="(item,index) in list" v-key="index">
            <van-steps direction="vertical" :active="0">
              <p><img src="https://hnqt.0898yzzx.com/static/rider/dishi.png">{{item.create_time}}<b>#{{item.today_number}}</b></p>
              <van-step>
                <h3>{{item.order.store_name}}</h3>
              </van-step>
              <van-step>
                <h3>{{item.order.campus_name}}( {{item.order.delivery_address}} )</h3>
                <h4>{{item.order.delivery_name}}(先生/女士){{item.order.delivery_phone}}</h4>
                <h4>订单号{{item.order_number}}</h4>
              </van-step>
              <van-step>
                <b>{{item.remarks}}</b>
              </van-step>
            </van-steps>
          </slot>
        </van-list>
      </van-tab>
    </van-tabs>
  </div>
</template>

<script>
  import qrcode from '@/components/qrcode.vue';
  export default {
    name: 'Index',
    components: {
      'vue-qrcode': qrcode,
    },
    data() {
      return {
        active: Number.parseInt(this.$route.query.active) ? Number.parseInt(this.$route.query.active) : 0,
        status: 1,
        username: localStorage.getItem("username"),
        list: [],
        rider_order_count: 0,
        msg: '',
        dialog: false,
        loading: false,
        finished: false,
        page: 1,
      }
    },
    created() {
      localStorage.setItem('rider_order_id', this.$route.query.rider_order_id) //存储骑手订单ID
      this.curren()
    },
    methods: {
      curren(i) {
        if(this.list != []){
          this.list = []
        }
        localStorage.setItem('i', i)
        this.$axios.post(process.env.API_HOST+'api/Rider/getRiderOderList', {
            rider_id: localStorage.getItem("id"),
            status: localStorage.getItem("i"),
            page: this.page
          })
          .then(res => {
            let rows = res.data.rider_order
            this.loading = false;
            this.rider_order_count = res.data.unsent_num

            if (rows == null || rows.length === 0) {
              // 加载结束
              this.finished = true;
              return;
            }
            this.finished = false
            // 将新数据与老数据进行合并
            this.list = this.list.concat(rows);
            //如果列表数据条数>=总条数，不再触发滚动加载
            if (this.list.length >= this.rider_order_count) {
              this.finished = true;
            }
          })
      },
      onLoad() {
        this.page = this.page++
        this.curren(localStorage.getItem("i"))
      },
      // 骑手信息
      Personal() {
        this.$router.push('/personal')
      },
      // 扫描订单
      qrcode() {
        this.$router.push({
          path: '/qrcode'
        });
      },
      // 搜索
      search() {
        this.$router.push('/search')
      },
      // 确认送达
      btn(i) {
        this.$axios.post(process.env.API_HOST+'api/Rider/confirmRiderOder', {
            rider_order_id: i.id,
          })
          .then(res => {
            console.log(res)
            if(res.data.code == 200){
              this.active = 2
            }else{
              this.$toast.fail('暂无信息');
            }
          })
      },
      // 遮罩
      popup() {
        this.dialog = true
      },
      close() {
        this.dialog = false
      },
      // 未送达
      btn_1(i) {
        this.dialog = false
        this.$axios.post(process.env.API_HOST+'api/Rider/undeliveredRiderOder', {
            rider_order_id: i.id,
            remarks: this.msg
          })
          .then(res => {
            if(res.data.code == 200){
              this.active = 3
            }else{
              this.$toast.fail('暂无信息');
            }
          })
      }
    }
  }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
  h3,
  h4,
  p {
    margin: 0;
  }

  .dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 10px;
    z-index: 9;
    width: 80%;
    height: 50%;
    display: flex;
    justify-content: center;
    flex-flow: column;
  }

  .zhao {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background-color: rgba(0, 0, 0, .5);
    z-index: 8;
  }

  .dialog textarea {
    width: auto;
    flex: 1;
    padding: 10px;
    border-top-color: rgba(0, 0, 0, .1);
  }

  .dialog h2 {
    text-align: center;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
  }

  .dialog button {
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }

  .list .van-tab {
    flex: unset;
    padding: 0 10px;
  }

  .index {
    height: 100vh;
  }

  .van-tabs__content {
    background-color: #f4f6f7;
  }

  .nav {
    background-color: #0d1a39;
    padding: 20px 10px;
    color: white;
    font-size: 15px;
  }

  .nav .title {
    margin-left: 5px;
  }

  .saoma {
    position: fixed;
    top: 55%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
  }

  .saoma>button {
    width: 50%;
    height: 60px;
    margin-top: 30px;
    font-size: 18px;
    color: black !important;
    font-weight: bold;
    border-radius: 5px;
  }

  .van-col--2 {
    display: contents;
  }

  .stepss {
    /* padding: 10px; */
    border-radius: 5px;
  }

  .van-steps--vertical {
    margin: 10px;
  }

  .stepss p {
    display: flex;
    align-items: center;
    margin: 20px 0 15px;
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
    color: #242526;
    font-weight: lighter;
    font-size: 12px;
    margin-top: 5px;
  }

  .big_btn .van-step__title {
    display: flex;
    justify-content: space-between;
  }

  .stepss .btn {
    width: 49%;
    height: 35px;
    font-size: 13px;
    color: white;
    border-radius: 5px;
  }

  .list .stepss>.van-steps {
    margin-bottom: 10px;
  }
</style>
