<?php
/**
 * Created by PhpStorm.
 * User: minhtran
 * Date: 10/26/18
 * Time: 23:38
 */

namespace CastPlugin;


class CpAdminNotice
{

    /**
     * CpAdminNotice constructor.
     */
    public function __construct()
    {
    }

    public static function noticeError($message){
        self::error($message);
    }

    public function error($message)
    {
        add_action( 'admin_notices', function() use ($message){
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php echo $message ?></p>
            </div>
            <?php
        } );
    }
    public function warning($message)
    {
        add_action( 'admin_notices', function() use ($message){
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php echo $message ?></p>
            </div>
            <?php
        } );
    }
}