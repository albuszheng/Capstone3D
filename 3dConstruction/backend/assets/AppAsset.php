<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/screen.css',
    ];
    public $js = [
        'assets/da80f562/jquery.js',
        'js/three.js',
        'js/ThreeBSP.js',
        'js/ColladaLoader.js',
        'js/FirstPersonControls.js',
        'js/TrackballControls.js',
        'js/SceneExport.js',
        'js/pixi.js',
        'js/SceneLoad.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
