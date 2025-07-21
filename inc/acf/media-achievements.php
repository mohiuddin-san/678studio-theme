<?php
/**
 * ACF Field Group for Media Achievements
 */

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
    'key' => 'group_media_achievements',
    'title' => 'メディア実績情報',
    'fields' => array(
        array(
            'key' => 'field_media_image',
            'label' => 'メディア画像',
            'name' => 'media_image',
            'type' => 'image',
            'instructions' => 'メディアの実績画像をアップロードしてください',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => 'jpg,jpeg,png,gif,webp',
        ),
        array(
            'key' => 'field_media_subtitle',
            'label' => 'サブタイトル',
            'name' => 'media_subtitle',
            'type' => 'text',
            'instructions' => 'メディアのサブタイトル（任意）',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ),
        array(
            'key' => 'field_display_order',
            'label' => '表示順',
            'name' => 'display_order',
            'type' => 'number',
            'instructions' => '数値が小さいほど前に表示されます',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '50',
                'class' => '',
                'id' => '',
            ),
            'default_value' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => '',
            'max' => '',
            'step' => 1,
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'media_achievements',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
));

endif;