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
        <h2 class="media-slider-section__heading">メディア掲載実績</h2>
        
        <div class="media-slider-section__wrapper">
            <!-- Navigation buttons -->
            <button class="media-slider-section__nav media-slider-section__nav--prev" aria-label="前へ">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <button class="media-slider-section__nav media-slider-section__nav--next" aria-label="次へ">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <!-- Slider container -->
            <div class="media-slider-section__slider">
                <div class="media-slider-section__track">
                    <?php while ($media_query->have_posts()) : $media_query->the_post(); 
                        $media_image = get_field('media_image');
                        $media_subtitle = get_field('media_subtitle');
                    ?>
                        <div class="media-slider-section__item">
                            <?php if ($media_image) : ?>
                                <div class="media-slider-section__image">
                                    <img src="<?php echo esc_url($media_image['sizes']['large']); ?>" 
                                         alt="<?php echo esc_attr($media_image['alt']); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="media-slider-section__content">
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
        
        <!-- Dots indicator -->
        <div class="media-slider-section__dots">
            <?php 
            $slide_count = $media_query->post_count;
            for ($i = 0; $i < $slide_count; $i++) : ?>
                <button class="media-slider-section__dot<?php echo $i === 0 ? ' media-slider-section__dot--active' : ''; ?>" 
                        data-slide="<?php echo $i; ?>" 
                        aria-label="スライド <?php echo $i + 1; ?>へ移動"></button>
            <?php endfor; ?>
        </div>
    </div>
</section>

<?php endif;
wp_reset_postdata(); ?>