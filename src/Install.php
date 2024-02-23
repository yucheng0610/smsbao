<?php
namespace Yucheng0610\Smsbao;

use plugin\admin\api\Menu;
use plugin\smsbao\app\admin\controller\SettingController;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = array (
    'config/plugin/yucheng0610/smsbao' => 'config/plugin/yucheng0610/smsbao',
    );

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        if (Menu::get(SettingController::class)) {
            return;
        }
        // 找到通用菜单
        $commonMenu = Menu::get('common');
        if (!$commonMenu) {
            echo "未找到通用设置菜单" . PHP_EOL;
            return;
        }
        // 以通用菜单为上级菜单插入菜单
        $pid = $commonMenu['id'];
        Menu::add([
            'title' => '短信設置',
            'href' => '/app/smsbao/admin/setting',
            'pid' => $pid,
            'key' => SettingController::class,
            'weight' => 0,
            'type' => 1,
        ]);
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        Menu::delete(SettingController::class);
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path().'/'.substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/$source", base_path()."/$dest");
            copy_dir(__DIR__ . "/$source", base_path()."/$dest");
            echo "Create $dest";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path()."/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            echo "Remove $dest
";
            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }
            remove_dir($path);
        }
    }
    
}