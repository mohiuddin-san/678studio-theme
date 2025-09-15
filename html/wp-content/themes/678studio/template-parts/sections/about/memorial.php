<?php
/**
 * About Memorial Section - supportの180度対称レイアウト
 * PC: 複雑なグリッドレイアウト（supportと180度対称）
 * SP: シンプルなflexbox縦並び
 */
?>

<section class="about-memorial">
    <!-- PC Version -->
    <div class="about-memorial__grid pc">
        <div class="about-memorial__background-gray gitem" data-grid="1, 19, 1, 8">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/memorial_flower.svg" alt="Decorative flower" class="about-memorial__flower">
        </div>
        <div class="about-memorial__photos gitem" data-grid="9, 19, 2, 12">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/memorial_photo.jpg" alt="Memorial photography">
        </div>
        <div class="about-memorial__content gitem" data-grid="1, 9, 4, 14">
            <div class="about-memorial__text-wrapper">
                <h2 class="about-memorial__title">
                    思い出を残すための
                    <br>特別な撮影
                </h2>
                <div class="about-memorial__description">
                    <p>
                        人生の大切な瞬間を美しく残すメモリアル撮影。
                        還暦、喜寿、米寿などの節目のお祝いから、
                        遺影撮影まで、プロの技術で心に残る一枚をお撮りいたします。
                    </p>
                    <p>
                        ご家族の絆を深める撮影体験として、
                        皆様に愛され続けています。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- SP Version -->
    <div class="about-memorial__container sp">
        <div class="about-memorial__text-section">
            <h2 class="about-memorial__title">
                思い出を残すための
                <br>特別な撮影
            </h2>
            <div class="about-memorial__description">
                <p>
                    人生の大切な瞬間を美しく残すメモリアル撮影。
                    還暦、喜寿、米寿などの節目のお祝いから、
                    遺影撮影まで、プロの技術で心に残る一枚をお撮りいたします。
                </p>
                <p>
                    ご家族の絆を深める撮影体験として、
                    皆様に愛され続けています。
                </p>
            </div>
        </div>
        <div class="about-memorial__image-section">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/memorial_photo.jpg" alt="Memorial photography">
        </div>
    </div>
</section>