<?php
namespace App\HttpController\Common;

use App\HttpController\Base;
use App\HttpController\WebApiBase;

class Upload extends WebApiBase
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 上传图片
     */
    public function upload()
    {
        $request = $this->request();
        $img_file = $request->getUploadedFile('file');
        if (!$img_file) {
            $this->writeJson(500, '请选择上传的文件');
            return;
        }
        if ($img_file->getSize() > 1024 * 1024 * 5) {
            $this->writeJson(500, '图片不能大于5M！');
            return;
        }

        $MediaType = explode("/", $img_file->getClientMediaType());
        $MediaType = $MediaType[1] ?? "";
        if (!in_array($MediaType, ['png', 'jpg', 'gif', 'jpeg', 'pem', 'ico','m4a','acc'])) {
            $this->writeJson(500, '文件类型不正确！');
            return;
        }
        $path =  '/Upload/Img/'.date('Y-m-d').'/'; // 返回给客户端的地址
        $dir =  EASYSWOOLE_ROOT.'/Public'. $path; // 真实地址
        //$fileName = uniqid().$img_file->getClientFilename(); // 客户文件可能有中文
        $fileName = uniqid().'.'.$MediaType;

        if(!is_dir($dir)) {
            mkdir($dir, 0777 , true);
        }

        $flag = $img_file->moveTo($dir.$fileName);

        $data = [
            'name' => $fileName,
            'src'  => trim($path.$fileName,'/'),
        ];
        // $config = new \EasySwoole\Oss\AliYun\Config([
        //     'accessKeyId'     => 'LTAI4FxTyGzn5VSLe84tVocU',
        //     'accessKeySecret' => '7tcAzuOisu7PIRsNXQXRmpXWhaOzcq',
        //     'endpoint'        => 'http://oss-cn-beijing.aliyuncs.com',
        // ]);
        // $client = new \EasySwoole\Oss\AliYun\OssClient($config);
        // $dataoss = $client->putObject('sxlive-jk-kj-com',$fileName,$img_file->getStream());
        // echo $dir.$fileName;
        // var_dump($data);
        if($flag) {
            return $this->writeJson(200, '上传成功', $data);
        } else {
            return $this->writeJson(500, '上传失败');
        }
    }
    
    // 上传ios证书
    public function iosCert()
    {
        $file = $this->request()->getUploadedFile('file');
        if (!$file) {
            $this->writeJson(401, '请选择上传的证书');
            return;
        }
        if ($file->getSize() > 1024 * 1024 * 5) {
            $this->writeJson(401, '证书不能大于5M！');
            return;
        }

        $media_type = explode("/", $file->getClientMediaType());
        $media_type = $media_type[1] ?? "";
        if (!in_array($media_type, ['pem'])) {
            $this->writeJson(401, '证书类型不正确！');
            return;
        }
        $path =  '/Upload/Ioscert/'.date('Y-m-d').'/'; // 返回给客户端的地址
        $dir =  EASYSWOOLE_ROOT.'/Public'. $path; // 真实地址
        $fileName = uniqid().'.'.$media_type;

        if(!is_dir($dir)) {
            mkdir($dir, 0777 , true);
        }
        try{
            $flag = $file->moveTo($dir.$fileName);
            $data = [
                'name' => $fileName,
                'src'  => trim($path.$fileName,'/'),
            ];
            return $this->writeJson(200, '上传成功', $data);
        }catch(\Exception $e) {
            //var_dump($e->getMessage());
            return ['code'=> 401, 'msg'=> '移动文件失败'];
        }
        
    }
}
