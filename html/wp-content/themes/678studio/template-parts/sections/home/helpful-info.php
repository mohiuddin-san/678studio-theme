<?php
/**
 * Helpful Information Section with Layered Design
 */
?>

<section class="helpful-info-section">
  <div class="helpful-info-container">

    <!-- Layer 1: Background -->
    <div class="helpful-info__background"></div>

    <!-- Layer 2: Left Illustration -->
    <div class="helpful-info__illustration-left">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/helpful-2.svg" alt="左側装飾イラスト">
    </div>

    <!-- Layer 3: Right Illustration -->
    <div class="helpful-info__illustration-right">
      <img src="<?php echo get_template_directory_uri(); ?>/assets/images/helpful-1.svg" alt="右側装飾イラスト">
    </div>

    <!-- Layer 4: Content -->
    <div class="helpful-info__content">
      <div class="helpful-info__header">
        <h2 class="helpful-info__title">お役立ち情報</h2>
        <div class="helpful-info__description">
          写真館を利用する際に役立つ情報をまとめました<br class="pc-only">
          写真館の選び方から、撮影時に気を付けることまで、<br class="pc-only">
          ぜひ参考になさってください
        </div>
      </div>

      <div class="helpful-info__articles">
        <?php
        // お役立ち情報のカスタム投稿を取得
        $helpful_info_args = array(
          'post_type' => 'seo_articles', // カスタム投稿タイプ名
          'posts_per_page' => 3, // 表示する記事数
          'orderby' => 'date',
          'order' => 'DESC',
          'post_status' => 'publish'
        );

        $helpful_info_query = new WP_Query($helpful_info_args);

        if ($helpful_info_query->have_posts()) :
          while ($helpful_info_query->have_posts()) : $helpful_info_query->the_post();

            // カテゴリー取得（カスタムタクソノミーの場合は調整が必要）
            $categories = get_the_terms(get_the_ID(), 'article_category');
            $category_name = '';
            if ($categories && !is_wp_error($categories)) {
              $category_name = $categories[0]->name;
            }

            // 日付取得
            $post_date = get_the_date('Y年n月j日');

            // タイトル取得（文字数制限付き）
            $title = get_the_title();
            if (mb_strlen($title) > 30) {
              $title = mb_substr($title, 0, 30) . '・・';
            }
        ?>
        <a href="<?php the_permalink(); ?>" class="helpful-info__article">
          <?php if ($category_name) : ?>
          <div class="helpful-info__article-tag"><?php echo esc_html($category_name); ?></div>
          <?php endif; ?>
          <div class="helpful-info__article-date"><?php echo esc_html($post_date); ?></div>
          <div class="helpful-info__article-title"><?php echo esc_html($title); ?></div>
        </a>
        <?php
          endwhile;
          wp_reset_postdata();
        else :
          // 記事がない場合のダミーデータ
        ?>
        <div class="helpful-info__article">
          <div class="helpful-info__article-tag">カテゴリー</div>
          <div class="helpful-info__article-date">2024年11月28日</div>
          <div class="helpful-info__article-title">お役立ち情報の記事がまだ投稿されていません。</div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>