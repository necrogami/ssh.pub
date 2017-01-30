<?php

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use App\Mail\Confirm;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect('https://github.com/necrogami/ssh.pub');
});

Route::group(['prefix' => 'key/{email}'], function () {


    // /key/user@domain.tld/
    Route::get('/', function ($email) {
        $aws = new \App\Aws;
        return (string) $aws->lookup_key($email);
    });


    // /key/user@domain.tld/ -- POST
    Route::post('/', function (Request $request, $email) {
        $token = Uuid::uuid4()->toString();
        $data['action'] = 'upload_key';
        $data['email'] = $email;
        $data['keyname'] = 'default';
        $data['expires'] = Carbon::now()->addHour();
        $data['key'] = file_get_contents($request->file('key')->path());
        $json_data = json_encode($data);
        Storage::put('keys/'.$token.'.json', $json_data);
        $bob = new Confirm($data['action'], url('/key'), $email, $token);
        $bob->subject('SSH.pub '.$data['action'].' Confirmation');
        Mail::to($email)->send($bob);
        return "Key received, check email to confirm upload.\n";
    });

    // /key/user@domain.tld/ -- DELETE
    Route::delete('/', function ($email) {
        $token = Uuid::uuid4()->toString();
        $data['action'] = 'delete_key';
        $data['email'] = $email;
        $data['keyname'] = 'default';
        $data['expires'] = Carbon::now()->addHour();
        $json_data = json_encode($data);
        Storage::put('keys/'.$token.'.json', $json_data);
        $bob = new Confirm($data['action'], url('/key'), $email, $token);
        $bob->subject('SSH.pub '.$data['action'].' Confirmation');
        Mail::to($email)->send($bob);
        return "Check your email to confirm key deletion.\n";
    });


    // /key/user@domain.tld/upload
    Route::get('upload', function (Request $request, $email) {
        return view('upload', ['email' => $email, 'keyname' => '', 'keypath' => $request->input('keypath'), 'url_root' => url('/key')]);
    });


    // /key/user@domain.tld/install
    Route::get('install', function ($email) {
        $aws = new \App\Aws;
        $key = $aws->lookup_key($email);
        return view('install', ['keys' => [$key]]);
    });


    // /key/user@domain.tld/all
    Route::get('all', function ($email) {
        $aws = new \App\Aws;
        foreach($aws->lookup_keys($email) as $key) {
            echo $aws->lookup_key($email, $key);
        }
    });


    // /key/user@domain.tld/all/install
    Route::get('all/install', function ($email) {
        $aws = new \App\Aws;
        $keys = $aws->lookup_keys($email);
        foreach ($keys as $key) {
            $data[] = $aws->lookup_key($email, $key);
        }
        return view('install', ['keys' => $data]);
    });



    Route::get('fingerprint', function ($email) {
        $aws = new \App\Aws;
        $key = $aws->lookup_key($email);
        $cleanedKey = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($key));
        $buffer = base64_decode($cleanedKey);
        $hash = md5($buffer);

        return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
    });



    Route::get('confirm/{token}', function ($email, $token) {
        $aws = new \App\Aws;
        try {
            $data = Storage::get('keys/'.$token.'.json');
        } catch (FileNotFoundException $e) {
            die('Token Expired');
        }
        $data = json_decode($data);
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

    });



    Route::group(['prefix' => '{keyname}'], function () {


        // /key/user@domain.tld/namedkey/
        Route::get('/', function ($email, $keyname) {
            $aws = new \App\Aws;
            return (string) $aws->lookup_key($email, $keyname);
        });


        // /key/user@domain.tld/ -- POST
        Route::post('/', function (Request $request, $email, $keyname) {
            $token = Uuid::uuid4()->toString();
            $data['action'] = 'upload_key';
            $data['email'] = $email;
            $data['keyname'] = $keyname;
            $data['expires'] = Carbon::now()->addHour();
            $data['key'] = file_get_contents($request->file('key')->path());
            $json_data = json_encode($data);
            Storage::put('keys/'.$token.'.json', $json_data);
            $bob = new Confirm($data['action'], url('/key'), $email, $token);
            $bob->subject('SSH.pub '.$data['action'].' Confirmation');
            Mail::to($email)->send($bob);
            return "Key received, check email to confirm upload.\n";
        });

        // /key/user@domain.tld/ -- DELETE
        Route::delete('/', function ($email, $keyname) {
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
        });


        // /key/user@domain.tld/namedkey/upload
        Route::get('upload', function (Request $request, $email, $keyname) {
            return view('upload', ['email' => $email, 'keyname' => $keyname, 'keypath' => $request->input('keypath'), 'url_root' => url('/key')]);
        });


        // /key/user@domain.tld/namedkey/install
        Route::get('install', function ($email, $keyname) {
            $aws = new \App\Aws;
            $key = $aws->lookup_key($email, $keyname);
            return view('install', ['keys' => [$key]]);
        });


        // /key/user@domain.tld/namedkey/fingerprint
        Route::get('fingerprint ', function ($email, $keyname) {
            $aws = new \App\Aws;
            $key = $aws->lookup_key($email, $keyname);
            $cleanedKey = preg_replace('/^(ssh-[dr]s[as]\s+)|(\s+.+)|\n/', '', trim($key));
            $buffer = base64_decode($cleanedKey);
            $hash = md5($buffer);

            return preg_replace('/(.{2})(?=.)/', '$1:', $hash);
        });
    });

});
