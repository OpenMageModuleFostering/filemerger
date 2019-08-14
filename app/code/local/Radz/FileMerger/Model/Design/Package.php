<?php


class Radz_FileMerger_Model_Design_Package extends Mage_Core_Model_Design_Package
{

   /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        $fileVersions = array();
        foreach ($files as $file) {
            $fileVersions[] = $file . '?' . filemtime($file);
        }

        // merge into target file
        $targetFilename = md5(implode(',', $fileVersions) . "|{$hostname}|{$port}") . '.css';
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, array($this, 'beforeMergeCss'), 'css')) {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }

    public function getMergedJsUrl($files)
    {
        $fileVersions = array();
        foreach ($files as $file) {
            $fileVersions[] = $file . '?' . filemtime($file);
        }

        $targetFilename = md5(implode(',', $fileVersions)) . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }

}