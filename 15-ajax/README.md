# Ajax in WordPress

## 📄 What is AJAX?

**AJAX** (Asynchronous JavaScript and XML) is a technique that allows web pages to communicate with the server without reloading the entire page. This enables dynamic content updates, enhancing user experience by making interactions faster and more responsive.

## 📊 How AJAX Works

1. **User Action:** A user interacts with the page (e.g., clicks a button).
2. **AJAX Request:** JavaScript sends a request to the server asynchronously.
3. **Server Processing:** The server processes the request and sends back data.
4. **Page Update:** JavaScript updates the webpage with the response without reloading.

## 🔧 Implementing AJAX in WordPress

### 1. **Enqueue Scripts and Localize Data**

```php
function my_plugin_enqueue_scripts() {
    wp_enqueue_script('my-plugin-script', plugin_dir_url(__FILE__) . 'js/my-plugin.js', ['jquery'], null, true);

    wp_localize_script('my-plugin-script', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('my_plugin_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');
```

### 2. **Backend PHP Handler (OOP Approach)**

```php
class MyPlugin_Ajax_Handler {

    public function __construct() {
        add_action('wp_ajax_fetch_data', [$this, 'fetch_data']);
        add_action('wp_ajax_nopriv_fetch_data', [$this, 'fetch_data']);
    }

    public function fetch_data() {
        check_ajax_referer('my_plugin_nonce');

        $data = [
            'message' => 'Data fetched successfully!',
        ];

        wp_send_json_success($data);
    }
}

new MyPlugin_Ajax_Handler();
```

---

## 📝 Different Ways to Send AJAX Requests

### 1. **Using `$.ajax` (jQuery)**

```javascript
jQuery(document).ready(function($) {
    $('#ajax-btn').on('click', function() {
        $.ajax({
            url: my_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_data',
                _ajax_nonce: my_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('Error fetching data.');
            }
        });
    });
});
```

### 2. **Using `$.post` (jQuery)**

```javascript
jQuery(document).ready(function($) {
    $('#post-btn').on('click', function() {
        $.post(my_ajax_object.ajax_url, {
            action: 'fetch_data',
            _ajax_nonce: my_ajax_object.nonce
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
            }
        });
    });
});
```

### 3. **Using `$.get` (jQuery)**

```javascript
jQuery(document).ready(function($) {
    $('#get-btn').on('click', function() {
        $.get(my_ajax_object.ajax_url, {
            action: 'fetch_data',
            _ajax_nonce: my_ajax_object.nonce
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
            }
        });
    });
});
```

---

## 🔗 Using `wp.ajax.send` and `wp.ajax.post`

### 1. **Using `wp.ajax.send`**

```javascript
jQuery(document).ready(function($) {
    $('#send-btn').on('click', function() {
        wp.ajax.send('fetch_data', {
            data: {
                _ajax_nonce: my_ajax_object.nonce
            },
            success: function(response) {
                alert(response.message);
            },
            error: function(error) {
                alert('Error: ' + error);
            }
        });
    });
});
```

### 2. **Using `wp.ajax.post`**

```javascript
jQuery(document).ready(function($) {
    $('#post-btn').on('click', function() {
        wp.ajax.post('fetch_data', {
            _ajax_nonce: my_ajax_object.nonce
        }).done(function(response) {
            alert(response.message);
        }).fail(function(error) {
            alert('Error: ' + error);
        });
    });
});
```

---

## 🛡️ Security with Nonces

- Use `wp_create_nonce()` in PHP to generate a nonce.
- Use `check_ajax_referer()` in the AJAX handler to validate the nonce.
- Pass the nonce to JavaScript using `wp_localize_script()`.

### **Example:**

**PHP:**
```php
wp_localize_script('my-plugin-script', 'my_ajax_object', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('my_plugin_nonce')
]);
```

**JavaScript:**
```javascript
data: {
    action: 'fetch_data',
    _ajax_nonce: my_ajax_object.nonce
}
```

**PHP Handler:**
```php
check_ajax_referer('my_plugin_nonce');
```

---

## 📚 Summary

| Method          | Flexibility  | HTTP Method | Usage                                     |
|-----------------|--------------|-------------|-------------------------------------------|
| `$.ajax`        | High         | GET/POST    | Custom AJAX requests                      |
| `$.post`        | Moderate     | POST        | Quick POST requests                      |
| `$.get`         | Moderate     | GET         | Quick GET requests                       |
| `wp.ajax.send`  | High         | GET/POST    | Flexible with callbacks                 |
| `wp.ajax.post`  | Simplified   | POST        | Simplified POST with promise handling   |

