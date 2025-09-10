<?php
/**
 * WordPress Standard Media Gallery for Studio Shops
 * ACF無料版対応のギャラリー機能
 */

// カスタムメタボックスを追加
add_action('add_meta_boxes', 'add_studio_gallery_metabox');
function add_studio_gallery_metabox() {
    add_meta_box(
        'studio-gallery-metabox',
        'ギャラリー画像管理',
        'studio_gallery_metabox_callback',
        'studio_shops',
        'normal',
        'high'
    );
}

// メタボックスの内容
function studio_gallery_metabox_callback($post) {
    wp_nonce_field('studio_gallery_metabox', 'studio_gallery_nonce');
    
    // 認定店かどうかをチェック
    $is_certified = get_field('is_certified_store', $post->ID) ?: false;
    
    // 保存された画像IDを取得
    $gallery_ids = get_post_meta($post->ID, '_gallery_image_ids', true);
    if (empty($gallery_ids)) {
        $gallery_ids = array();
    } else {
        $gallery_ids = explode(',', $gallery_ids);
    }
    
    ?>
    <div id="studio-gallery-container" class="<?php echo $is_certified ? 'certified-store' : 'non-certified-store'; ?>">
        <!-- 認定店状態の表示 -->
        <div id="certification-status" style="margin-bottom: 15px; padding: 10px; border-radius: 5px; <?php echo $is_certified ? 'background-color: #e7f5e7; border: 1px solid #4caf50; color: #2e7d32;' : 'background-color: #f5f5f5; border: 1px solid #ccc; color: #666;'; ?>">
            <?php if ($is_certified): ?>
                <strong>✓ 認定店</strong> - ギャラリー機能が利用できます
            <?php else: ?>
                <strong>⚠ 一般店</strong> - ギャラリー機能を利用するには「基本情報」タブで認定店として設定してください
            <?php endif; ?>
        </div>
        
        <div class="gallery-toolbar" style="margin-bottom: 20px;">
            <button type="button" class="button button-primary" id="add-gallery-images" <?php echo $is_certified ? '' : 'disabled'; ?>>
                画像を追加
            </button>
            <span id="gallery-count" style="margin-left: 15px; font-weight: 500;">
                現在の画像数: <span id="count-number"><?php echo count($gallery_ids); ?></span>枚
            </span>
        </div>
        
        <div id="gallery-images" class="gallery-images" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; min-height: 100px; border: 2px dashed #ddd; border-radius: 8px; padding: 20px; <?php echo $is_certified ? 'background: #fafafa;' : 'background: #f0f0f0; opacity: 0.6; pointer-events: none;'; ?>">
            <?php if (!empty($gallery_ids) && $gallery_ids[0] !== ''): ?>
                <?php foreach ($gallery_ids as $image_id): ?>
                    <?php if ($image_id): ?>
                        <div class="gallery-image-item" data-id="<?php echo esc_attr($image_id); ?>" style="position: relative; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: move;">
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail', false, array('style' => 'width: 100%; height: 120px; object-fit: cover;')); ?>
                            <button type="button" class="remove-image" style="position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px;">×</button>
                            <div style="padding: 8px; background: white; font-size: 11px; text-align: center; color: #666;">
                                ID: <?php echo $image_id; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-images-placeholder" style="grid-column: 1 / -1; text-align: center; color: #999; font-style: italic; padding: 40px 20px;">
                    <?php if ($is_certified): ?>
                        まだ画像が追加されていません。<br>
                        「画像を追加」ボタンをクリックして画像をアップロードしてください。
                    <?php else: ?>
                        ギャラリー機能は認定店のみ利用可能です。<br>
                        基本情報タブで「認定店として設定する」をONにしてください。
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <input type="hidden" id="gallery-image-ids" name="gallery_image_ids" value="<?php echo esc_attr(implode(',', $gallery_ids)); ?>" />
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var frame;
        
        // 認定店スイッチの変更を監視
        function updateGalleryState() {
            // ACFの認定店フィールドを監視
            var isCertified = false;
            
            // ACFのtrue_falseフィールドを取得
            var certifiedField = $('[data-name="is_certified_store"] input[type="checkbox"]');
            if (certifiedField.length) {
                isCertified = certifiedField.is(':checked');
            }
            
            var $container = $('#studio-gallery-container');
            var $statusDiv = $('#certification-status');
            var $addButton = $('#add-gallery-images');
            var $galleryImages = $('#gallery-images');
            var $placeholder = $('.no-images-placeholder');
            
            if (isCertified) {
                // 認定店の場合
                $container.removeClass('non-certified-store').addClass('certified-store');
                $statusDiv.html('<strong>✓ 認定店</strong> - ギャラリー機能が利用できます')
                    .css({
                        'background-color': '#e7f5e7',
                        'border': '1px solid #4caf50',
                        'color': '#2e7d32'
                    });
                $addButton.prop('disabled', false);
                $galleryImages.css({
                    'background': '#fafafa',
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                
                // プレースホルダーメッセージを更新
                if ($placeholder.length && $('#gallery-images .gallery-image-item').length === 0) {
                    $placeholder.html('まだ画像が追加されていません。<br>「画像を追加」ボタンをクリックして画像をアップロードしてください。');
                }
            } else {
                // 一般店の場合
                $container.removeClass('certified-store').addClass('non-certified-store');
                $statusDiv.html('<strong>⚠ 一般店</strong> - ギャラリー機能を利用するには「基本情報」タブで認定店として設定してください')
                    .css({
                        'background-color': '#f5f5f5',
                        'border': '1px solid #ccc',
                        'color': '#666'
                    });
                $addButton.prop('disabled', true);
                $galleryImages.css({
                    'background': '#f0f0f0',
                    'opacity': '0.6',
                    'pointer-events': 'none'
                });
                
                // プレースホルダーメッセージを更新
                if ($placeholder.length) {
                    $placeholder.html('ギャラリー機能は認定店のみ利用可能です。<br>基本情報タブで「認定店として設定する」をONにしてください。');
                }
                
                // 既存の画像がある場合もプレースホルダーを表示
                if ($('#gallery-images .gallery-image-item').length > 0 && $placeholder.length === 0) {
                    $('#gallery-images').append('<div class="no-images-placeholder" style="grid-column: 1 / -1; text-align: center; color: #999; font-style: italic; padding: 40px 20px;">ギャラリー機能は認定店のみ利用可能です。<br>基本情報タブで「認定店として設定する」をONにしてください。</div>');
                }
            }
        }
        
        // 初期状態をチェック
        updateGalleryState();
        
        // ACFフィールドの変更を監視（適切なタイミングで実行）
        setTimeout(function() {
            $(document).on('change', '[data-name="is_certified_store"] input[type="checkbox"]', function() {
                updateGalleryState();
            });
            
            // ACFのUI switcher変更も監視
            $(document).on('click', '[data-name="is_certified_store"] .acf-switch-slider', function() {
                setTimeout(updateGalleryState, 100);
            });
        }, 1000);
        
        // 画像追加ボタンのクリックイベント
        $('#add-gallery-images').on('click', function(e) {
            e.preventDefault();
            
            // メディアフレームが既に存在する場合は再利用
            if (frame) {
                frame.open();
                return;
            }
            
            // 新しいメディアフレームを作成
            frame = wp.media({
                title: 'ギャラリー画像を選択',
                button: {
                    text: '選択した画像を追加'
                },
                multiple: true // 複数選択を有効化
            });
            
            // 画像が選択された時の処理
            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var $container = $('#gallery-images');
                var $placeholder = $('.no-images-placeholder');
                
                // プレースホルダーを削除
                $placeholder.remove();
                
                // 各画像を追加
                attachments.forEach(function(attachment) {
                    // 既に存在する画像は追加しない
                    if ($container.find('[data-id="' + attachment.id + '"]').length > 0) {
                        return;
                    }
                    
                    var imageHtml = '<div class="gallery-image-item" data-id="' + attachment.id + '" style="position: relative; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: move;">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '" style="width: 100%; height: 120px; object-fit: cover;" alt="' + attachment.alt + '">' +
                        '<button type="button" class="remove-image" style="position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px;">×</button>' +
                        '<div style="padding: 8px; background: white; font-size: 11px; text-align: center; color: #666;">ID: ' + attachment.id + '</div>' +
                        '</div>';
                    
                    $container.append(imageHtml);
                });
                
                updateGalleryIds();
                updateImageCount();
            });
            
            // メディアフレームを開く
            frame.open();
        });
        
        // 画像削除ボタンのクリックイベント
        $(document).on('click', '.remove-image', function(e) {
            e.preventDefault();
            $(this).closest('.gallery-image-item').remove();
            updateGalleryIds();
            updateImageCount();
            
            // 画像がない場合はプレースホルダーを表示
            if ($('#gallery-images .gallery-image-item').length === 0) {
                $('#gallery-images').html('<div class="no-images-placeholder" style="grid-column: 1 / -1; text-align: center; color: #999; font-style: italic; padding: 40px 20px;">まだ画像が追加されていません。<br>「画像を追加」ボタンをクリックして画像をアップロードしてください。</div>');
            }
        });
        
        // ドラッグ&ドロップで順序変更（Sortable）
        $('#gallery-images').sortable({
            items: '.gallery-image-item',
            cursor: 'move',
            opacity: 0.8,
            placeholder: 'sortable-placeholder',
            update: function(event, ui) {
                updateGalleryIds();
            }
        });
        
        // ギャラリーIDを更新
        function updateGalleryIds() {
            var ids = [];
            $('#gallery-images .gallery-image-item').each(function() {
                ids.push($(this).data('id'));
            });
            $('#gallery-image-ids').val(ids.join(','));
        }
        
        // 画像数を更新
        function updateImageCount() {
            var count = $('#gallery-images .gallery-image-item').length;
            $('#count-number').text(count);
        }
    });
    </script>
    
    <style>
    .gallery-images .sortable-placeholder {
        border: 2px dashed #0073aa;
        background: rgba(0, 115, 170, 0.1);
        border-radius: 4px;
        margin: 0;
    }
    
    .gallery-image-item:hover {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    .remove-image:hover {
        background: rgba(255,0,0,1) !important;
    }
    
    /* 認定店状態のスタイル */
    .non-certified-store .gallery-images {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .non-certified-store .gallery-images::after {
        content: "認定店のみ利用可能";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.9);
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        color: #666;
        border: 1px solid #ddd;
        z-index: 10;
        pointer-events: none;
    }
    
    .certified-store .gallery-images {
        transition: all 0.3s ease;
    }
    
    /* ボタン状態 */
    #add-gallery-images:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f0f0f0 !important;
        border-color: #ddd !important;
        color: #999 !important;
    }
    
    /* 認定店状態表示 */
    #certification-status {
        transition: all 0.3s ease;
        font-size: 14px;
    }
    </style>
    <?php
}

