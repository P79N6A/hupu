<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Test extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('index');
        // 设置参数
        
    }

    protected function execute(Input $input, Output $output)
    {
        $res = $this->test();
    	// 指令输出
        $id = 1;

        while (true){
            $data = Db::name('user_detail')->find($id);
            $id++;
            $output->writeln($data);
            sleep(1);
        }
    }

    public function test(){
        return 123;
    }
}
