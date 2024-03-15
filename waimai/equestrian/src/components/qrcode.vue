<template>
  <div class="container">
    <!-- 扫描中心的盒子 -->
    <!-- {{url}} -->
    <qrcode-stream class="qrcode" @decode="onDecode" @init="onInit">
      <div class="center">
        <span class="border"></span>
        <span class="border"></span>
        <span class="border"></span>
        <span class="border"></span>

        <div class="animate"></div>
      </div>
    </qrcode-stream>
  </div>
</template>

<script>
  import {
    QrcodeStream
  } from 'vue-qrcode-reader'

  export default {
    components: {
      QrcodeStream
    },
    data() {
      return {
        // url: {}
      }
    },
    methods: {
      onDecode(result) {
        var n = result.indexOf("=")
        // this.url = result.substr(n+1);
        this.$axios.post(process.env.API_HOST+'api/Rider/addOrder', {
            rider_id: localStorage.getItem("id"),
            order_id: result.substr(n + 1)
          })
          .then(res => {
            // this.url = res
            if(res.data.rider_order_id){
              this.$router.push({
                path: '/index',
                query: {
                  active: 1,
                  rider_order_id: res.data.rider_order_id
                }
              })
            }else{
              this.$toast.loading({
                message: '二维码失效',
                forbidClick: true,
                loadingType: 'spinner',
              });
              this.$router.push({
                path: '/index',
                query: {
                  active: 0,
                }
              })
            }
          })
      },

      async onInit(promise) {
        try {
          await promise
        } catch (error) {
          if (error.name === 'NotAllowedError') {
            alert('ERROR: 您需要授予相机访问权限')
          } else if (error.name === 'NotFoundError') {
            alert('ERROR: 这个设备上没有摄像头')
          } else if (error.name === 'NotSupportedError') {
            alert('ERROR: 所需的安全上下文(HTTPS、本地主机)')
          } else if (error.name === 'NotReadableError') {
            alert('ERROR: 相机被占用')
          } else if (error.name === 'OverconstrainedError') {
            alert('ERROR: 安装摄像头不合适')
          } else if (error.name === 'StreamApiNotSupportedError') {
            alert('ERROR: 此浏览器不支持流API')
          }
        }
      }
    }
  }
</script>

<style scoped>
  .container {
    height: 100vh;
    overflow: hidden;
  }

  .qrcode {
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
  }

  .center {
    width: 250px;
    height: 250px;
    position: fixed;
    background-color: transparent;

    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .animate {
    width: 200px;
    background-color: #41b482;
    height: 2px;
    margin: 0 auto;
    animation: animate 3s infinite;
  }

  @keyframes animate {
    0% {
      margin-top: 50px;
      opacity: 1;
    }

    100% {
      margin-top: 200px;
      opacity: 0;
    }
  }

  .border {
    position: absolute;
  }

  .border:nth-child(1) {
    top: 0;
    left: 0;
    border-top: 2px solid #41b482;
    border-left: 2px solid #41b482;
    width: 10px;
    height: 10px;
  }

  .border:nth-child(2) {
    top: 0;
    right: 0;
    border-top: 2px solid #41b482;
    border-right: 2px solid #41b482;
    width: 10px;
    height: 10px;
  }

  .border:nth-child(3) {
    bottom: 0;
    left: 0;
    border-bottom: 2px solid #41b482;
    border-left: 2px solid #41b482;
    width: 10px;
    height: 10px;
  }

  .border:nth-child(4) {
    bottom: 0;
    right: 0;
    border-bottom: 2px solid #41b482;
    border-right: 2px solid #41b482;
    width: 10px;
    height: 10px;
  }
</style>
