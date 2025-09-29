<?php
/**
 * Store Search Header Section
 * 店舗検索ヘッダーセクション
 */
?>

<section class="store-search-header">
    <div class="store-search-header__container">
        <!-- タイトル部分 -->
        <div class="store-search-header__title-section">
            <!-- アイテム1: 左側の波線装飾 -->
            <div class="store-search-header__wave-left">
                <!-- PC用波線 -->
                <img class="pc-only" src="<?php echo get_template_directory_uri(); ?>/assets/images/search-wave.svg" alt="" />
                <!-- SP用波線 -->
                <img class="sp-only" src="<?php echo get_template_directory_uri(); ?>/assets/images/search-wave-sp.svg" alt="" />
            </div>

            <!-- アイテム2: タイトルとサブタイトル -->
            <div class="store-search-header__text-content">
                <h1 class="store-search-header__title">
                    お近くの写真館を探す
                </h1>
                <p class="store-search-header__subtitle">
                    全国の写真館で<br class="sp-only">ロクナナハチ撮影が受けられます
                </p>
            </div>

            <!-- アイテム3: 右側の波線装飾 -->
            <div class="store-search-header__wave-right">
                <!-- PC用波線 -->
                <img class="pc-only" src="<?php echo get_template_directory_uri(); ?>/assets/images/search-wave.svg" alt="" />
                <!-- SP用波線 -->
                <img class="sp-only" src="<?php echo get_template_directory_uri(); ?>/assets/images/search-wave-sp.svg" alt="" />
            </div>
        </div>

        <!-- 検索フォーム -->
        <div class="store-search-header__search-wrapper">
            <form class="store-search-header__search-form" action="<?php echo esc_url(get_permalink()); ?>" method="get">
                <div class="store-search-header__search-input-wrapper">
                    <button type="button" class="store-search-header__search-icon" aria-label="検索">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.375 2.4375C6.43896 2.4375 2.4375 6.43896 2.4375 11.375C2.4375 16.311 6.43896 20.3125 11.375 20.3125C13.8434 20.3125 16.0761 19.3135 17.6948 17.6948C19.3135 16.0761 20.3125 13.8434 20.3125 11.375C20.3125 6.43896 16.311 2.4375 11.375 2.4375ZM0 11.375C0 5.09276 5.09276 0 11.375 0C17.6572 0 22.75 5.09276 22.75 11.375C22.75 14.0766 21.807 16.5597 20.2341 18.5105L25.643 23.9195C26.119 24.3954 26.119 25.1671 25.643 25.643C25.1671 26.119 24.3954 26.119 23.9195 25.643L18.5105 20.2341C16.5597 21.807 14.0766 22.75 11.375 22.75C5.09276 22.75 0 17.6572 0 11.375Z" fill="#3F3F3F"/>
                        </svg>
                    </button>
                    <input
                        type="text"
                        class="store-search-header__search-input"
                        placeholder="フリーワードで探す"
                        name="search"
                        id="store-search-keyword"
                        value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>"
                    >
                </div>

                <!-- 都道府県プルダウン -->
                <div class="store-search-header__prefecture-wrapper">
                    <select class="store-search-header__prefecture-select" name="prefecture" id="store-search-prefecture">
