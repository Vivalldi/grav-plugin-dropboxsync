<?php
/**
 * DropBox Sync
 *
 * Helper class to sync data with your DropBox account.
 *
 * Licensed under MIT, see LICENSE.
 */

namespace Grav\Plugin\DropBoxSync;

use Grav\Common\Grav;
use Grav\Common\GravTrait;

/**
 * DropBox Sync
 *
 * Helper class to sync your data with your DropBox account.
 */
class DropBoxSync
{
	/**
     * @var DropBoxSync
     */
	use GravTrait;

    protected $api;

    public function __construct(array $credentials = [])
    {
       require_once(dirname(__DIR__).'/vendor/DropPHP/DropboxClient.php');

       $this->api = $dropbox = new \DropboxClient($credentials, 'en');


       $file = dirname(__DIR__).'/vendor/DropPHP/README.md';
       $this->upload([$file]);
    }

    public function upload($files)
    {
        $this->handle_dropbox_auth();
        foreach ($files as $key => $this_file)
        {
            if ( file_exists($this_file) )
            {
                try {
                    $upload_name=$this_file;
                    $result="<pre>";
                    $result.="\r\n\r\n\<b>Uploading $upload_name:</b>\r\n";
                    $meta=$this->api->UploadFile($this_file);
                    $result.=print_r($meta,true);
                    $result.="\r\n done!";
                    $result.="</pre>";

                    $result.='<span style=color:green">File successfully uploaded to you Dropbox! </span>';
                } catch(Exception $e) {
                    $result.='<span style="color: red">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                }
            }
        }//end foreach

        $backup_folder=dirname(__FILE__).'/backup/'."dropbox_results.txt"; ;
        $myFile = $backup_folder;
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, $result);
        fclose($fh);

        return $result;
    }//end function

    public function store_token($token, $name)
    {
        file_put_contents(dirname(__DIR__)."/classes/tokens/$name.token", serialize($token));
    }

    public function load_token($name)
    {
        if(!file_exists(dirname(__DIR__)."/classes/tokens/$name.token")) return null;
        return @unserialize(@file_get_contents(dirname(__DIR__)."/classes/tokens/$name.token"));
    }

    public function delete_token($name)
    {
        @unlink("tokens/$name.token");
    }

    public function handle_dropbox_auth()
    {
        $access_token= $this->load_token("access");
        if(!empty($access_token)){
            $this->api->SetAccessToken($access_token);
        }
        elseif(!empty($_GET['auth_callback']))
        {
            $request_token = $this->load_token($_GET['oauth_token']);
            if(empty($request_token)) die ('Request token not found!');
            $access_token = $this->api->GetAccessToken($request_token);
            $this->store_token($access_token, "access");
            $this->delete_token($_GET['oauth_token']);
        }

        if(!$this->api->IsAuthorized())
        {
            $return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
            $auth_url=$this->api->BuildAuthorizeUrl($return_url);
            $request_token=$this->api->GetRequestToken();
            $this->store_token($request_token, $request_token['t']);
            die("Authentication required. <a href='$auth_url'>Click here.</a>");
        }
    }
}
