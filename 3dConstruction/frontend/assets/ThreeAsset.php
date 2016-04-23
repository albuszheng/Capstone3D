<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Jia Liu
 * @since 2.0
 */
class ThreeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/web.css',
    ];
    public $js = [
//        'js/three.js',
//        'js/ThreeBSP.js',
//        'js/ColladaLoader.js',
//        'js/FirstPersonControls.js',
//        'js/SceneExport.js',
//        'js/pixi.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}