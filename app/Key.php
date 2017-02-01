<?php

namespace App;

use LayerShifter\TLDExtract\Extract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use App\Mail\Confirm;
use Carbon\Carbon;


/**
 * Class Key
 * @package App
 */
class Key
{
    /**
     * @param      $action
     * @param      $email
     * @param      $keyname
     * @param null $key
     *
     * @return string|void
     */
    static public function mail ($action, $email, $keyname, $key = NULL) {
        $token = Uuid::uuid4()->toString();
        $data['action'] = $action;
        $data['email'] = $email;
        $data['keyname'] = $keyname;
        $data['expires'] = Carbon::now()->addHour();
        if (!is_null($key)) {
            if (!in_array(substr($key, 0, 7), ['ssh-rsa', 'ssh-dss'], true)) {
                return "This is not a valid DSA or RSA Public SSH Key.\n";
            }
            $data['key'] = $key;
        }
        $json_data = json_encode($data);
        Storage::disk('local')->put('keys/'.$token.'.json', $json_data);
        $bob = new Confirm($data['action'], url('/key'), $email, $token);
        $bob->subject('SSH.pub '.$data['action'].' Confirmation');
        Mail::to($email)->send($bob);
    }

    /**
     * @param        $email
     * @param string $keyname
     *
     * @return string
     */
    static public function path ($email, $keyname = NULL) {
        $extract = new Extract;
        $domain = explode('@', $email);
        $result = $extract->parse($domain[1]);
        $path = $result->suffix . DIRECTORY_SEPARATOR . $result->hostname;
        if (!is_null($result->subdomain)) {
            $path .= DIRECTORY_SEPARATOR . $result->subdomain;
        }
        $path .= DIRECTORY_SEPARATOR . $domain[0];
        Storage::makeDirectory($path);
        if (!is_null($keyname)) {
            $path .= DIRECTORY_SEPARATOR . $keyname;
        }
        return $path;
    }
}