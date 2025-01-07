(function($) {
    'use strict';

    var NewsAdmin = {
        init: function() {
            this.initTabs();
            this.initColorPicker();
            this.initCharacterCounter();
            this.initTitlePreview();
            this.initSourceValidation();
            this.initMediaUploader();
            this.initTooltips();
        },

        // 初始化标签页
        initTabs: function() {
            $('.tab-button').on('click', function() {
                var $this = $(this);
                var tab = $this.data('tab');
                
                // 切换按钮状态
                $('.tab-button').removeClass('active');
                $this.addClass('active');
                
                // 切换内容显示
                $('.tab-content').removeClass('active');
                $('#' + tab + '-settings').addClass('active');
            });
        },

        // 初始化颜色选择器
        initColorPicker: function() {
            $('.color-picker').wpColorPicker({
                defaultColor: jiaoziAINews.defaultColor,
                change: function(event, ui) {
                    $('#title_preview').css('color', ui.color.toString());
                },
                clear: function() {
                    $('#title_preview').css('color', jiaoziAINews.defaultColor);
                },
                palettes: [
                    '#333333', '#0073aa', '#dc3232',
                    '#46b450', '#f56e28', '#00a0d2', '#826eb4'
                ]
            });
        },

        // 初始化字符计数器
        initCharacterCounter: function() {
            var $description = $('#seo_description');
            var maxLength = 160;

            $description.on('input', function() {
                var length = $(this).val().length;
                var remaining = maxLength - length;
                var $counter = $(this).next('.description-counter');
                
                if (!$counter.length) {
                    $counter = $('<div class="description-counter"></div>');
                    $(this).after($counter);
                }

                $counter.text(jiaoziAINews.remainingChars + 
                            remaining + jiaoziAINews.characters);

                if (remaining < 0) {
                    $counter.addClass('error');
                } else {
                    $counter.removeClass('error');
                }
            }).trigger('input');
        },

        // 初始化标题预览
        initTitlePreview: function() {
            var $title = $('#post_title');
            var $preview = $('#title_preview');
            var defaultTitle = jiaoziAINews.defaultTitle;

            $title.on('input', function() {
                var value = $(this).val();
                $preview.text(value || defaultTitle);
            });
        },

        // 初始化来源验证
        initSourceValidation: function() {
            var $source = $('#news_source');
            var $sourceUrl = $('#news_source_url');
            var $validation = $('.source-validation');

            $sourceUrl.on('change', function() {
                var url = $(this).val();
                if (url && !$source.val()) {
                    if (!$validation.length) {
                        $validation = $('<div class="source-validation"></div>');
                        $(this).after($validation);
                    }
                    $validation.text(jiaoziAINews.enterSourceName)
                              .addClass('error')
                              .slideDown();
                } else {
                    $validation.slideUp();
                }
            });

            $source.on('input', function() {
                if ($(this).val()) {
                    $validation.slideUp();
                }
            });
        },

        // 初始化媒体上传
        initMediaUploader: function() {
            var frame;
            var $imageContainer = $('.news-image-container');
            var $uploadButton = $('.upload-news-image');
            var $removeButton = $('.remove-news-image');
            var $imageId = $('#news_image_id');

            $uploadButton.on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: jiaoziAINews.selectImage,
                    button: {
                        text: jiaoziAINews.useImage
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var imageUrl = attachment.sizes.thumbnail ? 
                                 attachment.sizes.thumbnail.url : 
                                 attachment.url;

                    $imageContainer.html('<img src="' + imageUrl + '" alt="">');
                    $imageId.val(attachment.id);
                    $uploadButton.hide();
                    $removeButton.show();
                });

                frame.open();
            });

            $removeButton.on('click', function(e) {
                e.preventDefault();
                $imageContainer.html('');
                $imageId.val('');
                $removeButton.hide();
                $uploadButton.show();
            });
        },

        // 初始化工具提示
        initTooltips: function() {
            $('.help-tip').each(function() {
                var $tip = $(this);
                var content = $tip.data('tip');
                
                if (content) {
                    var $tooltip = $('<div class="tooltip-content"></div>')
                        .text(content);
                    $tip.addClass('tooltip').append($tooltip);
                }
            });
        }
    };

    // 文档加载完成后初始化
    $(document).ready(function() {
        NewsAdmin.init();
    });

})(jQuery);
