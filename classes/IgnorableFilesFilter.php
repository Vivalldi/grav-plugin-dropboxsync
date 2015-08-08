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
 * Ignorable Files Filter
 *
 * Helper class to filter out files that are not wanted in recursive iterator
 */

class IgnorableFilesFilter extends \RecursiveFilterIterator
{
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        $fileName = $this->getInnerIterator()->current()->getFileName();

        //check if folder
        if (!in_array($fileName, $this->ignoreableFiles())){
            return true;
        }
    }

     /**
     * ignoreList array of filenames or directories to ignore
     * @return array
     */
    private function ignoreableFiles(){
        return array(
            'Thumbs.db'
        );
    }
}

?>
