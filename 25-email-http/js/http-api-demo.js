jQuery(document).ready(function ($) {
    function fetchPosts() {
        $.ajax({
            url: httpApiDemo.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_posts',
                nonce: httpApiDemo.nonce
            },
            success: function (response) {
                if (response.success) {
                    var posts = response.data;
                    var output = '<ul>';
                    posts.forEach(function (post) {
                        output += '<li>' + post.title + ' <button class="delete-post" data-id="' + post.id + '">Delete</button></li>';
                    });
                    output += '</ul>';
                    $('#posts-list').html(output);
                } else {
                    alert('Error fetching posts: ' + response.data);
                }
            }
        });
    }

    $('#fetch-posts').on('click', fetchPosts);

    $('#create-post-form').on('submit', function (e) {
        e.preventDefault();
        var title = $('#post-title').val();
        var body = $('#post-body').val();

        $.ajax({
            url: httpApiDemo.ajax_url,
            type: 'POST',
            data: {
                action: 'create_post',
                nonce: httpApiDemo.nonce,
                title: title,
                body: body
            },
            success: function (response) {
                if (response.success) {
                    alert('Post created successfully!');
                    $('#post-title').val('');
                    $('#post-body').val('');
                    fetchPosts();
                } else {
                    alert('Error creating post: ' + response.data);
                }
            }
        });
    });

    $(document).on('click', '.delete-post', function () {
        var postId = $(this).data('id');

        $.ajax({
            url: httpApiDemo.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_post',
                nonce: httpApiDemo.nonce,
                post_id: postId
            },
            success: function (response) {
                if (response.success) {
                    alert('Post deleted successfully!');
                    fetchPosts();
                } else {
                    alert('Error deleting post: ' + response.data);
                }
            }
        });
    });
});