<?php $selected_prefecture = isset($_GET['prefecture']) ? $_GET['prefecture'] : ''; ?>
                <option value="">都道府県で探す</option>
                <optgroup label="北海道・東北">
                    <option value="北海道" <?php selected($selected_prefecture, '北海道'); ?>>北海道</option>
                    <option value="青森県" <?php selected($selected_prefecture, '青森県'); ?>>青森県</option>
                    <option value="岩手県" <?php selected($selected_prefecture, '岩手県'); ?>>岩手県</option>
                    <option value="宮城県" <?php selected($selected_prefecture, '宮城県'); ?>>宮城県</option>
                    <option value="秋田県" <?php selected($selected_prefecture, '秋田県'); ?>>秋田県</option>
                    <option value="山形県" <?php selected($selected_prefecture, '山形県'); ?>>山形県</option>
                    <option value="福島県" <?php selected($selected_prefecture, '福島県'); ?>>福島県</option>
                </optgroup>
                <optgroup label="関東">
                    <option value="茨城県" <?php selected($selected_prefecture, '茨城県'); ?>>茨城県</option>
                    <option value="栃木県" <?php selected($selected_prefecture, '栃木県'); ?>>栃木県</option>
                    <option value="群馬県" <?php selected($selected_prefecture, '群馬県'); ?>>群馬県</option>
                    <option value="埼玉県" <?php selected($selected_prefecture, '埼玉県'); ?>>埼玉県</option>
                    <option value="千葉県" <?php selected($selected_prefecture, '千葉県'); ?>>千葉県</option>
                    <option value="東京都" <?php selected($selected_prefecture, '東京都'); ?>>東京都</option>
                    <option value="神奈川県" <?php selected($selected_prefecture, '神奈川県'); ?>>神奈川県</option>
                </optgroup>
                <optgroup label="中部">
                    <option value="新潟県" <?php selected($selected_prefecture, '新潟県'); ?>>新潟県</option>
                    <option value="富山県" <?php selected($selected_prefecture, '富山県'); ?>>富山県</option>
                    <option value="石川県" <?php selected($selected_prefecture, '石川県'); ?>>石川県</option>
                    <option value="福井県" <?php selected($selected_prefecture, '福井県'); ?>>福井県</option>
                    <option value="山梨県" <?php selected($selected_prefecture, '山梨県'); ?>>山梨県</option>
                    <option value="長野県" <?php selected($selected_prefecture, '長野県'); ?>>長野県</option>
                    <option value="岐阜県" <?php selected($selected_prefecture, '岐阜県'); ?>>岐阜県</option>
                    <option value="静岡県" <?php selected($selected_prefecture, '静岡県'); ?>>静岡県</option>
                    <option value="愛知県" <?php selected($selected_prefecture, '愛知県'); ?>>愛知県</option>
                </optgroup>
                <optgroup label="近畿">
                    <option value="三重県" <?php selected($selected_prefecture, '三重県'); ?>>三重県</option>
                    <option value="滋賀県" <?php selected($selected_prefecture, '滋賀県'); ?>>滋賀県</option>
                    <option value="京都府" <?php selected($selected_prefecture, '京都府'); ?>>京都府</option>
                    <option value="大阪府" <?php selected($selected_prefecture, '大阪府'); ?>>大阪府</option>
                    <option value="兵庫県" <?php selected($selected_prefecture, '兵庫県'); ?>>兵庫県</option>
                    <option value="奈良県" <?php selected($selected_prefecture, '奈良県'); ?>>奈良県</option>
                    <option value="和歌山県" <?php selected($selected_prefecture, '和歌山県'); ?>>和歌山県</option>
                </optgroup>
                <optgroup label="中国">
                    <option value="鳥取県" <?php selected($selected_prefecture, '鳥取県'); ?>>鳥取県</option>
                    <option value="島根県" <?php selected($selected_prefecture, '島根県'); ?>>島根県</option>
                    <option value="岡山県" <?php selected($selected_prefecture, '岡山県'); ?>>岡山県</option>
                    <option value="広島県" <?php selected($selected_prefecture, '広島県'); ?>>広島県</option>
                    <option value="山口県" <?php selected($selected_prefecture, '山口県'); ?>>山口県</option>
                </optgroup>
                <optgroup label="四国">
                    <option value="徳島県" <?php selected($selected_prefecture, '徳島県'); ?>>徳島県</option>
                    <option value="香川県" <?php selected($selected_prefecture, '香川県'); ?>>香川県</option>
                    <option value="愛媛県" <?php selected($selected_prefecture, '愛媛県'); ?>>愛媛県</option>
                    <option value="高知県" <?php selected($selected_prefecture, '高知県'); ?>>高知県</option>
                </optgroup>
                <optgroup label="九州・沖縄">
                    <option value="福岡県" <?php selected($selected_prefecture, '福岡県'); ?>>福岡県</option>
                    <option value="佐賀県" <?php selected($selected_prefecture, '佐賀県'); ?>>佐賀県</option>
                    <option value="長崎県" <?php selected($selected_prefecture, '長崎県'); ?>>長崎県</option>
                    <option value="熊本県" <?php selected($selected_prefecture, '熊本県'); ?>>熊本県</option>
                    <option value="大分県" <?php selected($selected_prefecture, '大分県'); ?>>大分県</option>
                    <option value="宮崎県" <?php selected($selected_prefecture, '宮崎県'); ?>>宮崎県</option>
                    <option value="鹿児島県" <?php selected($selected_prefecture, '鹿児島県'); ?>>鹿児島県</option>
                    <option value="沖縄県" <?php selected($selected_prefecture, '沖縄県'); ?>>沖縄県</option>
                </optgroup>
            </select>
            <div class="store-search-header__prefecture-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="6" height="12" viewBox="0 0 6 12" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.21967 7.96967C0.512563 7.67678 0.987437 7.67678 1.28033 7.96967L3 9.68934L4.71967 7.96967C5.01256 7.67678 5.48744 7.67678 5.78033 7.96967C6.07322 8.26256 6.07322 8.73744 5.78033 9.03033L3.53033 11.2803C3.23744 11.5732 2.76256 11.5732 2.46967 11.2803L0.21967 9.03033C-0.0732233 8.73744 -0.0732233 8.26256 0.21967 7.96967Z" fill="#0F172A"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.78033 3.53033C5.48744 3.82322 5.01256 3.82322 4.71967 3.53033L3 1.81066L1.28033 3.53033C0.987437 3.82322 0.512563 3.82322 0.21967 3.53033C-0.0732231 3.23744 -0.0732231 2.76256 0.21967 2.46967L2.46967 0.21967C2.76256 -0.0732233 3.23744 -0.0732233 3.53033 0.21967L5.78033 2.46967C6.07322 2.76256 6.07322 3.23744 5.78033 3.53033Z" fill="#0F172A"/>
                </svg>
                    </div>
                </div>

                <!-- 検索ボタン -->
                <div class="store-search-header__button-wrapper">
                    <button type="submit" class="store-search-header__search-button">
                        写真館を検索
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>