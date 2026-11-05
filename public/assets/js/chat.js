/**
 * TRIVEX — support chat polling (customer + backoffice).
 * Server contract lives in SupportController::messages/postMessage and
 * AdminController::chatMessages/chatPost/chatHeartbeat.
 */
(function () {
    'use strict';

    var POLL_INTERVAL = 5000;
    var ADMIN_POLL_INTERVAL = 4000;
    var HEARTBEAT_INTERVAL = 30000;

    function scrollToBottom(container) {
        if (!container) return;
        container.scrollTop = container.scrollHeight;
    }

    function appendMessages(container, html) {
        if (!container || !html) return;
        var template = document.createElement('template');
        template.innerHTML = html.trim();
        var empty = container.querySelector('[data-chat-empty], .chat-empty');
        if (empty) empty.remove();
        while (template.content.firstChild) {
            container.appendChild(template.content.firstChild);
        }
    }

    function setStatusPill(pill, status, label) {
        if (!pill) return;
        if (label) pill.textContent = label;
        pill.classList.remove('status-blue', 'status-amber', 'status-green', 'status-gray');
        if (status === 'open') pill.classList.add('status-blue');
        else if (status === 'in_progress') pill.classList.add('status-amber');
        else if (status === 'concluded') pill.classList.add('status-green');
        else pill.classList.add('status-gray');
    }

    function postForm(url, formData) {
        return fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            return response.json().catch(function () { return { error: true }; });
        });
    }

    /* ------------------------------------------------------------------
     * Customer chat
     * ---------------------------------------------------------------- */
    function initCustomerChat() {
        var panel = document.querySelector('[data-chat-window]');
        if (!panel) return;

        var chatId = panel.getAttribute('data-chat-id');
        var pollUrl = panel.getAttribute('data-poll-url');
        var sendUrl = panel.getAttribute('data-send-url');
        var status = panel.getAttribute('data-status') || 'open';
        var messages = panel.querySelector('[data-chat-messages]');
        var form = panel.querySelector('[data-chat-form]');
        var afterInput = panel.querySelector('[data-chat-after]');
        var textarea = panel.querySelector('[data-chat-input]');
        var submit = panel.querySelector('[data-chat-send]');
        var statusPill = panel.querySelector('[data-chat-status-pill]');
        var moderatorLabel = panel.querySelector('[data-chat-moderator]');
        var notice = panel.querySelector('[data-chat-notice]');
        var hint = panel.querySelector('[data-chat-hint]');
        var lastId = afterInput ? parseInt(afterInput.value, 10) || 0 : 0;
        var busy = false;
        var closed = status !== 'open' && status !== 'in_progress';

        function applyStatus(nextStatus, label, moderatorText) {
            var wasClosed = closed;
            status = nextStatus || status;
            closed = status !== 'open' && status !== 'in_progress';
            setStatusPill(statusPill, status, label);
            if (moderatorLabel && typeof moderatorText === 'string') {
                moderatorLabel.textContent = moderatorText;
            }
            if (form) form.hidden = closed;
            if (notice) notice.hidden = !closed;
            if (hint && closed) {
                var span = hint.querySelector('span');
                if (span) span.textContent = label || '';
            }
            if (!wasClosed && closed) {
                if (textarea) textarea.blur();
            }
        }

        function poll() {
            if (document.hidden || busy) return;
            var separator = pollUrl.indexOf('?') === -1 ? '?' : '&';
            fetch(pollUrl + separator + 'after=' + encodeURIComponent(String(lastId)), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (data) {
                    if (!data || !data.ok) return;
                    if (data.count > 0 && data.html) {
                        appendMessages(messages, data.html);
                        scrollToBottom(messages);
                    }
                    if (data.last_id && data.last_id > lastId) {
                        lastId = parseInt(data.last_id, 10) || lastId;
                        if (afterInput) afterInput.value = String(lastId);
                    }
                    if (data.status) {
                        applyStatus(data.status, data.status_label, data.moderator_text);
                    }
                })
                .catch(function () {
                    /* transient errors are ignored — the next tick retries */
                });
        }

        if (form && textarea && submit) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var value = textarea.value.trim();
                if (!value || busy) return;

                busy = true;
                submit.disabled = true;
                var data = new FormData(form);
                data.set('message', value);
                data.set('after', String(lastId));

                postForm(sendUrl, data)
                    .then(function (result) {
                        if (result && result.ok) {
                            textarea.value = '';
                            textarea.style.height = 'auto';
                            if (result.html) {
                                appendMessages(messages, result.html);
                                scrollToBottom(messages);
                            }
                            if (result.last_id) {
                                lastId = parseInt(result.last_id, 10) || lastId;
                                if (afterInput) afterInput.value = String(lastId);
                            }
                        } else if (result && result.error && typeof result.error === 'string') {
                            window.alert(result.error);
                        }
                    })
                    .catch(function () {
                        window.alert('Não foi possível enviar a mensagem. Tente novamente.');
                    })
                    .then(function () {
                        busy = false;
                        submit.disabled = false;
                        if (textarea) textarea.focus();
                    });
            });

            textarea.addEventListener('input', function () {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(120, textarea.scrollHeight) + 'px';
            });

            textarea.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }
            });
        }

        applyStatus(status, statusPill ? statusPill.textContent.trim() : null, null);
        scrollToBottom(messages);

        if (!closed) {
            window.setInterval(poll, POLL_INTERVAL);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) poll();
            });
        }
    }

    /* ------------------------------------------------------------------
     * Backoffice chat
     * ---------------------------------------------------------------- */
    function initAdminChat() {
        var panel = document.querySelector('[data-admin-chat]');
        if (!panel) return;

        var pollUrl = panel.getAttribute('data-poll-url');
        var sendUrl = panel.getAttribute('data-send-url');
        var heartbeatUrl = panel.getAttribute('data-heartbeat-url');
        var status = panel.getAttribute('data-status') || 'open';
        var assigned = panel.getAttribute('data-assigned') === '1';
        var messages = panel.querySelector('[data-admin-chat-messages]');
        var form = panel.querySelector('[data-admin-chat-form]');
        var afterInput = panel.querySelector('[data-admin-chat-after]');
        var textarea = panel.querySelector('[data-admin-chat-input]');
        var submit = form ? form.querySelector('button[type="submit"]') : null;
        var statusPill = panel.querySelector('[data-admin-chat-status]');
        var lastId = afterInput ? parseInt(afterInput.value, 10) || 0 : 0;
        var busy = false;
        var reloaded = false;
        var markedRead = false;

        function reloadOnce() {
            if (reloaded) return;
            reloaded = true;
            window.setTimeout(function () { window.location.reload(); }, 700);
        }

        function heartbeat() {
            if (!heartbeatUrl || !assigned || status !== 'in_progress') return;
            fetch(heartbeatUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function () {});
        }

        function applyStatus(nextStatus, label, moderator, isAssigned) {
            var changed = nextStatus && nextStatus !== status;
            status = nextStatus || status;

            if (typeof isAssigned === 'boolean') {
                if (isAssigned !== assigned) {
                    assigned = isAssigned;
                    reloadOnce();
                    return;
                }
            }

            setStatusPill(statusPill, status, label);
            if (changed) {
                var closed = status === 'concluded' || status === 'archived' || status === 'open';
                if (closed) {
                    reloadOnce();
                }
            }
        }

        function poll() {
            if (document.hidden || busy) return;
            var separator = pollUrl.indexOf('?') === -1 ? '?' : '&';
            fetch(pollUrl + separator + 'after=' + encodeURIComponent(String(lastId)), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (data) {
                    if (!data || !data.ok) return;
                    if (data.count > 0 && data.html) {
                        appendMessages(messages, data.html);
                        scrollToBottom(messages);
                        markedRead = false;
                    }
                    if (data.last_id && data.last_id > lastId) {
                        lastId = parseInt(data.last_id, 10) || lastId;
                        if (afterInput) afterInput.value = String(lastId);
                    }
                    if (data.status) {
                        applyStatus(data.status, data.status_label, data.moderator, data.is_assigned);
                    }
                })
                .catch(function () {});
        }

        if (form && textarea) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var value = textarea.value.trim();
                if (!value || busy) return;

                busy = true;
                if (submit) submit.disabled = true;
                var data = new FormData(form);
                data.set('message', value);
                data.set('after', String(lastId));

                postForm(sendUrl, data)
                    .then(function (result) {
                        if (result && result.ok) {
                            textarea.value = '';
                            textarea.style.height = 'auto';
                            if (result.html) {
                                appendMessages(messages, result.html);
                                scrollToBottom(messages);
                            }
                            if (result.last_id) {
                                lastId = parseInt(result.last_id, 10) || lastId;
                                if (afterInput) afterInput.value = String(lastId);
                            }
                        } else if (result && result.error && typeof result.error === 'string') {
                            window.alert(result.error);
                        }
                    })
                    .catch(function () {
                        window.alert('Não foi possível enviar a mensagem.');
                    })
                    .then(function () {
                        busy = false;
                        if (submit) submit.disabled = false;
                        textarea.focus();
                    });
            });

            textarea.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }
            });

            textarea.addEventListener('input', function () {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(120, textarea.scrollHeight) + 'px';
            });
        }

        scrollToBottom(messages);

        if (status === 'in_progress' && assigned) {
            window.setInterval(poll, ADMIN_POLL_INTERVAL);
            window.setInterval(heartbeat, HEARTBEAT_INTERVAL);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) poll();
            });
        }
    }

    /* ------------------------------------------------------------------
     * Boot
     * ---------------------------------------------------------------- */
    function boot() {
        initCustomerChat();
        initAdminChat();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
