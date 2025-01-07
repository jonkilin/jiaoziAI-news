<?php get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('jiaoziAI-news-single'); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    <div class="entry-meta">
                        <?php
                        if (jiaoziAI_news()->get_option('show_date')) {
                            echo '<span class="posted-on">' . get_the_date() . '</span>';
                        }
                        if (jiaoziAI_news()->get_option('show_author')) {
                            echo '<span class="author">' . get_the_author() . '</span>';
                        }
                        ?>
                    </div>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-footer">
                    <?php
                    if (jiaoziAI_news()->get_option('show_category')) {
                        $categories = get_the_term_list(get_the_ID(), 'news_category', '', ', ');
                        if ($categories) {
                            echo '<div class="news-categories">' . $categories . '</div>';
                        }
                    }

                    if (jiaoziAI_news()->get_option('show_tags')) {
                        $tags = get_the_term_list(get_the_ID(), 'news_tag', '', ', ');
                        if ($tags) {
                            echo '<div class="news-tags">' . $tags . '</div>';
                        }
                    }
                    ?>
                </footer>
            </article>
        <?php endwhile; ?>
    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>