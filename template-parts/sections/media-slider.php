<?php
/**
 * Template part for displaying media slider section
 */

// Query media achievements
$args = array(
    'post_type' => 'media_achievements',
    'posts_per_page' => -1,
    'meta_key' => 'display_order',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'post_status' => 'publish'
);

$media_query = new WP_Query($args);

if ($media_query->have_posts()) : ?>

<section class="media-slider-section">
  <div class="media-slider-section__container">
    <!-- Auto-scroll slider container -->
    <div class="media-slider-section__slider">
      <div class="media-slider-section__track">
        <?php while ($media_query->have_posts()) : $media_query->the_post(); 
                      $media_image = get_field('media_image');
                      $media_subtitle = get_field('media_subtitle');
                  ?>
        <div class="media-slider-section__item">
          <!-- 画像ボックス -->
          <div class="media-slider-section__image-box">
            <?php if ($media_image) : ?>
            <img src="<?php echo esc_url($media_image['sizes']['large']); ?>"
              alt="<?php echo esc_attr($media_image['alt']); ?>">
            <?php endif; ?>
          </div>
          
          <!-- テキストボックス -->
          <div class="media-slider-section__text-box">
            <h3 class="media-slider-section__title"><?php the_title(); ?></h3>
            <?php if ($media_subtitle) : ?>
            <p class="media-slider-section__subtitle"><?php echo esc_html($media_subtitle); ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</section>

<?php endif;
wp_reset_postdata(); ?>