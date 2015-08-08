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
 * Files Only Filter
 *
 * Helper class to filter out folders in recursive iterator
 */

class FilesOnlyFilter extends \RecursiveFilterIterator
{
    public function accept()
    {
        $iterator = $this->getInnerIterator();

        // allow traversal
        if ($iterator->hasChildren()) {
            return true;
        }

        // filter entries, only allow true files
        return $iterator->current()->isFile();
    }
}

?>