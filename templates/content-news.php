<article id="post-<?php the_ID(); ?>" <?php post_class('jiaoziAI-news-item'); ?>>
    <?php if (has_post_thumbnail()) : ?>
        <div class="news-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium'); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="news-content">
        <header class="entry-header">
            <?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>

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

        <div class="entry-summary">
            <?php the_excerpt(); ?>
        </div>

        <?php if (jiaoziAI_news()->get_option('show_category') || jiaoziAI_news()->get_option('show_tags')) : ?>
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
        <?php endif; ?>
    </div>
</article>