<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    /**
     * @var UploadedFile
     */
    public $files;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['files'], 'file', 'skipOnEmpty' => false, 'maxFiles' => 0, 'extensions' => 'png,gif,jpg'],
        ];
    }

    public function attributeLabels(){
        return [
            'imageFile'=>'image上传',
            'files'=>'file上传'
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) {
            $path = 'uploads/'.date("YmdH",time()).'/';
            $this->exists_dir($path);
            $this->imageFile->saveAs($path . $this->imageFile->baseName . '.' . $this->imageFile->extension);
            return true;
        } else {
            return false;
        }
    }
    
    public function uploads()
    {
        if ($this->validate()) { 
            $path = 'uploads/'.date("YmdH",time()).'/';
            $this->exists_dir($path);

            if(!empty($this->imageFile)) {
                $this->imageFile->saveAs($path . $this->imageFile->baseName . '.' . $this->imageFile->extension);
            }
            
            if(!empty($this->files)) {
                foreach ($this->files as $file) {
                    if(empty($file)) continue;
                    $file->saveAs($path . $file->baseName . '.' . $file->extension);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    public function exists_dir ($path) 
    {
        // echo $path;exit;
        if(!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return true;
    }
}