<?php
/**
 * Template Name: Privacy Policy
 * プライバシーポリシーページ
 */

get_header(); ?>

<main class="main-content">
  <!-- パンくずリスト -->
  <?php get_template_part('template-parts/components/breadcrumb', null, [
      'items' => [
          ['text' => 'TOP', 'url' => home_url()],
          ['text' => 'プライバシーポリシー', 'url' => '']
      ]
  ]); ?>

  <div class="privacy-policy">
    <div class="privacy-policy__container">
      <!-- ページヘッダー -->
      <div class="privacy-policy__header">
        <h1 class="privacy-policy__title">プライバシーポリシー</h1>
        <p class="privacy-policy__date">制定日：2018年5月1日</p>
      </div>

      <!-- プライバシーポリシー内容 -->
      <div class="privacy-policy__content">

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">01. 関係法令・ガイドライン等の遵守</h2>
          <p class="privacy-policy__text">
            株式会社サンクリエーション（以下「当社」）は、個人情報保護法をはじめとする関係法令およびガイドライン等を遵守し、個人情報を適法かつ適正に取り扱います。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">02. 個人情報の取得</h2>
          <p class="privacy-policy__text">
            当社は、適法かつ適正な手段により個人情報を取得いたします。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">03. 個人情報の利用目的</h2>
          <p class="privacy-policy__text">
            当社は、個人情報を以下の範囲内で利用いたします。
          </p>
          <ol class="privacy-policy__list">
            <li>お客様からの問合せ、相談への対応のため</li>
            <li>当社サービスにおける商品発送、サービス提供のため</li>
            <li>代金請求、返金、支払い事務処理のため</li>
            <li>当社サービスサポートのため</li>
            <li>当社サービス案内、情報提供、アンケート調査のため</li>
            <li>肖像権使用同意書の範囲内での顔写真、年齢情報の利用のため</li>
          </ol>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">04. 個人データの委託</h2>
          <p class="privacy-policy__text">
            当社は、上記の利用目的の範囲内において、個人データの取扱いの全部または一部を協力会社に委託することがあります。この場合、委託先での個人データの取扱いについては、必要最小限の情報に限定して行います。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">05. 個人データの第三者提供</h2>
          <p class="privacy-policy__text">
            当社は、ご本人の事前の同意を得ることなく、または法令に認められた場合を除き、個人データを第三者に提供いたしません。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">06. 個人データの管理</h2>
          <p class="privacy-policy__text">
            当社は、個人データの不正アクセス、紛失、破壊、改ざん、漏洩等のリスクに対し、必要かつ適切な安全対策を実施いたします。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">07. 保有個人データに関する受付</h2>
          <p class="privacy-policy__text">
            当社は、保有個人データについて、開示、訂正、追加、削除、利用停止、消去および第三者提供の停止の権利に対応いたします。ただし、肖像権使用同意書に記載された内容が優先される場合があります。
          </p>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">08. 問合せ等及び苦情処理窓口</h2>
          <div class="privacy-policy__contact">
            <p class="privacy-policy__text">
              個人情報の取扱いに関するお問合せや苦情等については、下記までご連絡ください。
            </p>
            <div class="privacy-policy__contact-info">
              <p><strong>株式会社サンクリエーション</strong></p>
              <p>〒170-0002 東京都豊島区巣鴨4-22-26 1F</p>
              <p>お問合せ: <a href="https://san-creation.com/contact/" target="_blank" rel="noopener noreferrer">お問合せページ</a>よりご連絡ください</p>
            </div>
          </div>
        </section>

        <section class="privacy-policy__section">
          <h2 class="privacy-policy__section-title">09. プライバシーポリシーの改定</h2>
          <p class="privacy-policy__text">
            当社は、本プライバシーポリシーを随時改定することがあります。改定されたプライバシーポリシーは、当社ウェブサイトに掲載された時点から効力を生じるものとします。
          </p>
        </section>

        <div class="privacy-policy__footer">
          <p class="privacy-policy__company">株式会社サンクリエーション</p>
          <p class="privacy-policy__date">制定日：2018年5月1日</p>
        </div>

      </div>
    </div>
  </div>

</main>
<!-- Contact & Booking Section -->
<?php get_template_part('template-parts/components/contact-booking'); ?>
<?php
get_template_part('template-parts/components/footer');
get_footer();
?>