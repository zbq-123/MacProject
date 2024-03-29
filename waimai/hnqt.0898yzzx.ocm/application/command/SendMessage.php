<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;

class SendMessage extends Command
{
    protected function configure(){
        $this->setName('SendMessage')->setDescription("定时发送任务 SendMessage");
    }

    //调用SendMessage 这个类时,会自动运行execute方法
    protected function execute(Input $input, Output $output){
        $output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/
        $now_time=date('H:i:s',time());
        if($now_time>'09:00:15'){
            $this->Expired();//发短信
        }
        /*** 这里写计划任务列表集 END ***/
        $output->writeln('Date Crontab job end...');
    }

    //获取过期的订单发送短信
    public function Expired()
    {

        $url = 'https://web01.cc138008.com/kaijiang/history/ygxy5.json?v=1665384716364';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:111.222.333.4', 'CLIENT-IP:111.222.333.4'));
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11");
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $output=json_decode($output,true);
        $data=$output['data']['list'][0];
        $number = explode(',',$data['draw_code']);
        $newnumber=$data['draw_code'];
        $date=str_replace('期','',$data['pc_issue'][0]);
        $start_time=$data['pc_issue'][1];
        if(abs($number[0]-$number[1])==5||abs($number[0]-$number[2])==5||abs($number[0]-$number[3])==5||abs($number[1]-$number[2])==5||abs($number[1]-$number[3])==5||abs($number[2]-$number[3])==5){
            $newdata['duishu']=1;
        }else{
            $newdata['duishu']=0;
        }
        if($number[0]==$number[1]||$number[0]==$number[2]||$number[0]==$number[3]||$number[1]==$number[2]||$number[1]==$number[3]||$number[2]==$number[3]){
            $newdata['schong']=1;
        }else{
            $newdata['schong']=0;
        }
        if(!empty($newdata['schong'])&&empty($newdata['duishu'])){
            $order_number=$date;
            $order_describe=$date.'-'.$newnumber;
            send_add_success('olytE6lYaexQlc5pxa-yvWumUWxE',
                $order_number,
                '测试',
                date('Y-m-d',time()).'-'.$start_time,
                $order_describe,
                1500);
            send_add_success('olytE6ioo1dbETl8M4xTtaR7ij9M',
                $order_number,
                '测试',
                date('Y-m-d',time()).'-'.$start_time,
                $order_describe,
                1000);

        }
    }
}
