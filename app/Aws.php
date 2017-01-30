<?php

namespace App;

use AWS as SDK;
use Aws\S3\Exception\S3Exception;

/**
*    
*/
class Aws
{
    
    function __construct()
    {
        $this->s3 = SDK::createClient('s3');
        $this->ses = SDK::createClient('ses');
    }

    public function upload_key ($email, $name, $key) {
        $result = $this->s3->putObject([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Key'    => $email . '.' . $name,
            'Body'   => $key
        ]);
        return $result;
    }

    public function delete_key ($email, $name = 'default') {
        $this->s3->deleteObject([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Key'    => $email . '.' . $name
        ]);
    }

    public function list_keys ($email) {
        $result = $this->s3->listObjects([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Prefix'    => $email
        ]);
        return $result['Contents'];
    }

    public function lookup_key ($email, $name = 'default') {
        try {
            $result = $this->s3->getObject(array(
                'Bucket' => getenv('AWS_S3_BUCKET'),
                'Key'    => $email.'.'.$name
            ));
        } catch (S3Exception $e) {
            echo "No Key Found";
            die();
        }

        return (string) $result['Body'];
    }

    public function lookup_keys ($email) {
        $keys = $this->list_keys($email);
        if ($keys === NULL) {
            echo "No Keys Found";
            die();
        }
        foreach ($keys as $key) {
            $data[] = str_replace($email.".", '', $key['Key']);
        }
        return $data;
    }

    public function test() {
        $path = storage_path('8192.pub');
        $file = file_get_contents($path);
        $result = $this->upload_key('a@c4.io', '8192', $file);
        return $result;
    }


}