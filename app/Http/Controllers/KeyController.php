<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Mail\Confirm;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Carbon\Carbon;

class KeyController extends Controller
{
    /**
     * @param        $email
     * @param string $keyname
     *
     * @return string
     */
    public function getIndex ($email, $keyname = 'default') {
        $aws = new \App\Aws;
        return (string) $aws->lookup_key($email, $keyname)['key'];
    }

    /**
     * @param Request $request
     * @param         $email
     * @param string  $keyname
     *
     * @return string
     */
    public function postIndex (Request $request, $email, $keyname = 'default') {
        $token = Uuid::uuid4()->toString();
        $data['action'] = 'upload_key';
        $data['email'] = $email;
        $data['keyname'] = $keyname;
        $data['expires'] = Carbon::now()->addHour();
        $fileData = file_get_contents($request->file('key')->path());
        $fileData = trim($fileData);
        if (!in_array(substr($fileData, 0, 7), ['ssh-rsa', 'ssh-dss'], true)) {
            return $fileData;
        }
        $data['key'] = $fileData;
        $json_data = json_encode($data);
        Storage::put('keys/'.$token.'.json', $json_data);
        $bob = new Confirm($data['action'], url('/key'), $email, $token);
        $bob->subject('SSH.pub '.$data['action'].' Confirmation');
        Mail::to($email)->send($bob);
        return "Key received, check email to confirm upload.\n";
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return string
     */
    public function deleteIndex ($email, $keyname = 'default') {
        $token = Uuid::uuid4()->toString();
        $data['action'] = 'delete_key';
        $data['email'] = $email;
        $data['keyname'] = $keyname;
        $data['expires'] = Carbon::now()->addHour();
        $json_data = json_encode($data);
        Storage::put('keys/'.$token.'.json', $json_data);
        $bob = new Confirm($data['action'], url('/key'), $email, $token);
        $bob->subject('SSH.pub '.$data['action'].' Confirmation');
        Mail::to($email)->send($bob);
        return "Check your email to confirm key deletion.\n";
    }

    /**
     * @param Request $request
     * @param         $email
     * @param string  $keyname
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getUpload (Request $request, $email, $keyname = 'default') {
        return view('upload', ['email' => $email, 'keyname' => $keyname, 'keypath' => $request->input('keypath'), 'url_root' => url('/key')]);
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getInstall ($email, $keyname = 'default') {
        $aws = new \App\Aws;
        $key = $aws->lookup_key($email, $keyname);
        return view('install', ['keys' => [$key]]);
    }

    /**
     * @param $email
     */
    public function getAll ($email) {
        $aws = new \App\Aws;
        foreach($aws->lookup_keys($email) as $key) {
            echo $aws->lookup_key($email, $key)['key'];
        }
    }

    /**
     * @param $email
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAllInstall ($email) {
        $aws = new \App\Aws;
        $keys = $aws->lookup_keys($email);
        foreach ($keys as $key) {
            $data[] = $aws->lookup_key($email, $key);
        }
        return view('install', ['keys' => $data]);
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return mixed
     */
    public function getFingerprint ($email, $keyname = 'default') {
        $aws = new \App\Aws;
        $key = $aws->lookup_key($email, $keyname)['key'];
        $cleanedKey = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($key));
        $buffer = base64_decode($cleanedKey);
        $hash = md5($buffer);

        return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
    }

    /**
     * @param $email
     * @param $token
     */
    public function getConfirmToken ($email, $token) {
        $aws = new \App\Aws;
        try {
            $data = Storage::get('keys/'.$token.'.json');
        } catch (FileNotFoundException $e) {
            die('Token Expired');
        }
        $data = json_decode($data);
        if ($email != $data->email) {
            die('Email Mismatch');
        }
        if(!Carbon::now()->lt(Carbon::parse($data->expires->date))) {
            Storage::delete('keys'.$token.'.json');
            die('Token Expired');
        }
        switch ($data->action) {
            case 'upload_key':
                $aws->upload_key($data->email, $data->keyname, $data->key);
                break;
            case 'delete_key':
                $aws->delete_key($data->email, $data->keyname);
                break;
            default:
                # code...
                break;
        }
        Storage::delete('keys/'.$token.'.json');
        echo 'Action Completed';
    }
}
