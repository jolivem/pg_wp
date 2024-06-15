<?php
get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Oops ! La page est introuvable.', 'your-theme' ); ?></h1>
            </header><!-- .page-header -->

            <!-- <div class="page-content">
                <p><?php esc_html_e( 'Peut-être n?', 'your-theme' ); ?></p>
            </div> .page-content -->
        </section><!-- .error-404 -->
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
