<?php
namespace Grav\Plugin;

use Grav\Common\Data;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Page\Pages;
use RocketTheme\Toolbox\Event\Event;

//require_once("vendor/dropPHP/DropboxClient.php");
include(__DIR__.'/vendor/dropPHP/DropboxClient.php');

        $dropbox = new DropboxClient(array(
            'app_key' => $this->config->get('plugins.dropbox.app_key'),
            'app_secret' => $this->config->get('plugins.dropbox.app_secret'),
            'app_full_acces' => false,
        ),'en');


class DropBoxSyncPlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        DropBox_Upload("vendor/dropPHP/readme.md");
    }
    function DropBox_Upload($files)
    {
        global $dropbox;
        handle_dropbox_auth($dropbox);
        foreach ($files as $key => $this_file)
        {
            if ( file_exists($this_file) )
            {
                try {
                    $upload_name=$this_file;
                    $result="<pre>";
                    $result.="\r\n\r\n\<b>Uploading $upload_name:</b>\r\n";
                    $meta=$dropbox->UploadFile($this_file);
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

    function store_token($token, $name)
    {
        file_put_contents("tokens/$name.token", serialize($token));
    }

    function load_token($name)
    {
        if(!file_exists("tokens/$name.token")) return null;
        return @unserialize(@file_get_contents("tokens/$name.token"));
    }

    function delet_token($name)
    {
        @unlink("tokens/$name.token");
    }

    function handle_dropbox_auth($dropbox)
    {
        $access_token=load_token("access");
        if(!empty($access_token)){
            $dropbox->SetAccessToken($access_token);
        }
        elseif(!empty($_GET['auth_callback']))
        {
            $request_token = load_token($_GET['oauth_token']);
            if(empty($request_token)) die ('Request token not found!');
            $access_token = $dropbox->GetAccessToken($request_token);
            store_token($access_token, "access");
            delete_token($_GET['oauth_token']);
        }

        if(!$dropbox->IsAuthorized())
        {
            $return_url = "http://".$_SERVER['HTTP_HOST'].$SERVER['SCRIPT_NAME']."?auth_callback=1";
            $auth_url=$dropbox->BuildAuthorizeUrl($return_url);
            $request_token=$dropbox->GetRequestToken();
            store_token($request_token, $request_token['t']);
            die("Authentication required. <a href='$auth_url'>Click here.</a>");
        }
    }
}
