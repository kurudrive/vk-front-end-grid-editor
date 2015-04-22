<?php
/*
 * Plugin Name: VK Front-end Grid Editor
 * Plugin URI:
 * Description:
 * Version:
 * Author: kurudrive @ Vektor,Inc
 * Author URI:
 * Text Domain:
 * Domain Path: languages
 * Network:     false
 * License:     GPL-2.0+
 */

/*-------------------------------------------*/
/* ajax _ jsファイル読み込み
/*-------------------------------------------*/
if (  function_exists( 'vkEdit2_setup' ) ) :
	add_action( 'after_setup_theme', 'vkEdit2_setup' );
endif;

function vkEdit2_setup() {
	add_action('wp_enqueue_scripts','vkEdit2_scripts',5);
}

function vkEdit2_scripts(){
    if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
    	wp_enqueue_script( 'jquery' );
    	wp_enqueue_script('vkEdit2_main_js', plugins_url("js/vkEdit2_main.js", __FILE__) ,array(), '1.0', true);
    } // if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
}

/*-------------------------------------------*/
/* 編集用CSSファイルの読み込み
/*-------------------------------------------*/
function vkEdit2_style_setup(){
    if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
        wp_enqueue_style( 'vkEdit2_style_setup_load_admin_css', plugins_url('css/admin_style.css', __FILE__) , false, '2015-04-13');
        wp_enqueue_style( 'vkEdit2_style_setup_load_awesome_css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' , false);
    } // if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
}
add_action('wp_head', 'vkEdit2_style_setup');

/*-------------------------------------------*/
/* フロント用CSSファイルの読み込み
/*-------------------------------------------*/
function vkEdit2_front_style_setup(){
    wp_enqueue_style( 'vkEdit2_style_setup_load_bootstrap_css', plugins_url('css/bootstrap.min.css', __FILE__) , false, '2015-04-19');
}
add_action('wp_head', 'vkEdit2_front_style_setup');

/*-------------------------------------------*/
/*  Admin page _ Add editor css
/*-------------------------------------------*/
function vkEdit_add_editor_style_setup() {
    $gridCss_url = plugins_url('css/bootstrap.min.css', __FILE__);
    add_editor_style( $gridCss_url );
}
add_action( 'after_setup_theme', 'vkEdit_add_editor_style_setup' );

/*-------------------------------------------*/
/* ajax _ URLを取得・設定
/*-------------------------------------------*/
function add_my_ajaxurl() {
?>
    <script>
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php'); ?>';
    </script>
<?php
}
add_action( 'wp_head', 'add_my_ajaxurl', 1 );

/*-------------------------------------------*/
/* ajax _
/*-------------------------------------------*/
function content_edit() {
    print '<pre style="text-align:left">';print_r(get_edit_post_link( $post->ID ));print '</pre>';
    if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
?>
<script>
function vkEdit_saveStart(){
    jQuery('#submit').click(function(){
        // phpに投げる変数（変更するポストID）
        <?php global $post; ?>
        var post_id = '<?php echo $post->ID; ?>';
        var post_content = jQuery('#vkEdit_editWrap').html();
        // 保存ボタンをクリックされたらボタンを変更する
        jQuery(this).html('<i class="fa fa-refresh"></i>');
        // 編集パネルが消える前に保存されるのを防ぐために少し待つ
        setTimeout(function(){        
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    // 実行するphp関数
                    'action' : 'ajax_post_update',
                    'post_id' : post_id,
                    'post_content' : post_content,
                },
                success: function( response ){
                    jQuery('#vkEdit_masterCtrlPanel button').remove();
                    jQuery('#vkEdit_masterCtrlPanel').removeClass('vkEdit_masterCtrlPanel_alert');
                    jQuery('#vkEdit_masterCtrlPanel p').html('Save was successfully.<br>If the display is broken, please reload the page.');
                    jQuery('#vkEdit_editWrap').html(response);
                    location.reload();
                }
            });
        },500);
        return false;
    });  
}
</script>
<?php
    } // if ( get_edit_post_link( $post->ID ) ) { // 記事の編集権限があるなら
}
add_action( 'wp_footer', 'content_edit', 1 );

/*-------------------------------------------*/
/* ajax _ 実行するPHPの関数 
/*-------------------------------------------*/
function ajax_post_update(){
    // ajaxで受け取る変数（変更するポストID）
    $post_id = $_POST['post_id'];
    $post_content = $_POST['post_content'];

	/* コンテンツの書き換え
	/*-------------------------------------------*/
	$my_post = array();
	$my_post['ID'] = $post_id;
    $my_post['post_content'] = $post_content;
	// データベースの投稿情報を更新
	wp_update_post( $my_post );

    /* フロントにコンテンツを返す
    /*-------------------------------------------*/   
	echo $post_content;
	die();
}
// ログインユーザー用
add_action( 'wp_ajax_ajax_post_update', 'ajax_post_update' );
// 未ログインユーザー用
add_action( 'wp_ajax_nopriv_ajax_post_update', 'ajax_post_update' );

/*-------------------------------------------*/
/*  When content empty
/*-------------------------------------------*/
add_filter( 'the_content', 'vkEdit_add_editWrap',2);
function vkEdit_add_editWrap($content){

    // $contentの中身が何もなかった場合の処理
    if ( $content == '') $content = '<div class="row"><div class="col-sm-12">Input here.</div></div>';

    // $contentを保存用のdivで囲う
    $content = '<div id="vkEdit_editWrap">'.$content.'</div>';
    return $content;
}
