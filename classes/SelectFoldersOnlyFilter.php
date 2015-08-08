<?php
/**
 * DropBox Sync
 *
 * Helper class to sync data with your DropBox account.
 *
 * Licensed under MIT, see LICENSE.
 */

namespace Grav\Plugin\DropBoxSync;

/**
 * Select Folders Only Filter
 *
 * Helper class to filter out folders that are not wanted in recursive iterator
 */

class SelectFoldersOnlyFilter extends \RecursiveFilterIterator
{
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        $currentPath = $iterator->current()->getPathname();
        
        //check if folder
        if (!in_array($currentPath, $this->ignoreList())){ //check for cache folder
            return true;
        }
    }
    
     /**
     * ignoreList array of filenames or directories to ignore
     * @return array 
     */
    private function ignoreList(){
        return array(
            $_SERVER['DOCUMENT_ROOT'] . '/assets',
            $_SERVER['DOCUMENT_ROOT'] . '/cache',
            $_SERVER['DOCUMENT_ROOT'] . '/system'
        );
    }
}

?>