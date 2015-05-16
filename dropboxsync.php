<?php
/**
 * DropBox Sync v1.0.0
 *
 * This plugin syncs data with your DropBox account.
 *
 * Licensed under MIT, see LICENSE.
 *
 * @package     DropBox Sync
 * @version     1.0.0
 * @link        <https://github.com/Vivalldi/grav-plugin-dropboxsync>
 * @author      Tyler Cosgrove <vivalldi1998@gmail.com>
 * @author      Benjamin Regler <sommerregen@benjamin-regler.de>
 * @copyright   2015, Tyler Cosgrove and Benjamin Regler
 * @license     <http://opensource.org/licenses/MIT>            MIT
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

use Grav\Plugin\DropBoxSync\DropBoxSync;

/**
 * DropBox Sync v1.0.0
 *
 * This plugin syncs your data with your DropBox account.
 */
class DropBoxSyncPlugin extends Plugin
{
    /** ---------------------------
     * Private/protected properties
     * ----------------------------
     */

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * Instance of DropBoxSync class
     *
     * @var object
     */
    protected $backend;

    /** -------------
     * Public methods
     * --------------
     */

    /**
     * Return a list of subscribed events.
     *
     * @return array    The list of events of the plugin of the form
     *                      'name' => ['method_name', priority].
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Enable DropBoxSync only if url matches to the configuration.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $route = $this->config->get('plugins.dropboxsync.route');

        if ($this->config->get('plugins.dropboxsync.enabled') && $route && $route == $uri->path()) {
            $this->active = true;

            require_once(__DIR__.'/classes/DropBoxSync.php');

            // Get API key and API secret
            $credentials = [
                'app_key' => $this->config->get('plugins.dropboxsync.app.key'),
                'app_secret' => $this->config->get('plugins.dropboxsync.app.secret'),
                'app_full_access' => false
            ];

            // Initialize DropBoxSync class
            $this->backend = new DropBoxSync($credentials);
        }
    }
}
