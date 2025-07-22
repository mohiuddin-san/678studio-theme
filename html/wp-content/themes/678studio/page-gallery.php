<?php
/**
 * Template Name: Gallery Page
 * Description: A page template for displaying image galleries with FTP integration
 */

get_header(); ?>

<div class="gallery-container">
    <?php
    $ftp = @ftp_connect(GALLERY_FTP_HOST);
    $images_by_category = [];
    
    if ($ftp && @ftp_login($ftp, GALLERY_FTP_USER, GALLERY_FTP_PASS)) {
        ftp_pasv($ftp, true);

        $categories = @ftp_nlist($ftp, GALLERY_BASE_PATH);
        
        if ($categories) {
            foreach ($categories as $category_path) {
                $category = basename($category_path);
                if (in_array($category, ['.', '..'])) continue;

                $files = @ftp_nlist($ftp, $category_path);
                
                if ($files && is_array($files)) {
                    $images = [];
                    foreach ($files as $file) {
                        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                            $image_name = basename($file);
                            $img_url = GALLERY_BASE_URL . $category . '/' . $image_name;
                            $images[] = ['url' => $img_url, 'name' => $image_name];
                        }
                    }
                    if (!empty($images)) {
                        $images_by_category[$category] = $images;
                    }
                }
            }
        }
        ftp_close($ftp);
    } else {
        ?>
        <div class="gallery-error">
            <p><?php _e('FTP connection failed. Please check your connection settings.', '678studio'); ?></p>
            <p><small><?php echo date('Y-m-d H:i:s'); ?></small></p>
        </div>
        <?php
    }
    ?>

    <?php if (empty($images_by_category)): ?>
        <div class="gallery-error">
            <p><?php _e('No gallery images available at the moment.', '678studio'); ?></p>
            <p><small><?php _e('Please check back later or contact the administrator.', '678studio'); ?></small></p>
        </div>
        
        <?php
        // Fallback: Show images from WordPress Media Library
        $media_images = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($media_images): ?>
            <div class="gallery-fallback">
                <h3><?php _e('Recent Images', '678studio'); ?></h3>
                <div class="gallery-images">
                    <div class="image-grid">
                        <?php foreach ($media_images as $image): 
                            $img_url = wp_get_attachment_image_url($image->ID, 'large');
                            $img_thumb = wp_get_attachment_image_url($image->ID, 'medium');
                            ?>
                            <div class="gallery-item">
                                <a href="<?php echo esc_url($img_url); ?>" target="_blank">
                                    <img src="<?php echo esc_url($img_thumb); ?>" 
                                         alt="<?php echo esc_attr($image->post_title); ?>" 
                                         loading="lazy">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="gallery-controls">
            <button class="category-btn active" data-category="all"><?php _e('All', '678studio'); ?></button>
            <?php foreach (array_keys($images_by_category) as $category): ?>
                <button class="category-btn" data-category="<?php echo esc_attr($category); ?>">
                    <?php echo esc_html($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="gallery-images">
            <?php foreach ($images_by_category as $category => $images): ?>
                <div class="image-category" data-category="<?php echo esc_attr($category); ?>">
                    <h3 class="category-title"><?php echo esc_html($category); ?></h3>
                    <div class="image-grid">
                        <?php foreach ($images as $image): ?>
                            <div class="gallery-item">
                                <img src="<?php echo esc_url($image['url']); ?>" 
                                     alt="<?php echo esc_attr($image['name']); ?>" 
                                     loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.category-btn');
    const categories = document.querySelectorAll('.image-category');

    if (!buttons.length || !categories.length) return;

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            // Hide all categories
            categories.forEach(cat => cat.style.display = 'none');

            // Show the selected category or all
            const selectedCategory = this.getAttribute('data-category');
            if (selectedCategory === 'all') {
                categories.forEach(cat => cat.style.display = 'block');
            } else {
                const targetCategory = document.querySelector(`.image-category[data-category="${selectedCategory}"]`);
                if (targetCategory) {
                    targetCategory.style.display = 'block';
                }
            }
        });
    });

    // Trigger "All" button click on load
    const allButton = document.querySelector('.category-btn[data-category="all"]');
    if (allButton) {
        allButton.click();
    }
});
</script>