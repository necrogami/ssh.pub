<?php

namespace App;

use AWS as SDK;
use Aws\S3\Exception\S3Exception;

/**
 * Class Aws
 * @package App
 */
class Aws
{
    /**
     * Aws constructor.
     */
    function __construct()
    {
        $this->s3 = SDK::createClient('s3');
    }

    /**
     * @param $email
     * @param $name
     * @param $key
     *
     * @return mixed
     */
    public function upload_key ($email, $name, $key) {
        $result = $this->s3->putObject([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Key'    => $email . '.' . $name,
            'Body'   => $key
        ]);
        return $result;
    }

    /**
     * @param        $email
     * @param string $name
     */
    public function delete_key ($email, $name = 'default') {
        $this->s3->deleteObject([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Key'    => $email . '.' . $name
        ]);
    }

    /**
     * @param $email
     *
     * @return mixed
     */
    public function list_keys ($email) {
        $result = $this->s3->listObjects([
            'Bucket' => getenv('AWS_S3_BUCKET'),
            'Prefix'    => $email
        ]);
        return $result['Contents'];
    }

    /**
     * @param        $email
     * @param string $name
     *
     * @return string
     */
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

        return ['key' => $result['Body'], 'keyname' => $name];
    }

    /**
     * @param $email
     *
     * @return array
     */
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
}