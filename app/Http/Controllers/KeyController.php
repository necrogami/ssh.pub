<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Mail\Confirm;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Carbon\Carbon;
use App\Key;

class KeyController extends Controller
{
    /**
     * @param        $email
     * @param string $keyname
     *
     * @return string
     */
    public function getIndex ($email, $keyname = 'default') {
        try {
            $key = Storage::get(Key::path($email, $keyname));
        } catch (FileNotFoundException $e) {
            return 'Key Not Found';
        }
        return $key;
    }

    /**
     * @param Request $request
     * @param         $email
     * @param string  $keyname
     *
     * @return string
     */
    public function postIndex (Request $request, $email, $keyname = 'default') {
        $key = trim(file_get_contents($request->file('key')->path()));
        Key::mail('upload_key', $email, $keyname, $key);
        return "Key received, check email to confirm upload.\n";
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return string
     */
    public function deleteIndex ($email, $keyname = 'default') {
        Key::mail('delete_key', $email, $keyname);
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
        try {
            $key = Storage::get(Key::path($email, $keyname));
        } catch (FileNotFoundException $e) {
            return 'echo "Key Not Found"';
        }
        $key = [
            'key' => $key,
            'keyname' => $keyname,
            'email' => $email,
            'url_root' => url('/key')
        ];
        return view('install', ['keys' => [$key]]);
    }

    /**
     * @param $email
     */
    public function getAll ($email) {
        $files = Storage::files(Key::path($email));
        if (count($files) == 0) {
            return "No keys found";
        }

        foreach($files as $keyname) {
            echo Storage::get($keyname) . PHP_EOL;
        }
    }

    /**
     * @param $email
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAllInstall ($email) {
        $files = Storage::files(Key::path($email));
        if (count($files) == 0) {
            return 'echo "No keys found"';
        }
        foreach ($files as $keyname) {
            $path = explode('/', $keyname);
            $keyname = last($path);
            $keys[] = [
                'keyname' => $keyname,
                'key' => Storage::get(Key::path($email, $keyname)),
                'email' => $email,
                'url_root' => url('/key')
            ];
        }
        return view('install', ['keys' => $keys]);
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return mixed
     */
    public function getFingerprint ($email, $keyname = 'default') {
//        $aws = new \App\Aws;
//        $key = $aws->lookup_key($email, $keyname)['key'];
        try {
            $key = Storage::get(Key::path($email, $keyname));
            $cleanedKey = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($key));
            $buffer = base64_decode($cleanedKey);
            $hash = md5($buffer);

            return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
        } catch (FileNotFoundException $e) {
            return 'Key Not Found';
        }
    }

    /**
     * @param $email
     * @param $token
     */
    public function getConfirmToken ($email, $token) {
        try {
            $data = Storage::disk('local')->get('keys/'.$token.'.json');
        } catch (FileNotFoundException $e) {
            die('Token Expired');
        }
        $data = json_decode($data);
        if ($email != $data->email) {
            die('Email Mismatch');
        }
        if(!Carbon::now()->lt(Carbon::parse($data->expires->date))) {
            Storage::disk('local')->delete('keys'.$token.'.json');
            die('Token Expired');
        }
        switch ($data->action) {
            case 'upload_key':
                Storage::put(Key::path($email, $data->keyname), $data->key);
                break;
            case 'delete_key':
                Storage::delete(Key::path($email, $data->keyname));
                break;
            default:
                # code...
                break;
        }
        Storage::disk('local')->delete('keys/'.$token.'.json');
        echo 'Action Completed';
    }
}
