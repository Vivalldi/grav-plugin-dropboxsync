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
 * Visible Only Filter
 *
 * Helper class to filter out files that begin with "." (period) in recursive iterator
 */

class VisibleOnlyFilter extends \RecursiveFilterIterator
{
    public function accept()
    {
        $fileName = $this->getInnerIterator()->current()->getFileName();
        $firstChar = $fileName[0];
        return $firstChar !== '.';
    }
}

?>