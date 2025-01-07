<?php get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php if (have_posts()) : ?>
            <header class="page-header">
                <h1 class="page-title"><?php _e('快讯列表', 'jiaoziAI-news'); ?></h1>
            </header>

            <div class="jiaoziAI-news-grid">
                <?php
                while (have_posts()) :
                    the_post();
                    get_template_part('templates/content', 'news');
                endwhile;
                ?>
            </div>

            <?php
            if (jiaoziAI_news()->get_option('enable_scroll_load')) {
                echo '<div class="jiaoziAI-news-load-more" data-page="1">';
                echo '<button class="load-more-button">' . __('加载更多', 'jiaoziAI-news') . '</button>';
                echo '</div>';
            } else {
                the_posts_pagination();
            }
            ?>

        <?php else : ?>
            <p><?php _e('暂无快讯', 'jiaoziAI-news'); ?></p>
        <?php endif; ?>
    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
