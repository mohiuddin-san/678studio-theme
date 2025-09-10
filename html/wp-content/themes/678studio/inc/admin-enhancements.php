<?php
/**
 * Admin Enhancements for Studio Shops
 * 管理画面の使いやすさを向上させる機能
 * 
 * @package 678studio
 */

// 管理画面にカスタムCSSとJSを追加
add_action('admin_enqueue_scripts', 'studio_shops_admin_scripts');
function studio_shops_admin_scripts($hook) {
    // Studio Shopsの編集画面でのみ読み込み
    global $post_type;
    if ($post_type === 'studio_shops') {
        
        // カスタムCSS
        wp_add_inline_style('wp-admin', '
        /* ACF Gallery field enhancements */
        .acf-gallery .acf-gallery-main {
            min-height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            position: relative;
            background: #fafafa;
        }
        
        .acf-gallery .acf-gallery-main:hover {
            border-color: #0073aa;
            background: #f0f8ff;
        }
        
        .acf-gallery .acf-gallery-main.dragover {
            border-color: #00a32a;
            background: #f0fff0;
        }
        
        .acf-gallery-instructions {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
            font-size: 14px;
            pointer-events: none;
        }
        
        .acf-gallery.has-value .acf-gallery-instructions {
            display: none;
        }
        
        /* Enhanced upload button */
        .acf-gallery .acf-button {
            background: #0073aa;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .acf-gallery .acf-button:hover {
            background: #005a87;
        }
        
        /* Image preview improvements */
        .acf-gallery .acf-gallery-attachment {
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        .acf-gallery .acf-gallery-attachment:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Studio Shops form improvements */
        .acf-field[data-name="gallery_images"] .acf-label {
            font-weight: 600;
            color: #23282d;
        }
        
        .acf-field[data-name="gallery_images"] .description {
            background: #e7f3ff;
            padding: 12px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
            margin-bottom: 10px;
            font-size: 13px;
            line-height: 1.4;
        }
        ');
        
        // カスタムJS
        wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            
            // ギャラリーフィールドの強化
            function enhanceGalleryField() {
                var $galleryField = $(".acf-field[data-name=\"gallery_images\"] .acf-gallery");
                
                if ($galleryField.length) {
                    var $main = $galleryField.find(".acf-gallery-main");
                    
                    // 説明テキストを追加
                    if (!$main.find(".acf-gallery-instructions").length) {
                        $main.append("<div class=\"acf-gallery-instructions\"><strong>複数画像の一括アップロード</strong><br>• ドラッグ&ドロップでファイルを追加<br>• Ctrl/Cmd+クリックで複数選択<br>• 最大50枚まで対応</div>");
                    }
                    
                    // ドラッグ&ドロップイベントの強化
                    $main.on("dragenter dragover", function(e) {
                        e.preventDefault();
                        $(this).addClass("dragover");
                    });
                    
                    $main.on("dragleave drop", function(e) {
                        e.preventDefault();
                        $(this).removeClass("dragover");
                    });
                    
                    // 画像数の表示
                    function updateImageCount() {
                        var count = $galleryField.find(".acf-gallery-attachment").length;
                        var $counter = $galleryField.find(".image-counter");
                        
                        if (count > 0) {
                            if (!$counter.length) {
                                $galleryField.find(".acf-gallery-toolbar").append("<span class=\"image-counter\" style=\"margin-left: 10px; font-weight: 500; color: #0073aa;\"></span>");
                                $counter = $galleryField.find(".image-counter");
                            }
                            $counter.text("画像数: " + count + "/50");
                            $galleryField.addClass("has-value");
                        } else {
                            $counter.remove();
                            $galleryField.removeClass("has-value");
                        }
                    }
                    
                    // MutationObserverで画像の追加/削除を監視
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === "childList") {
                                updateImageCount();
                            }
                        });
                    });
                    
                    observer.observe($main[0], {
                        childList: true,
                        subtree: true
                    });
                    
                    // 初期カウント
                    updateImageCount();
                }
            }
            
            // ページ読み込み時とACF更新時に実行
            enhanceGalleryField();
            
            // ACFが動的に更新される場合に対応
            $(document).on("acf/setup_fields", function() {
                setTimeout(enhanceGalleryField, 100);
            });
            
            // アップロードボタンのテキストを変更
            $(document).on("click", ".acf-gallery .acf-button", function() {
                setTimeout(function() {
                    $(".media-frame-title h1").text("ギャラリー画像を選択");
                    $(".media-toolbar-primary .button-primary").text("ギャラリーに追加");
                }, 100);
            });
            
            // フィールドグループのタブ強化
            $(".acf-tab-wrap .acf-tab-button").on("click", function() {
                var $tab = $(this);
                setTimeout(function() {
                    if ($tab.text().trim() === "ギャラリー") {
                        enhanceGalleryField();
                    }
                }, 100);
            });
        });
        ');
    }
}

// ヘルプテキスト削除済み

// カスタム投稿タイプのリスト表示をカスタマイズ
add_filter('manage_studio_shops_posts_columns', 'studio_shops_custom_columns');
function studio_shops_custom_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['thumbnail'] = '📷 メイン画像';
    $new_columns['address'] = '📍 住所';
    $new_columns['phone'] = '📞 電話番号';
    $new_columns['gallery_count'] = '🖼️ ギャラリー画像数';
    $new_columns['prefecture'] = '📍 都道府県';
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}

add_action('manage_studio_shops_posts_custom_column', 'studio_shops_custom_column_content', 10, 2);
function studio_shops_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'thumbnail':
            $image = get_field('main_image', $post_id);
            if ($image) {
                echo '<img src="' . esc_url($image['sizes']['thumbnail']) . '" alt="' . esc_attr($image['alt']) . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">';
            } else {
                echo '<span style="color: #999;">画像なし</span>';
            }
            break;
            
        case 'address':
            $address = get_field('address', $post_id);
            echo $address ? esc_html(mb_strimwidth($address, 0, 40, '...')) : '<span style="color: #999;">未設定</span>';
            break;
            
        case 'phone':
            $phone = get_field('phone', $post_id);
            echo $phone ? esc_html($phone) : '<span style="color: #999;">未設定</span>';
            break;
            
        case 'gallery_count':
            $gallery = get_field('gallery_images', $post_id);
            $count = is_array($gallery) ? count($gallery) : 0;
            echo '<span style="background: #0073aa; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">' . $count . ' 枚</span>';
            break;
            
        case 'prefecture':
            $terms = get_the_terms($post_id, 'studio_prefecture');
            if ($terms && !is_wp_error($terms)) {
                echo esc_html($terms[0]->name);
            } else {
                echo '<span style="color: #999;">未設定</span>';
            }
            break;
    }
}

// 都道府県タクソノミーメタボックスを削除
add_action('admin_menu', 'remove_studio_prefecture_metabox');
function remove_studio_prefecture_metabox() {
    remove_meta_box('studio_prefecturediv', 'studio_shops', 'side');
    remove_meta_box('tagsdiv-studio_prefecture', 'studio_shops', 'side');
}

// 保存時に都道府県を自動設定
add_action('save_post_studio_shops', 'auto_set_prefecture_from_address');
function auto_set_prefecture_from_address($post_id) {
    // 自動保存時はスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $address = get_field('address', $post_id);
    if ($address) {
        $prefectures = array(
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        );
        
        foreach ($prefectures as $prefecture) {
            if (strpos($address, $prefecture) !== false) {
                wp_set_object_terms($post_id, $prefecture, 'studio_prefecture');
                break;
            }
        }
    }
}