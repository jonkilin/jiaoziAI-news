jQuery(function($) {
    // 加载更多功能
    var $loadMore = $('.jiaoziAI-news-load-more');
    var loading = false;

    if ($loadMore.length && jiaoziAINews.enableScrollLoad) {
        $(window).on('scroll', function() {
            if (loading) return;

            if ($(window).scrollTop() + $(window).height() > $loadMore.offset().top - 100) {
                loadMoreNews();
            }
        });
    }

    $loadMore.on('click', '.load-more-button', function() {
        loadMoreNews();
    });

    function loadMoreNews() {
        if (loading) return;
        loading = true;

        var $button = $loadMore.find('.load-more-button');
        var currentPage = parseInt($loadMore.data('page'));
        
        $button.text(jiaoziAINews.loading);

        $.ajax({
            url: jiaoziAINews.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_news',
                nonce: jiaoziAINews.nonce,
                page: currentPage + 1
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.html) {
                        $('.jiaoziAI-news-grid').append(response.data.html);
                        $loadMore.data('page', currentPage + 1);
                        
                        if (!response.data.hasMore) {
                            $button.text(jiaoziAINews.noMore).prop('disabled', true);
                        } else {
                            $button.text(jiaoziAINews.loadMore);
                        }
                    }
                } else {
                    $button.text(jiaoziAINews.error);
                }
            },
            error: function() {
                $button.text(jiaoziAINews.error);
            },
            complete: function() {
                loading = false;
            }
        });
    }
});