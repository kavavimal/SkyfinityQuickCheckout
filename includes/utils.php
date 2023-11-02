<?php

defined('ABSPATH') || exit;

/**

 * Add a flash notice to {prefix}options table until a full page refresh is done

 *

 * @param string $notice our notice message

 * @param string $type This can be "info", "warning", "error" or "success", "warning" as default

 * @param boolean $dismissible set this to TRUE to add is-dismissible functionality to your notice

 * @return void

 */



function skyfinityqc_add_flash_notice($notice = "", $type = "warning", $dismissible = true)

{



    $notices = get_option("skyfinityqc_flash_notices", array());



    $dismissible_text = ($dismissible) ? "is-dismissible" : "";



    // We add our new notice.

    array_push(

        $notices,

        array(

            "notice" => $notice,

            "type" => $type,

            "dismissible" => $dismissible_text

        )

    );



    update_option("skyfinityqc_flash_notices", $notices);



}



/**

 * Function executed when the 'admin_notices' action is called, here we check if there are notices on

 * our database and display them, after that, we remove the option to prevent notices being displayed forever.

 * @return void

 */



function skyfinityqc_display_flash_notices()

{

    $notices = get_option("skyfinityqc_flash_notices", array());



    // Iterate through our notices to be displayed and print them.

    foreach ($notices as $notice) {



        printf(

            '<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',

            $notice['type'],

            $notice['dismissible'],

            $notice['notice']

        );

    }



    // Now we reset our options to prevent notices being displayed forever.

    if (!empty($notices)) {

        delete_option("skyfinityqc_flash_notices", array());

    }

}

add_action('admin_notices', 'skyfinityqc_display_flash_notices', 12);



function check_is_mobile()

{

    $useragent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);



    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))

        return true;

    else

        return false;

}





/**

 * Get Themes List

 */

function skyfinityqc_get_theme_colors()

{

    $theme_colors = array(

        'T1' => array(

            'color-primary' => '#D6445E',

            'color-primary-alpha' => '#D6445E4d',

            'color-primary-light' => '#DF445F',

            'color-secondary' => '#2A2742',

            'color-primary-gradient-start' => '#D6445E',

            'color-primary-gradient-end' => '#740015'

        ),

        'T2' => array(

            'color-primary' => '#1877F2',

            'color-primary-alpha' => '#1877F24d',

            'color-primary-light' => '#1A77F1',

            'color-secondary' => '#407BFF',

            'color-primary-gradient-start' => '#1877F2',

            'color-primary-gradient-end' => '#1877F2'

        )

    );

    $extra_theme_colors = apply_filters('skyfinityqc_theme_colors', array());

    return array_merge($theme_colors, $extra_theme_colors);

}