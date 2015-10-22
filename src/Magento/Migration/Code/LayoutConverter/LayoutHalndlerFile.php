<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\LayoutConverter;

use Magento\Migration\Utility\M1\File;

class LayoutHalndlerFile
{
    const FILE_PERMS = 0777;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var string
     */
    protected $moduleNamespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $handlerFileName;

    /**
     * @var string
     */
    protected $xml;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param string $handlerFileName
     * @param string $xml
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $file,
        $handlerFileName,
        $xml
    ) {
        $this->file = $file;
        $this->handlerFileName = $handlerFileName;
        $this->xml = $xml;
        $this->parseModuleNameSpace();
    }

    /**
     * @return $this
     */
    protected function parseModuleNameSpace()
    {
        if ($this->handlerFileName) {
            if (preg_match('/app\/code\/([^\/]+)\/([^\/]+)\/view/is', $this->handlerFileName, $match)) {
                if (count($match) == 3) {
                    $this->moduleNamespace = $match[1];
                    $this->moduleName = $match[2];
                }

            }
        }
        return $this;
    }

    public function createFileHandler()
    {
        if ($this->handlerFileName) {
            $this->xml = "<?xml version=\"1.0\"?>\n<layout>\n" . $this->xml . '</layout>';
            $this->xml = $this->formatFileHandler($this->xml);
            return $this->file->filePutContents($this->handlerFileName, $this->xml);
        }
    }

    protected function formatFileHandler($xml)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->loadXML($xml);

        $stylesheet = new \DOMDocument();
        $stylesheet->preserveWhiteSpace = true;

        $formatter = new \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter();

        $files = glob(__DIR__ . '/XmlProcessors/_files/*.xsl');

        foreach ($files as $file) {
            $stylesheet->load($file);
            $xslt = new \XSLTProcessor();
            $xslt->registerPHPFunctions();
            $xslt->importStylesheet($stylesheet);
            $doc->loadXML($xslt->transformToXml($doc));
        }

        return  $formatter->format($doc->saveXML());
    }
}
