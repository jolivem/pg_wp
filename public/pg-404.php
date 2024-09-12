<?php
get_header(); ?>

<style>
.site-content .ast-container {
    /* display: flex; */
    display: block !important;
}
</style>


<div id="primary" class="content-area" style="margin: 100px 100px 200px 100px;">
    <main id="main" class="site-main" role="main">
        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Oops ! La page est introuvable.', 'your-theme' ); ?></h1>
            </header><!-- .page-header -->

        </section><!-- .error-404 -->
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
