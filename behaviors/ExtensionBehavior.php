<?php
/**
 * ExtensionBehavior class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-extension.behaviors
 */

/**
 * Extension behavior for components.
 */
abstract class ExtensionBehavior extends CBehavior
{
    /**
     * @var string the id of the database connection component.
     */
    public $connectionID = 'db';

    /** @var string */
    private $_assetsUrl;

    /**
     * Returns the path alias for the extension.
     * @return string the alias or false if not set.
     */
    abstract public function getAlias();

    /**
     * Returns the path for the extension.
     * @return string the path or false if not set.
     */
    abstract public function getPath();

    /**
     * Imports the a class or directory.
     * The path alias is automatically prepended if applicable.
     * @param string $alias path alias to be imported.
     * @param boolean $forceInclude whether to include the class file immediately.
     * @return string the class name or the directory that this alias refers to.
     * @throws \CException if the alias is invalid.
     */
    public function import($alias, $forceInclude = false)
    {
        if (($baseAlias = $this->getAlias()) !== null) {
            $alias = $baseAlias . '.' . $alias;
        }
        return Yii::import($alias, $forceInclude);
    }

    /**
     * Returns the database connection for this component.
     * @return \CDbConnection the connection component.
     * @throws \CException if the component does not exist or is not an instance of CDbConnection.
     */
    public function getDbConnection()
    {
        if (!Yii::app()->hasComponent($this->connectionID)) {
            throw new CException(sprintf('Connection component "%s" does not exist.', $this->connectionID));
        }
        $db = Yii::app()->getComponent($this->connectionID);
        if (!$db instanceof CDbConnection) {
            throw new CException(sprintf(
                'Connection component "%s" is not an instance of CDbConnection.',
                $this->connectionID
            ));
        }
        return $db;
    }

    /**
     * Publishes the extension assets.
     * @param string $path assets path.
     * @param boolean $forceCopy whether we should copy the asset file or directory
     * even if it is already published before.
     * @return string the url.
     */
    public function publishAssets($path, $forceCopy = false)
    {
        if (!Yii::app()->hasComponent('assetManager')) {
            return false; // ignore this method while ran from the console
        }
        /* @var CAssetManager $assetManager */
        $assetManager = Yii::app()->getComponent('assetManager');
        if (($basePath = $this->getPath()) !== false) {
            $path = $basePath . DIRECTORY_SEPARATOR . $path;
        }
        $assetsUrl = $assetManager->publish($path, false, -1, $forceCopy);
        return $this->_assetsUrl = $assetsUrl;
    }

    /**
     * Registers a CSS file.
     * @param string $url URL of the CSS file.
     * @param string $media media that the CSS file should be applied to.
     * @return CClientScript the client script component.
     */
    public function registerCssFile($url, $media = '')
    {
        if (($cs = $this->getClientScript()) === false) {
            return null;
        }
        if (isset($this->_assetsUrl)) {
            $url = $this->_assetsUrl . '/' . ltrim($url, '/');
        }
        return $cs->registerCssFile($url, $media);
    }

    /**
     * Registers a JavaScript file.
     * @param string $url URL of the javascript file.
     * @param integer $position the position of the JavaScript code.
     * @return CClientScript the client script component.
     */
    public function registerScriptFile($url, $position = null)
    {
        if (($cs = $this->getClientScript()) === false) {
            return null;
        }
        if (isset($this->_assetsUrl)) {
            $url = $this->_assetsUrl . '/' . ltrim($url, '/');
        }
        return $cs->registerScriptFile($url, $position);
    }

    /**
     * Returns the name of the correct script file to use.
     * @param string $filename the base file name.
     * @param boolean $minified whether to include the minified version (defaults to false).
     * @return string the full filename.
     */
    public function resolveScriptVersion($filename, $minified = false)
    {
        list($name, $extension) = str_split($filename, strrpos($filename, '.') + 1);
        return !$minified ? $name . $extension : $name . 'min.' . $extension;
    }

    /**
     * Returns the client script component.
     * @return CClientScript the component.
     */
    protected function getClientScript()
    {
        if (!Yii::app()->hasComponent('clientScript')) {
            return false;
        }
        return Yii::app()->getComponent('clientScript');
    }
}
