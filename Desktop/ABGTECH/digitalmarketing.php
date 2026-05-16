<?php require __DIR__ . '/includes/site.php'; abg_head('Digital Marketing | ABGTECH', 'ABGTECH digital marketing for SEO, PPC, social media, email, content, SMS, dashboards, and growth reporting.'); abg_nav('services'); ?>
<header class="page-hero" style="background:radial-gradient(circle at 80% 15%, rgba(32,201,151,.24), transparent 24rem),linear-gradient(135deg,#06142e,#123b94 55%,#0d8a78);">
  <div class="container hero-content">
    <div class="breadcrumb-soft"><a href="index.php">Homepage</a><i class="bi bi-chevron-right"></i><span>Services Page</span></div>
    <div class="row align-items-center g-5">
      <div class="col-lg-7 reveal"><span class="eyebrow"><i class="bi bi-graph-up-arrow"></i> Digital Marketing</span><h1 class="display-title mt-4 mb-4">Campaigns built for visibility, leads, and measurable growth.</h1><p class="lead-copy">ABGTECH plans, runs, and reports digital campaigns across search, social, email, content, influencer, and SMS channels.</p></div>
      <div class="col-lg-5 reveal"><div class="hero-mock"><div class="mock-window"><div class="mock-top"><span></span><span></span><span></span></div><div class="p-4"><small class="text-muted">Results teaser</small><h2 class="display-5 fw-bold text-primary">3.2x ROI</h2><p class="section-copy mb-0">Average fictional client return shown for preview dashboards.</p></div></div></div></div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal" style="max-width:850px;"><div class="section-kicker">Channels We Cover</div><h2 class="section-title">A practical channel mix for every campaign stage.</h2></div>
    <div class="row g-4">
      <?php foreach ([['bi-search','SEO','Technical SEO, content clusters, local search, and monthly rankings.'],['bi-badge-ad','Google Ads PPC','Search campaigns, landing pages, retargeting, and ROI checks.'],['bi-share','Social Media','Meta, Instagram, LinkedIn, TikTok content and paid social campaigns.'],['bi-envelope-paper','Email Marketing','Lifecycle campaigns, segmentation, newsletters, and lead nurturing.'],['bi-journal-richtext','Content Marketing','Website copy, blogs, lead magnets, and campaign storytelling.'],['bi-person-hearts','Influencer Marketing','Creator selection, campaign briefs, approvals, and performance tracking.'],['bi-chat-left-text','SMS Campaigns','Customer reminders, offers, launches, and conversion follow-ups.']] as $item): ?>
      <div class="col-md-6 col-lg-4 reveal"><div class="premium-card p-4 h-100"><div class="icon-box mb-3"><i class="bi <?= $item[0] ?>"></i></div><h3 class="h4 fw-bold"><?= $item[1] ?></h3><p class="section-copy mb-0"><?= $item[2] ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-dark">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 reveal"><div class="section-kicker">Sample Reporting Dashboard</div><h2 class="section-title">ABGTECH Analytics makes campaign results visible.</h2><p class="section-copy">Track impressions, CTR, conversions, ROAS, email open rate, and weekly trends in one reporting view.</p></div>
      <div class="col-lg-7 reveal">
        <div class="dashboard-mock text-dark">
          <small class="text-muted fw-bold">Sample Client Dashboard - ABGTECH Analytics</small>
          <div class="row g-3 mt-1">
            <?php foreach ([['Impressions','482K','+18%','green'],['CTR','6.8%','+2.1%','green'],['Conversions','1,284','+31%','green'],['ROAS','4.1x','-0.3%','red']] as $m): ?>
            <div class="col-6 col-lg-3"><div class="metric-tile"><small><?= $m[0] ?></small><h3 class="fw-bold"><?= $m[1] ?></h3><span class="badge-soft <?= $m[3]==='green'?'badge-green':'badge-red' ?>"><?= $m[2] ?></span></div></div>
            <?php endforeach; ?>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-lg-6"><div class="bar-chart"><span data-height="42%"></span><span data-height="66%"></span><span data-height="58%"></span><span data-height="80%"></span><span data-height="72%"></span><span data-height="92%"></span><span data-height="68%"></span></div></div>
            <div class="col-lg-6"><div class="metric-tile h-100"><svg viewBox="0 0 320 180" width="100%" height="180" role="img" aria-label="30-day trend line"><polyline points="10,140 50,118 90,130 130,86 170,96 210,58 250,76 310,38" fill="none" stroke="#1f55c7" stroke-width="8" stroke-linecap="round"/><polyline points="10,156 50,144 90,148 130,120 170,116 210,94 250,102 310,72" fill="none" stroke="#59d7f1" stroke-width="6" stroke-linecap="round"/></svg></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 reveal"><div class="section-kicker">What You Get</div><h2 class="section-title">Execution plus reporting discipline.</h2><ul class="section-copy fs-5"><li>Monthly reports with KPIs and next actions</li><li>Dedicated account manager</li><li>Weekly check-ins during active campaigns</li><li>A/B testing for ads, landing pages, and messaging</li><li>Campaign audits and channel recommendations</li></ul></div>
      <div class="col-lg-6 reveal"><div class="cycle"><span>Plan</span><span>Execute</span><span>Measure</span><span>Optimise</span><strong class="h3 text-primary">Growth Cycle</strong></div></div>
    </div>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal" style="max-width:850px;"><div class="section-kicker">Past Results</div><h2 class="section-title">Fictional case studies for page preview.</h2></div>
    <div class="row g-4">
      <?php foreach ([['E-commerce client, Kampala','+180% organic traffic','SEO','12K to 34K visits'],['Professional services','3.8x lead ROI','Google Ads','42 to 163 leads'],['Retail brand','+64% repeat engagement','Email + SMS','18% to 42% opens']] as $case): ?>
      <div class="col-md-4 reveal"><div class="premium-card p-4 h-100"><small class="text-muted"><?= $case[0] ?></small><h3 class="h2 fw-bold text-primary mt-2"><?= $case[1] ?></h3><p class="section-copy">Channel used: <strong><?= $case[2] ?></strong></p><span class="badge-soft badge-green"><?= $case[3] ?></span></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section pt-0"><div class="container"><div class="cta-banner reveal d-flex flex-column flex-lg-row gap-4 align-items-lg-center justify-content-between"><div><h2 class="section-title mb-2">Ready to grow your digital presence?</h2><p class="text-white-50 mb-0">Let us plan a measurable campaign for your business.</p></div><a class="btn btn-light btn-lg fw-bold" href="contact.php">Contact Us</a></div></div></section>
<?php abg_footer(); ?>
