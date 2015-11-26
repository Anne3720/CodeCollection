<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ini_set('max_execution_time', '0');
date_default_timezone_set("PRC");

class foodsalesvolume extends MY_Controller {
    public function __construct(){
        parent::__construct();
        ini_set ('memory_limit', '4048M');
        set_time_limit(0);
    }
    public function run($CityID,$CityName){
        $this->load->model("foodsales");
        //在统计菜品销售量之前，重置所有菜品销售量为0
        $this->foodsales->resetVolumes($CityName);
        $curMonth = date('Ym');
        $preMonth = date('Ym',strtotime('-30 days'));
        //从当月历史表里统计销售量
        $this->updateSalesVolume($curMonth,$CityID,$CityName);
        //如果跨月，累加统计前一个月的最近30天里的销售量
        if($preMonth != $curMonth){
            $this->updateSalesVolume($preMonth,$CityID,$CityName);
        }
    }
    
    public function task_run(){
        $this->load->model("foodsales");
        $cities = $this->foodsales->getCities();
        for($i=0;$i<count($cities);$i++){
            $shell="/usr/bin/php ".FCPATH."index.php foodsalesvolume run ".$cities[$i]['CityID']." ".$cities[$i]['Pinyin'].''." >".FCPATH."logs/crontablog/foodsalesvolume_run_".time().".log";
            `$shell &`;//启动任务进程
            echo $shell;
            $this->sleepShell();
        }
    }
    //脚本进程管理器
    public function sleepShell(){
        $shell = "ps w -C php|grep \"/usr/bin/php ".FCPATH."index.php foodsalesvolume run \"|wc -l";
        $msg = system($shell);
        if($msg>=10){
            sleep("1");
            $this->sleepShell();
        }else{
            return true;
        }
    }
}
