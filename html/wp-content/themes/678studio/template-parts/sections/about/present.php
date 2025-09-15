<?php
/**
 * About Present Section - supportと同じ構造
 * PC: 複雑なグリッドレイアウト（supportと同じ配置）
 * SP: シンプルなflexbox縦並び
 */
?>

<section class="about-present">
    <!-- PC Version -->
    <div class="about-present__grid pc">
        <div class="about-present__background-gray gitem" data-grid="13, 21, 1, 8"></div>
        <div class="about-present__photos gitem" data-grid="1, 13, 2, 12">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-present-pic.jpg" alt="Present photography">
        </div>
        <div class="about-present__content gitem" data-grid="11, 21, 4, 14">
            <div class="about-present__text-wrapper">
                <h2 class="about-present__title">
                    贈り物として選ばれる
                    <br>特別な記念写真
                </h2>
                <div class="about-present__description">
                    <p>
                        ご家族への贈り物として人気の記念撮影。
                        お誕生日、記念日、長寿のお祝いなど、
                        大切な方への心のこもったプレゼントとして
                        多くの方に選ばれています。
                    </p>
                    <p>
                        美しく仕上げられた写真は、
                        きっと喜ばれる特別な贈り物になります。
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- SP Version -->
    <div class="about-present__container sp">
        <div class="about-present__text-section">
            <h2 class="about-present__title">
                贈り物として選ばれる
                <br>特別な記念写真
            </h2>
            <div class="about-present__description">
                <p>
                    ご家族への贈り物として人気の記念撮影。
                    お誕生日、記念日、長寿のお祝いなど、
                    大切な方への心のこもったプレゼントとして
                    多くの方に選ばれています。
                </p>
                <p>
                    美しく仕上げられた写真は、
                    きっと喜ばれる特別な贈り物になります。
                </p>
            </div>
        </div>
        <div class="about-present__image-section">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/about-present-pic.jpg" alt="Present photography">
        </div>
    </div>
</section>