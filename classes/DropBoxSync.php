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
        //exit();
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

    public function sync($source = '', $target = '') {
        echo "sync";
        $file = $_SERVER['DOCUMENT_ROOT'];//dirname(dirname(__DIR__)); //.'/vendor/DropPHP/README.md';
        $this->upload($file);
    }

    // -----------------------------
    
    /**
     * upload 
     * @return metadata to page 
     */
    public function upload($dirtocopy)
    {

        require_once(dirname(__FILE__)."/VisibleOnlyFilter.php");
        require_once(dirname(__FILE__)."/FilesOnlyFilter.php");
        require_once(dirname(__FILE__)."/SelectFoldersOnlyFilter.php");

       if(!file_exists($dirtocopy)){

            exit("File $dirtocopy does not exist");
            
        } else {

            //if dealing with a file upload it
            if(is_file($dirtocopy)){
                //$this->uploadFile($dirtocopy);
                $meta = $this->api->UploadFile($dirtocopy, "\Grav",true);
                print_r($meta,true);
                   
            } else { //otherwise collect all files and folders

                $fileinfos = new \RecursiveIteratorIterator(
                    new FilesOnlyFilter(
                        new VisibleOnlyFilter(
                            new SelectFoldersOnlyFilter(
                            new \RecursiveDirectoryIterator(
                                $dirtocopy,
                                \FilesystemIterator::SKIP_DOTS
                                    | \FilesystemIterator::UNIX_PATHS
                            )
                        )
                    )),
                    \RecursiveIteratorIterator::LEAVES_ONLY,
                    \RecursiveIteratorIterator::CATCH_GET_CHILD
                );

                $count = 0;
                foreach ($fileinfos as $pathname => $fileinfo) {
                    echo $fileinfos->getSubPathname(), "\n";
                    echo dirname($fileinfos->getSubPathname()), "\n\n";
                    $count++;
                    set_time_limit($count*2000);
                    $this->api->UploadFile($pathname, '\Grav\\' . dirname($fileinfos->getSubPathname()));
                }
                echo $count;
            }
        }
    }//end public function upload()

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

?>