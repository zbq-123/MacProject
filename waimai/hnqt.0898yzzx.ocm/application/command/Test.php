<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Log;
class Test extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('测试任务');
    }// configure() end

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('测试任务 - 正在执行中！');
         Log::write(date('Y-m-d H:i:s',time()).'自动收货开始执行'.PHP_EOL);
    }// execute() end
}