// データを保存
add_action('save_post', 'save_studio_gallery_metabox');
function save_studio_gallery_metabox($post_id) {
    // 権限チェック
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Nonceチェック
    if (!isset($_POST['studio_gallery_nonce']) || !wp_verify_nonce($_POST['studio_gallery_nonce'], 'studio_gallery_metabox')) {
        return;
    }
    
    // 自動保存時はスキップ
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // 投稿タイプチェック
    if (get_post_type($post_id) !== 'studio_shops') {
        return;
    }
    
    // ギャラリー画像IDを保存
    if (isset($_POST['gallery_image_ids'])) {
        $gallery_ids = sanitize_text_field($_POST['gallery_image_ids']);
        update_post_meta($post_id, '_gallery_image_ids', $gallery_ids);
    }
}

// 管理画面にスクリプトとスタイルをエンキュー
add_action('admin_enqueue_scripts', 'enqueue_studio_gallery_scripts');
function enqueue_studio_gallery_scripts($hook) {
    global $post_type;
    
    if ($post_type === 'studio_shops' && ($hook === 'post.php' || $hook === 'post-new.php')) {
        // WordPress標準のメディアアップロードスクリプト
        wp_enqueue_media();
        
        // jQuery UI Sortable
        wp_enqueue_script('jquery-ui-sortable');
    }
}