jQuery(function ($) {

    $('form#profile-form').on('submit', function (e) {
        e.preventDefault();

        var button = $(this).find('button[type="submit"]');
        button.attr('disabled', 'disabled');

        $.post(
            simpleAuthAjax.ajax_url,
            $(this).serialize() + '&_wpnonce=' + simpleAuthAjax.nonce,
            function (response) {
                if (response.success) {
                    $('#profile-update-message')
                        .html(response.data.message)
                        .removeClass('hidden');

                    setTimeout(function () {
                        $('#profile-update-message').addClass('hidden');
                    }, 4000);
                }

                button.removeAttr('disabled');
            });

    });

    $('form#simple-auth-login-form').on('submit', function (e) {
        e.preventDefault();

        var button = $(this).find('button[type="submit"]');
        button.attr('disabled', 'disabled');

        wp.ajax.post('simple-auth-login-form',
            $(this).serialize()
        ).done(function (response) {
            $('#login-message').html(response.message)
                .removeClass('hidden')
                .removeClass('error-message')
                .addClass('success-message');

            setTimeout(function () {
                window.location.reload();
            }, 2000);

        }).fail(function (err) {
            console.log('Failed', err);
            $('#login-message').html(err.message).removeClass('hidden').addClass('error-message');

            button.removeAttr('disabled');
        })
    });

    $('button#fetch-posts').on('click', function (e) {
        e.preventDefault();

        var button = $(this),
            ul = $('#ajax-posts-list'),
            page = parseInt(button.data('page')) || 1;

        button.attr('disabled', 'disabled');


        wp.ajax.send('simple-fetch-posts', {
            data: {
                _wpnonce: simpleAuthAjax.nonce,
                page: page
            }
        }).done(function (posts) {
            console.log(posts);

            if (posts.length) {
                let html = '';

                posts.forEach(function (post) {
                    html += '<li><a href="' + post.link + '">' + post.title + '</a></li>';
                });

                ul.append(html);
                button.data('page', page + 1).removeAttr('disabled');
            } else {
                alert('No more posts to fetch');
            }

        }).fail(function (err) {
            alert('Failed to fetch posts');

            button.removeAttr('disabled');
        });
    });

});