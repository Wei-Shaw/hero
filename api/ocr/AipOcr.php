<?php
/*
* Copyright (c) 2017 Baidu.com, Inc. All Rights Reserved
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may not
* use this file except in compliance with the License. You may obtain a copy of
* the License at
*
* Http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations under
* the License.
*/

require_once 'lib/AipBase.php';

/**
 * 文字OCR
 */
class AipOcr extends AipBase{

    /**
     * idcard api url
     * @var string
     */
    private $idcardUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard';
    
    /**
     * bankcard api url
     * @var string
     */
    private $bankcardUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/bankcard';
    
    /**
     * general api url
     * @var string
     */
    private $generalUrl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/general';

    /**
     * @param  string $image 图像读取
     * @param  bool $isFront 身份证是 true正面 false反面
     * @param  array $options 可选参数
     * @return array
     */
    public function idcard($image, $isFront, $options=array()){

        $data = array();
        $data['image'] = $image;
        $data['id_card_side'] = $isFront ? 'front' : 'back';
        $data['detect_direction'] = isset($options['detect_direction']) ? $options['detect_direction'] : 'false';
        $data['accuracy'] = isset($options['accuracy']) ? $options['accuracy'] : 'auto';

        return $this->request($this->idcardUrl, $data);
    }
    
    /**
     * @param  string $image 图像读取
     * @return array
     */
    public function bankcard($image){

        $data = array();
        $data['image'] = $image;

        return $this->request($this->bankcardUrl, $data);
    }

    /**
     * @param  string $image 图像读取
     * @param  array $options 可选参数
     * @return array
     */
    public function general($image, $options=array()){

        $data = array();
        $data['image'] = $image;
        $data['recognize_granularity'] = isset($options['recognize_granularity']) ? $options['recognize_granularity'] : 'big';
        $data['mask'] = base64_encode(isset($options['mask']) ? $options['mask'] : '');
        $data['language_type'] = isset($options['language_type']) ? $options['language_type'] : 'CHN_ENG';
        $data['detect_direction'] = isset($options['detect_direction']) ? $options['detect_direction'] : 'false';
        $data['detect_language'] = isset($options['detect_language']) ? $options['detect_language'] : 'false';
        $data['classify_dimension'] = isset($options['classify_dimension']) ? $options['classify_dimension'] : 'lottery';
        $data['vertexes_location'] = isset($options['vertexes_location']) ? $options['vertexes_location'] : 'false';   
        
        return $this->request($this->generalUrl, $data);
    }

    /**
     * 格式检查
     * @param  string $url
     * @param  array $data
     * @return array
     */
    protected function validate($url, &$data){
        $imageInfo = AipImageUtil::getImageInfo($data['image']);

        //图片格式检查
        if(!in_array($imageInfo['mime'], array('image/jpeg', 'image/png', 'image/bmp'))){
            return array(
                'error_code' => 'SDK109',
                'error_msg' => 'unsupported image format',
            );
        }

        //图片大小检查
        if($imageInfo['width'] < 15 || $imageInfo['width'] > 4096 || $imageInfo['height'] < 15 || $imageInfo['height'] > 4096){
            return array(
                'error_code' => 'SDK101',
                'error_msg' => 'image length error',
            );
        }

        $data['image'] = base64_encode($data['image']);

        //编码后小于4m
        if(strlen($data['image']) >= 4 * 1024 * 1024){
            return array(
                'error_code' => 'SDK100',
                'error_msg' => 'image size error',
            );
        }

        return true;
    }

}
