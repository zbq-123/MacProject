<template>
  <div class="center">
    <!-- 背景图片 -->
    <div class="background">
      <img class="img" src="https://hnqt.0898yzzx.com/static/rider/a.png">
      <img class="img_1" src="https://hnqt.0898yzzx.com/static/rider/a.png">
      <img src="https://hnqt.0898yzzx.com/static/rider/bg.png" width="100%" height="100%" alt="" />
    </div>
    <!-- 前景 -->
    <div class="front">
      <div class="card">
        <!-- 标题 -->
        <div slot="header" class="title">
          <span>
            骑手系统
          </span>
        </div>
        <!-- 表单 -->
        <div>
          <van-form @submit="onSubmit">
            <van-field v-model="username" name="用户名" label="用户名" placeholder="请输入用户名"
              :rules="[{ required: true, message: '' }]" />
            <van-field v-model="password" type="password" name="密码" label="密码" placeholder="请输入密码"
              :rules="[{ required: true, message: '' }]" />
            <div style="margin: 16px;">
              <van-button round block type="info" native-type="submit">提交</van-button>
            </div>
          </van-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Index',
    data() {
      return {
        username: '',
        password: ''
      }
    },
    methods: {
      onSubmit() {
        this.$axios.post(process.env.API_HOST+'api/Rider/login', {
            user_name: this.username,
            password: this.password
          })
          .then(res => {
            if (res.data.code == 200) {
              localStorage.setItem("username",res.data.rider_info.user_name);//存储
              localStorage.setItem("campus_name",res.data.rider_info.campus_name);//存储
              localStorage.setItem("id",res.data.rider_info.id);//存储
              this.$router.push('/index')
              setTimeout(() => {
                this.$notify({
                  type: 'success',
                  message: '欢迎来到骑手系统'
                });
              }, 100);
            } else {
              this.$notify({
                type: 'danger',
                message: res.data.msg
              });
            }
          })
          .catch(err => {
            console.error(err);
          })
      },
      getAutoCodeImg() {

      }
    }
  }
</script>

<style scoped>
  .background {
    height: 100vh;
    overflow: hidden;
    position: relative;
  }

  .background .img {
    width: 260px;
    animation: myMove 5s infinite;
    position: absolute;
    bottom: 100px;
  }

  .background .img_1 {
    width: 260px;
    animation: myMove1 15s infinite;
    position: absolute;
    bottom: 100px;
  }

  @keyframes myMove {
    0% {
      left: -260px;
      opacity: 1;
    }

    70% {
      left: 800px;
      opacity: 0;
    }

    100% {
      left: -260px;
      opacity: 0;
    }
  }

  @keyframes myMove1 {
    0% {
      left: -260px;
      opacity: 1;
    }

    99% {
      left: 800px;
      opacity: 0;
    }

    100% {
      left: -260px;
      opacity: 0;
    }
  }

  .title {
    text-align: center;
    font-size: 30px;
    margin: 15px;
  }

  .card {
    width: 320px;
    /* 动态剧中 */
    background-color: rgba(200, 200, 200, 0.5);
    /* 半透明 */
    border-color: rgba(200, 200, 200, 0.5);
    /* 半透明 */
    border-radius: 10px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .van-cell {
    background-color: rgba(200, 200, 200, 0) !important;
    /* 改变了组件的css为半透明 */
    color: white;
  }
</style>
