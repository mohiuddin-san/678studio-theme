<?php
/*
Template Name: Studio Reservation
*/
get_header(); ?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/reservation.css">
<div class="studio-container">
    <div class="studio-header">
        <nav><a href="#">Contact & Reservations</a></nav>
    </div>
    <div class="studio-content">
        <h2>お問い合わせ & ご予約</h2>
        <div class="action-buttons">
            <button class="btn-inquiry"><i class="fas fa-envelope"></i> お問い合わせ</button>
            <button class="btn-reserve"><i class="fas fa-calendar"></i> ご予約</button>
        </div>

        <div class="shop-selector">
            <select id="shop-dropdown" name="shop-dropdown">
                <?php
                $api_url = 'https://sugamo-navi.com/api/get_all_studio_shop.php';
                $response = wp_remote_get($api_url);
                if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                    $data = json_decode(wp_remote_retrieve_body($response), true);
                    $shops = $data['shops'];
                    foreach ($shops as $shop) {
                        echo '<option value="' . esc_attr($shop['id']) . '">' . esc_html($shop['name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <div class="studio-gallery" id="shop-gallery">
            <?php
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $shops = $data['shops'];
                $first_shop = reset($shops);
                $first_image = !empty($first_shop['image_urls']) ? $first_shop['image_urls'][0] : 'https://via.placeholder.com/300x200';
                echo '<img src="' . esc_url($first_image) . '" alt="' . esc_attr($first_shop['name']) . '" class="gallery-image">';
                echo '<div class="studio-info" id="shop-details">';
                echo '<table>';
                echo '<tr><th>項目名</th><th>詳細</th></tr>';
                echo '<tr><td>住所</td><td>' . esc_html($first_shop['address'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>電話</td><td>' . esc_html($first_shop['phone'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>最寄り駅</td><td>' . esc_html($first_shop['nearest_station'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>営業時間</td><td>' . esc_html($first_shop['business_hours'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td>定休日</td><td>' . esc_html($first_shop['holidays'] ?? 'N/A') . '</td></tr>';
                echo '</table>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="pricing-plans">
            <div class="plan-card">プラン1: 詳細...</div>
            <div class="plan-card">プラン2: 詳細...</div>
        </div>

        <div class="booking-container">
            <h1>オンラインチャットフォーム</h1>
            <form id="inquiry-form" method="post">
                <input type="hidden" id="shop-id" name="shop-id">
                <div class="form-group">
                    <label for="name">お名前 (必須)</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="furigana">フリガナ (必須)</label>
                    <input type="text" id="furigana" name="furigana" required>
                </div>
                <div class="form-group">
                    <label for="phone">電話番号 (必須)</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="user-email">メールアドレス (必須)</label>
                    <input type="email" id="user-email" name="user-email" required>
                </div>
                <div class="form-group">
                    <label for="gender">性別 (必須)</label>
                    <select id="gender" name="gender" required>
                        <option value="">選択してください</option>
                        <option value="male">男性</option>
                        <option value="female">女性</option>
                        <option value="other">その他</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="age">年齢 (必須)</label>
                    <input type="text" id="age" name="age" required>
                </div>
                <div class="form-group">
                    <label for="visit-purpose">ご来店目的 (必須)</label>
                    <input type="text" id="visit-purpose" name="visit-purpose" required>
                </div>
                <div class="form-group">
                    <label for="preferred-date">ご希望日 (必須)</label>
                    <input type="date" id="preferred-date" name="preferred-date" required>
                </div>
                <div class="form-group">
                    <label for="preferred-time">ご希望時間 (必須)</label>
                    <input type="time" id="preferred-time" name="preferred-time" required>
                </div>
                <div class="form-group">
                    <label for="message">ご質問・ご要望</label>
                    <textarea id="message" name="message" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="privacy">個人情報の取り扱いについて (必須)</label>
                    <input type="checkbox" id="privacy" name="privacy" required> 個人情報の取り扱いについて同意する
                </div>
                <button type="submit" class="submit-btn">送信</button>
            </form>
        </div>

        <div class="studio-footer">
            <p>ご予約</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('shop-dropdown');
    const gallery = document.getElementById('shop-gallery');
    const details = document.getElementById('shop-details');

    dropdown.addEventListener('change', function() {
        const shopId = this.value;
        fetch('https://sugamo-navi.com/api/get_all_studio_shop.php')
            .then(response => response.json())
            .then(data => {
                const shop = data.shops.find(s => s.id == shopId);
                if (shop) {
                    gallery.innerHTML = '';
                    const first_image = shop.image_urls && shop.image_urls.length ? shop.image_urls[0] : 'https://via.placeholder.com/300x200';
                    gallery.innerHTML = `<img src="${first_image}" alt="${shop.name}" class="gallery-image"><div class="studio-info" id="shop-details"><table><tr><th>項目名</th><th>詳細</th></tr><tr><td>住所</td><td>${shop.address || 'N/A'}</td></tr><tr><td>電話</td><td>${shop.phone || 'N/A'}</td></tr><tr><td>最寄り駅</td><td>${shop.nearest_station || 'N/A'}</td></tr><tr><td>営業時間</td><td>${shop.business_hours || 'N/A'}</td></tr><tr><td>定休日</td><td>${shop.holidays || 'N/A'}</td></tr></table></div>`;
                    document.getElementById('shop-id').value = shopId;
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    });
});
</script>
<?php get_footer(); ?>