<?php
/**
 * Agressiv AJAX Helper - chat.php dagi eski skriptlarni chetlab o'tadi.
 */
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

ob_start();

register_shutdown_function(function() use ($isAjax) {
    $content = ob_get_clean();
    $redirectUrl = null;

    foreach (headers_list() as $header) {
        if (stripos($header, 'Location:') === 0) {
            $redirectUrl = trim(substr($header, 9));
            header_remove('Location');
        }
    }

    if ($isAjax) {
        if ($redirectUrl) {
            header('Content-Type: application/json');
            echo json_encode(['redirect' => $redirectUrl]);
            exit;
        }
        echo $content;
    } else {
        echo '<div id="ajax-root">' . $content . '</div>';
        ?>
        <div id="ajax-loader" style="position:fixed;top:0;left:0;height:3px;background:#00a884;z-index:99999;width:0%;transition:0.3s;opacity:0;"></div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(function() {
                // 1. ChatBox uchun skroll funksiyasi
                function scrollToBottom() {
                    let cb = $('#chatBox');
                    if(cb.length) cb.scrollTop(cb[0].scrollHeight);
                }
                scrollToBottom();

                // 2. Universal yuklash funksiyasi
                function loadAjax(url, postData = null) {
                    $("#ajax-loader").css({width: '20%', opacity: 1});
                    $.ajax({
                        url: url,
                        type: postData ? 'POST' : 'GET',
                        data: postData,
                        processData: postData ? false : true,
                        contentType: postData ? false : 'application/x-www-form-urlencoded',
                        success: function(res) {
                            $("#ajax-loader").css('width', '100%');
                            if (typeof res === 'object' && res.redirect) {
                                loadAjax(res.redirect);
                                return;
                            }
                            // Faqat kerakli qismlarni yangilash
                            let $html = $(res);
                            $('#chatBox').html($html.find('#chatBox').html() || $html.filter('#chatBox').html());
                            $('.section-list').html($html.find('.section-list').html() || $html.filter('.section-list').html());
                            
                            scrollToBottom();
                            if(!postData) window.history.pushState({}, '', url);
                        },
                        complete: function() {
                            setTimeout(() => $("#ajax-loader").css({opacity: 0, width: 0}), 500);
                        }
                    });
                }

                // 3. FORM SUBMIT - ENG MUHIM QISM (Barcha to'qnashuvlarni yengadi)
                $(document).on('submit', 'form', function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation(); // Boshqa skriptlarni to'xtatadi
                    
                    let form = $(this);
                    let msgInput = form.find('textarea, input[type="text"]');
                    let formData = new FormData(this);

                    // Xabar joyini darhol tozalash (Foydalanuvchi kutib qolmasligi uchun)
                    let currentMsg = msgInput.val();
                    msgInput.val('').css('height', 'auto');
                    form.find('input[type="file"]').val('');

                    loadAjax(form.attr('action') || window.location.href, formData);
                    return false;
                });

                // 4. ENTER TUGMASINI BOSHQARISH
                // chat.php dagi eski "Enter" skriptini o'chirib, o'zimiznikini o'rnatamiz
                $(document).on('keydown', 'textarea#messageInput', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        $(this).closest('form').submit();
                    }
                });

                // 5. LINKLAR
                $(document).on('click', 'a:not(.no-ajax, [target="_blank"])', function(e) {
                    let url = $(this).attr('href');
                    if (url && url !== '#' && !url.includes('logout')) {
                        e.preventDefault();
                        loadAjax(url);
                    }
                });
            });
        </script>
        <?php
    }
});