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
use Grav\Common\Filesystem\Folder;

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

    protected $config;

    protected $file = 'backup://.dropboxsync';

    public function __construct(array $credentials = [])
    {
        require_once(dirname(__DIR__).'/vendor/DropPHP/DropboxClient.php');
        $this->api = new \DropboxClient($credentials, 'en');

        // Resolve path
        $locator = self::getGrav()['locator'];
        $this->file = $locator->findResource($this->file, true, true);
        $this->init();

        // Checks if access token is required
        if(!$this->IsAuthorized()) {
            $this->auth();
        } else {
            $this->sync();
        }
    }

    public function __call($function, $params) {
        if (method_exists($this->api, $function)) {
            return call_user_func_array([$this->api, $function], $params);
        } else {
            throw new \Exception("Function '$function' not found.");
        }

        return $this;
    }

    public function init(array $config = []) {
        if (!$this->config) {
            $this->config = $this->loadConfig($this->file, $config);
        }

        switch ($this->config['action']) {
            case 'access':
                // There already exists an access token, load it
                $this->SetAccessToken($this->config['token']);

                echo "<pre>";
                echo "<b>Config:</b>\r\n";
                print_r($this->config);
                echo "<b>Account:</b>\r\n";
                print_r($this->GetAccountInfo());
                $files = $this->GetFiles("",false);
                echo "\r\n\r\n<b>Files:</b>\r\n";
                print_r(array_keys($files));
                break;

            case 'auth':
                // Are we coming from dropbox's auth page?
                if(!empty($_GET['auth_callback'])) {
                    // Check if token match the previous one
                    if ($_GET['oauth_token'] !== $this->config['token']['t']) {
                        throw new \Exception('Request token not found!');
                    }

                    // Get & store access token, the request token is not needed anymore
                    $this->config['action'] = 'access';
                    $this->config['token'] = $this->GetAccessToken($this->config['token']);

                    $this->storeConfig($this->file, $this->config);

                    $url = strtok($_SERVER['REQUEST_URI'], '?');
                    $request_Scheme = 'SCHEME NOTSET';
                    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] = 'on'){
                        $request_Scheme = 'HTTPS';
                    }
                    else {
                        $request_Scheme = 'HTTP';
                    }
                    header('Location: '.$request_Scheme.'://'.$_SERVER['HTTP_HOST'].$url);
                } else {
                    call_user_func([$this, $this->config['action']]);
                    $this->storeConfig($this->file, $this->config);
                }
                break;
        }
        exit();
    }

    public function auth($reset = true) {
        // Checks if access token is required
        if($reset || !$this->IsAuthorized()) {
            // Redirect user to dropbox auth page
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            $returnUrl = 'http://'.$_SERVER['HTTP_HOST'].$url.'?auth_callback=1';
            $authUrl = $this->BuildAuthorizeUrl($returnUrl);

            $this->config['action'] = 'auth';
            $this->config['token'] = $this->GetRequestToken();
            echo("Authentication required. <a href='$authUrl'>Click here.</a>");
        }
    }

    public function sync($source, $target = '') {
        echo "sync";
        $file = dirname(__DIR__).'/vendor/DropPHP/README.md';
        $this->upload([$file]);
    }

    // -----------------------------

    public function upload($files)
    {
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

    /** -------------------------------
     * Private/protected helper methods
     * --------------------------------
     */

    protected function loadConfig($file, array $config = []) {
        $config += [
            'action' => 'auth',
            'token' => [],
        ];

        if (!file_exists($file)) {
            Folder::mkdir(dirname($file));
            $this->storeConfig($file, $config);
        }

        return json_decode(file_get_contents($file), true);
    }

    protected function storeConfig($file, array $config = []) {
        $content = json_encode($config);
        return file_put_contents($file, $content);
    }
}
