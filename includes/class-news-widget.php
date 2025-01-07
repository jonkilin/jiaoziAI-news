<?php
if (!defined('ABSPATH')) {
    exit;
}

class JiaoziAI_News_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'jiaoziAI_news_widget',
            __('快讯列表', 'jiaoziAI-news'),
            ['description' => __('显示最新的快讯列表', 'jiaoziAI-news')]
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('最新快讯', 'jiaoziAI-news');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;

        $query = new WP_Query([
            'post_type' => 'jiaoziAI-news',
            'posts_per_page' => $number,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        if ($query->have_posts()) {
            echo '<ul class="jiaoziAI-news-widget-list">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li>';
                echo '<a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a>';
                echo '<span class="post-date">' . get_the_date() . '</span>';
                echo '</li>';
            }
            echo '</ul>';
        }

        wp_reset_postdata();
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('最新快讯', 'jiaoziAI-news');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_attr_e('标题:', 'jiaoziAI-news'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">
                <?php esc_attr_e('显示数量:', 'jiaoziAI-news'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('number')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('number')); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($number); ?>" 
                   size="3">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? 
                            strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? 
                            absint($new_instance['number']) : 5;
        return $instance;
    }
}

add_action('widgets_init', function() {
    register_widget('JiaoziAI_News_Widget');
});