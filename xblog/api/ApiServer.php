<?php
/**
 * Class ApiServer
 * @description 主题对外提供的API服务
 * @author 小游
 * @date 2021/05/09
 * @package xblog\api
 */

namespace xblog\api;

use Exception;

require __DIR__ . "/../../themes/index.php";

class ApiServer
{
    // 请求地址
    const base = "/php/api/v1/";
    const GET = "GET";
    const PUT = "PUT";

    // 一些路径
    const THEME_DIR = __DIR__ . "/../../themes/";

    public function __construct($url)
    {
        // 允许跨域
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:*');

        // 处理不同的请求
        switch ($url){
            case self::GET.self::base."themes":
               $this->getAllThemes();
               break;
            case self::PUT.self::base."themes":
                $this->updateTheme();
                break;

        }
    }

    /**
     * 返回JSON形式数据
     * @param mixed $data 数据
     */
    private function returnJson($data){
        //这句是重点，它告诉接收数据的对象此页面输出的是json数据;
        header('Content-type:text/json');
        echo json_encode($data);
    }

    /**
     *  返回错误信息
     * @param string $message 错误信息
     * @param int $code 状态码
     */
    private function returnError(int $code, string $message){
        $data = [];
        $data["message"] = $message;
        header('Content-type:text/json');
        http_response_code($code);
        echo json_encode($data);
    }

    /**
     *  获取所有主题
     */
    private function getAllThemes()
    {
        $data = [];
        // 扫描所有文件
        $dirs = scandir(self::THEME_DIR,1);
//        var_dump($dirs);
        // 获取里面的配置信息
        foreach ($dirs as $dir){
            // 判断是否为文件
            $dirname = self::THEME_DIR.$dir;
            $filename = $dirname."/theme.json";
            if (is_dir($dirname) && is_file($filename)){
                // 读取文件
                $file = fopen($filename,"r");
                $content = fread($file,filesize($filename));
                fclose($file);
                // 解析为JSON数据
                $tmp = json_decode($content,true);
                $tmp["dir"] = $dir;
                $tmp["enable"] = $dir == THEME;
                // 添加到数组中
                array_push($data,$tmp);
            }
        }
        // 返回所有主题信息
        $this->returnJson($data);
    }


    /**
     *  更新主题
     */
    private function updateTheme(){
        // 获取切换的主题名字
        $name = $_GET["name"];
        $dirname = self::THEME_DIR.$name;
        try{
            // 判断主题是否存在
            if (is_dir($dirname)){
                // 修改主页index.php
                $config = self::THEME_DIR."index.php";
                if (is_file($config)){
                    // 读取文件
                    $file = fopen($config,"w+");
                    fwrite($file,'<?php const THEME = "'.$name.'";');
                    fclose($file);
                    $this->returnError(200,"修改成功");
                } else {
                    $this->returnError(500,"没有找到配置文件");
                }
            } else {
                $this->returnError(404,"没有找到页面");
            }
        }catch (Exception $e){
            $this->returnError(404,"系统错误");
        }
    }
}