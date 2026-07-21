<?php
require_once __DIR__ . '/db.php';
$pageTitle = $pageTitle ?? SITE_NAME . ' — Discover the Best AI Companion Apps';
$pageDesc  = $pageDesc  ?? 'Compare AI companions, virtual girlfriends, roleplay chatbots and character generators in one place.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-M8QMBPSR');</script>
<!-- End Google Tag Manager -->

<!--MICROSOFT CLARITY RECORDS START-->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "xpubx4i3ks");
</script>
<!--MICROSOFT CLARITY RECORDS END-->

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="<?= esc($pageDesc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/style.css?v=<?= filemtime(__DIR__ . '/../assets/style.css') ?>">
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M8QMBPSR"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?= SITE_URL ?>/index.php">
      <span class="logo-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 21s-7.5-4.6-9.7-9.2C.7 8.4 2.6 5 6 5c2 0 3.4 1 4.3 2.4h3.4C14.6 6 16 5 18 5c3.4 0 5.3 3.4 3.7 6.8C19.5 16.4 12 21 12 21Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
      </span>
      Companion<span>Verse</span>
    </a>
    <nav class="main-nav">
      <a href="<?= SITE_URL ?>/index.php#categories">Categories</a>
      <a href="<?= SITE_URL ?>/index.php#featured">Featured</a>
      <a href="<?= SITE_URL ?>/index.php#latest">Latest</a>
      <a href="<?= SITE_URL ?>/index.php#faq">FAQ</a>
    </nav>
    <a class="btn btn-ghost btn-sm" href="<?= SITE_URL ?>/index.php#latest">Browse all</a>
  </div>
</header>
<main>
