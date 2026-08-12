/**
 * BOLD Theme Scripts
 * 从 footer.php 内联脚本提取为可缓存静态资源，随 defer 加载。
 * 依赖 header.php 内联定义的全局 applyTheme()。
 */
(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ================= Mermaid 按需加载 ================= */

    var MERMAID_SRC = 'https://cdn.jsdelivr.net/npm/mermaid@10.9.6/dist/mermaid.min.js';
    var MERMAID_SRI = 'sha384-qX9VvWkP79m/O121ZE6sOYp0nf/pldQgtvWDbkpzi+3mUo4Wn4Ix4cFzNPay3VaB';
    var mermaidLoading = null;
    var mermaidRendering = false;

    function ensureMermaid() {
        if (typeof mermaid !== 'undefined') return Promise.resolve();
        if (mermaidLoading) return mermaidLoading;
        mermaidLoading = new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            script.src = MERMAID_SRC;
            script.integrity = MERMAID_SRI;
            script.crossOrigin = 'anonymous';
            script.onload = function () { resolve(); };
            script.onerror = function () {
                mermaidLoading = null;
                reject(new Error('Mermaid failed to load'));
            };
            document.head.appendChild(script);
        });
        return mermaidLoading;
    }

    function isDarkMode() {
        return document.documentElement.classList.contains('dark-mode');
    }

    function getMermaidConfig() {
        var dark = isDarkMode();
        return {
            startOnLoad: false,
            securityLevel: 'strict',
            theme: dark ? 'dark' : 'base',
            themeVariables: dark ? {
                background: '#121212',
                primaryColor: '#10b981',
                primaryBorderColor: '#10b981',
                primaryTextColor: '#000000',
                lineColor: '#10b981',
                textColor: '#e5e5e5',
                mainBkg: '#1e1e1e',
                nodeBorder: '#10b981',
                clusterBkg: '#121212',
                clusterBorder: '#10b981',
                fontFamily: 'Noto Sans SC, sans-serif'
            } : {
                background: '#ffffff',
                primaryColor: '#fef08a',
                primaryBorderColor: '#000000',
                primaryTextColor: '#000000',
                lineColor: '#000000',
                textColor: '#111827',
                mainBkg: '#ffffff',
                nodeBorder: '#000000',
                clusterBkg: '#f8f8f8',
                clusterBorder: '#000000',
                fontFamily: 'Noto Sans SC, sans-serif'
            }
        };
    }

    window.renderBoldMermaid = function () {
        var diagrams = Array.prototype.slice.call(document.querySelectorAll('.mermaid'));
        if (!diagrams.length || mermaidRendering) return;
        mermaidRendering = true;

        ensureMermaid().then(function () {
            diagrams.forEach(function (diagram) {
                if (!diagram.dataset.mermaidSource) {
                    diagram.dataset.mermaidSource = diagram.textContent.trim();
                }
                diagram.removeAttribute('data-processed');
                diagram.textContent = diagram.dataset.mermaidSource;
            });

            mermaid.initialize(getMermaidConfig());
            return mermaid.run({ nodes: diagrams }).catch(function (error) {
                console.error('Mermaid render failed:', error);
                // 渲染失败时至少让读者看到图表源码
                diagrams.forEach(function (diagram) {
                    if (!diagram.querySelector('svg') && diagram.dataset.mermaidSource) {
                        diagram.textContent = diagram.dataset.mermaidSource;
                    }
                });
            });
        }).catch(function (error) {
            console.error(error);
        }).then(function () {
            mermaidRendering = false;
        });
    };

    /* ================= DOM Ready ================= */

    document.addEventListener('DOMContentLoaded', function () {

        /* ---------- 移动端主导航 ---------- */
        var navToggle = document.getElementById('nav-toggle');
        var primaryNavigation = document.getElementById('primary-navigation');

        function setNavigationState(open) {
            if (!navToggle || !primaryNavigation) return;
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            primaryNavigation.classList.toggle('is-open', open);
        }

        if (navToggle && primaryNavigation) {
            navToggle.addEventListener('click', function () {
                setNavigationState(navToggle.getAttribute('aria-expanded') !== 'true');
            });

            primaryNavigation.addEventListener('click', function (event) {
                if (event.target.closest('a')) setNavigationState(false);
            });

            document.addEventListener('click', function (event) {
                if (navToggle.getAttribute('aria-expanded') === 'true'
                    && !navToggle.contains(event.target)
                    && !primaryNavigation.contains(event.target)) {
                    setNavigationState(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && navToggle.getAttribute('aria-expanded') === 'true') {
                    setNavigationState(false);
                    navToggle.focus();
                }
            });
        }

        /* ---------- 主题切换图标 ---------- */
        function updateIcons() {
            var iconSun = document.getElementById('icon-sun');
            var iconMoon = document.getElementById('icon-moon');
            if (!iconSun || !iconMoon) return;
            if (isDarkMode()) { iconSun.classList.remove('hidden'); iconMoon.classList.add('hidden'); }
            else { iconSun.classList.add('hidden'); iconMoon.classList.remove('hidden'); }
        }
        updateIcons();

        /* ---------- 站点标题光标跟随特效 ---------- */
        var logoLink = document.getElementById('site-logo');
        if (logoLink) {
            var titleText = logoLink.querySelector('.mouse-gradient-text');
            if (titleText) {
                logoLink.addEventListener('mousemove', function (e) {
                    var rect = titleText.getBoundingClientRect();
                    titleText.style.setProperty('--mouse-x', (e.clientX - rect.left) + 'px');
                    titleText.style.setProperty('--mouse-y', (e.clientY - rect.top) + 'px');
                });
            }
        }

        /* ---------- 回到顶部 & 阅读进度条 ---------- */
        var backToTopBtn = document.getElementById('fab-back');
        var progressBar = document.getElementById('reading-progress');
        window.addEventListener('scroll', function () {
            if (backToTopBtn) {
                if (window.scrollY > 300) { backToTopBtn.classList.remove('opacity-0', 'pointer-events-none'); }
                else { backToTopBtn.classList.add('opacity-0', 'pointer-events-none'); }
            }
            if (progressBar) {
                var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                var ratio = scrollHeight > 0 ? Math.min(1, Math.max(0, scrollTop / scrollHeight)) : 0;
                progressBar.style.transform = 'scaleX(' + ratio + ')';
            }
        }, { passive: true });
        if (backToTopBtn) {
            backToTopBtn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
            });
        }

        /* ---------- TOC ---------- */
        function getDirectChild(element, matcher) {
            var children = element.children || [];
            for (var i = 0; i < children.length; i++) {
                if (matcher(children[i])) return children[i];
            }
            return null;
        }

        function getTocHeadingLevel(link) {
            var className = link.className || '';
            var classMatch = className.match(/node-name--H([1-6])/i);
            if (classMatch) return parseInt(classMatch[1], 10);

            if (link.hash) {
                try {
                    var target = document.getElementById(decodeURIComponent(link.hash.slice(1)));
                    if (target && /^H[1-6]$/i.test(target.tagName)) {
                        return parseInt(target.tagName.slice(1), 10);
                    }
                } catch (error) {}
            }

            return 0;
        }

        function setTocToggleState(button, item, collapsed) {
            var label = button.getAttribute('data-toc-title') || '';
            item.classList.toggle('is-toc-collapsed', collapsed);
            button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            button.setAttribute('aria-label', (collapsed ? '展开：' : '折叠：') + label);
        }

        function clampTocScroll(scrollElement) {
            if (!scrollElement) return;
            var maxScrollTop = Math.max(0, scrollElement.scrollHeight - scrollElement.clientHeight);
            if (scrollElement.scrollTop < 0) scrollElement.scrollTop = 0;
            if (scrollElement.scrollTop > maxScrollTop) scrollElement.scrollTop = maxScrollTop;
        }

        function resetTocbotCollapseState(tocContainer) {
            var collapsedLists = tocContainer.querySelectorAll('.is-collapsible, .is-collapsed');
            for (var i = 0; i < collapsedLists.length; i++) {
                collapsedLists[i].classList.remove('is-collapsible', 'is-collapsed');
                collapsedLists[i].style.maxHeight = '';
            }
        }

        function insertTocPlaceholder(item, link) {
            var placeholder = document.createElement('span');
            placeholder.className = 'toc-toggle-placeholder';
            placeholder.setAttribute('aria-hidden', 'true');
            item.insertBefore(placeholder, link);
        }

        function initTocCollapse(tocContainer) {
            if (!tocContainer) return;
            resetTocbotCollapseState(tocContainer);

            var tocItems = tocContainer.querySelectorAll('.toc-list-item');
            for (var i = 0; i < tocItems.length; i++) {
                var item = tocItems[i];
                var link = getDirectChild(item, function (child) {
                    return child.classList && child.classList.contains('toc-link');
                });
                if (!link) continue;

                var level = getTocHeadingLevel(link);
                var childList = getDirectChild(item, function (child) {
                    return child.classList && child.classList.contains('toc-list');
                });

                // 仅 1-3 级且有子列表的条目有折叠按钮；
                // 其余（含 h4/h5）一律补占位符，保证缩进对齐
                if (!childList || level < 1 || level > 3) {
                    insertTocPlaceholder(item, link);
                    continue;
                }

                if (!childList.id) childList.id = 'toc-children-' + i;

                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'toc-toggle';
                button.setAttribute('aria-controls', childList.id);
                button.setAttribute('data-toc-title', link.textContent.trim());
                setTocToggleState(button, item, false);
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var currentButton = event.currentTarget;
                    var currentItem = currentButton.parentElement;
                    var isCollapsed = currentItem.classList.contains('is-toc-collapsed');
                    setTocToggleState(currentButton, currentItem, !isCollapsed);
                    clampTocScroll(currentButton.closest('.toc-container'));
                });

                item.insertBefore(button, link);
            }
        }

        // 由标题文本生成稳定锚点 id（不再依赖 DOM 序号，外链锚点不会因加标题而漂移）
        function headingSlug(text, used) {
            var base = (text || '').trim().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\u2E80-\u2FDF\u3040-\u30FF\u3400-\u4DBF\u4E00-\u9FFF\uF900-\uFAFF-]/g, '')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            if (!base) base = 'section';
            var id = base, n = 2;
            while (used[id] || document.getElementById(id)) { id = base + '-' + n; n++; }
            used[id] = true;
            return id;
        }

        var content = document.querySelector('.prose');
        if (content) {
            var headingSelector = 'h1, h2, h3, h4, h5';
            var headers = content.querySelectorAll(headingSelector);
            if (headers.length > 0) {
                var tocWrapper = document.getElementById('toc-wrapper');
                if (tocWrapper) tocWrapper.classList.remove('hidden');

                var usedIds = {};
                headers.forEach(function (header) {
                    if (!header.id) { header.id = headingSlug(header.textContent, usedIds); }
                });

                if (typeof tocbot !== 'undefined') {
                    // 依据 sticky 头部实际高度计算滚动偏移，避免点击目录后标题被遮挡
                    var stickyHeader = document.querySelector('body > header');
                    var offset = Math.ceil((stickyHeader ? stickyHeader.getBoundingClientRect().height : 0) + 16);

                    tocbot.init({
                        tocSelector: '.toc-container',
                        contentSelector: '.prose',
                        headingSelector: headingSelector,
                        hasInnerContainers: true,
                        collapseDepth: 6,
                        scrollSmooth: !prefersReducedMotion,
                        scrollSmoothDuration: 400,
                        headingsOffset: offset,
                        scrollSmoothOffset: -offset
                    });
                    var tocContainer = document.querySelector('.toc-container');
                    initTocCollapse(tocContainer);
                    clampTocScroll(tocContainer);
                    // 滚轮劫持已移除：滚动链由 CSS overscroll-behavior: contain 控制
                }

                if (tocWrapper) {
                    var tocSlot = document.getElementById('mobile-toc-slot');
                    var tocPanelToggle = document.getElementById('toc-panel-toggle');
                    var tocOrigin = document.createComment('toc-origin');
                    tocWrapper.parentNode.insertBefore(tocOrigin, tocWrapper);
                    var tocMobile = false;

                    function setTocPanelState(expanded) {
                        tocWrapper.classList.toggle('toc-panel-collapsed', !expanded);
                        if (tocPanelToggle) {
                            tocPanelToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                        }
                    }

                    function placeToc() {
                        var shouldUseMobileSlot = isMobileViewport() && tocSlot;
                        if (shouldUseMobileSlot && tocWrapper.parentNode !== tocSlot) {
                            tocSlot.appendChild(tocWrapper);
                            tocMobile = true;
                            setTocPanelState(false);
                        } else if (!shouldUseMobileSlot && tocOrigin.parentNode
                            && tocWrapper.parentNode !== tocOrigin.parentNode) {
                            tocOrigin.parentNode.insertBefore(tocWrapper, tocOrigin.nextSibling);
                            tocMobile = false;
                            setTocPanelState(true);
                        } else if (!shouldUseMobileSlot) {
                            tocMobile = false;
                            setTocPanelState(true);
                        }
                        clampTocScroll(tocWrapper.querySelector('.toc-container'));
                    }

                    if (tocPanelToggle) {
                        tocPanelToggle.addEventListener('click', function () {
                            if (!tocMobile) return;
                            setTocPanelState(tocPanelToggle.getAttribute('aria-expanded') !== 'true');
                        });
                    }

                    placeToc();
                    var tocResizeTimer = null;
                    window.addEventListener('resize', function () {
                        clearTimeout(tocResizeTimer);
                        tocResizeTimer = setTimeout(placeToc, 120);
                    });
                }
            }
        }

        /* ---------- 文章链接复制 ---------- */
        var copyLinkButton = document.getElementById('copy-article-link');
        var copyLinkStatus = document.getElementById('copy-link-status');
        if (copyLinkButton) {
            var copyButtonLabel = copyLinkButton.textContent.trim();
            var copyResetTimer = null;

            function copyWithFallback(value) {
                var input = document.createElement('textarea');
                input.value = value;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                var succeeded = false;
                try { succeeded = document.execCommand('copy'); } catch (error) {}
                document.body.removeChild(input);
                copyLinkButton.focus();
                return succeeded ? Promise.resolve() : Promise.reject(new Error('Copy failed'));
            }

            copyLinkButton.addEventListener('click', function () {
                var value = copyLinkButton.getAttribute('data-copy-url') || window.location.href;
                var operation = navigator.clipboard && window.isSecureContext
                    ? navigator.clipboard.writeText(value)
                    : copyWithFallback(value);

                operation.then(function () {
                    var message = copyLinkButton.getAttribute('data-copy-success') || 'Link copied';
                    copyLinkButton.textContent = message;
                    copyLinkButton.classList.add('is-copied');
                    if (copyLinkStatus) copyLinkStatus.textContent = message;
                    clearTimeout(copyResetTimer);
                    copyResetTimer = setTimeout(function () {
                        copyLinkButton.textContent = copyButtonLabel;
                        copyLinkButton.classList.remove('is-copied');
                    }, 2200);
                }).catch(function () {
                    var message = copyLinkButton.getAttribute('data-copy-failure') || 'Copy failed';
                    copyLinkButton.textContent = message;
                    if (copyLinkStatus) copyLinkStatus.textContent = message;
                    clearTimeout(copyResetTimer);
                    copyResetTimer = setTimeout(function () {
                        copyLinkButton.textContent = copyButtonLabel;
                    }, 2200);
                });
            });
        }

        /* ---------- 打赏弹窗 ---------- */
        var rewardOpen = document.getElementById('reward-open');
        var rewardModal = document.getElementById('reward-modal');
        var rewardClose = document.getElementById('reward-close');
        var rewardReturnFocus = null;

        function rewardFocusableElements() {
            if (!rewardModal) return [];
            return Array.prototype.slice.call(rewardModal.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            ));
        }

        function openRewardModal() {
            if (!rewardModal) return;
            rewardReturnFocus = document.activeElement;
            rewardModal.classList.remove('hidden');
            rewardModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            var focusable = rewardFocusableElements();
            if (focusable.length) focusable[0].focus();
        }

        function closeRewardModal() {
            if (!rewardModal || rewardModal.classList.contains('hidden')) return;
            rewardModal.classList.add('hidden');
            rewardModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            if (rewardReturnFocus && typeof rewardReturnFocus.focus === 'function') {
                rewardReturnFocus.focus();
            }
        }

        if (rewardOpen && rewardModal) {
            rewardOpen.addEventListener('click', openRewardModal);
            if (rewardClose) rewardClose.addEventListener('click', closeRewardModal);
            rewardModal.addEventListener('click', function (event) {
                if (event.target === rewardModal) closeRewardModal();
            });
            rewardModal.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeRewardModal();
                    return;
                }
                if (event.key !== 'Tab') return;
                var focusable = rewardFocusableElements();
                if (!focusable.length) {
                    event.preventDefault();
                    return;
                }
                var first = focusable[0];
                var last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            });
        }

        /* ---------- 图片灯箱 & Mermaid ---------- */
        if (typeof ViewImage !== 'undefined') { ViewImage.init('.prose img'); }
        if (typeof window.renderBoldMermaid === 'function') { window.renderBoldMermaid(); }

        /* ---------- 外链安全 ---------- */
        var links = document.links;
        for (var i = 0; i < links.length; i++) {
            if (links[i].hostname != window.location.hostname && links[i].href.indexOf('http') === 0) {
                links[i].target = '_blank';
                links[i].rel = 'noopener noreferrer';
            }
        }

        /* ---------- FAB 折叠逻辑（移动端） ---------- */
        var fabContainer = document.getElementById('fab-container');
        var fabList = document.getElementById('fab-list');
        var fabToggle = document.getElementById('fab-toggle');
        var fabToggleIcon = document.getElementById('fab-toggle-icon');
        var isExpanded = false;

        var ICON_PLUS = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v14M5 12h14"/>';
        var ICON_CLOSE = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>';

        function setFabExtrasFocusable(focusable) {
            if (!fabList) return;
            var extras = fabList.querySelectorAll('.fab-extra');
            for (var i = 0; i < extras.length; i++) {
                if (focusable) {
                    extras[i].removeAttribute('tabindex');
                    extras[i].removeAttribute('aria-hidden');
                } else {
                    extras[i].setAttribute('tabindex', '-1');
                    extras[i].setAttribute('aria-hidden', 'true');
                }
            }
        }

        function setFabState(expanded) {
            isExpanded = expanded;
            if (!fabContainer || !fabList) return;
            fabContainer.classList.toggle('fab-expanded', expanded);
            fabContainer.classList.toggle('fab-collapsed', !expanded);
            fabList.classList.toggle('fab-expanded', expanded);
            fabList.classList.toggle('fab-collapsed', !expanded);
            if (fabToggle) fabToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            if (fabToggleIcon) fabToggleIcon.innerHTML = expanded ? ICON_CLOSE : ICON_PLUS;
            setFabExtrasFocusable(expanded);
        }

        function isMobileViewport() {
            return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
        }

        function initFabState() {
            setFabState(!isMobileViewport());
        }
        if (fabContainer && fabList) {
            initFabState();

            var resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(initFabState, 120);
            });

            if (fabToggle) {
                fabToggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setFabState(!isExpanded);
                });
            }

            document.addEventListener('click', function (e) {
                if (isMobileViewport() && isExpanded && fabToggle
                    && !fabToggle.contains(e.target) && !fabList.contains(e.target)) {
                    setFabState(false);
                }
            });
        }

        /* ---------- 暗黑模式按钮 ---------- */
        var fabTheme = document.getElementById('fab-theme');
        if (fabTheme) {
            fabTheme.addEventListener('click', function () {
                var currentMode = false;
                try { currentMode = localStorage.getItem('darkMode') === 'true'; } catch (err) {}
                try { localStorage.setItem('darkMode', String(!currentMode)); } catch (err) {}
                if (typeof window.applyTheme === 'function') { window.applyTheme(); }
                updateIcons();
                if (typeof window.renderBoldMermaid === 'function') { window.renderBoldMermaid(); }
            });
        }

        /* ---------- 评论表单：防重复提交 + 草稿保留 ---------- */
        var commentForm = document.getElementById('comment-form');
        if (commentForm) {
            var textarea = commentForm.querySelector('textarea[name="text"]');
            var submitBtn = commentForm.querySelector('button[type="submit"]');
            var draftKey = 'bold_comment_draft:' + location.pathname;

            // 恢复未提交成功的草稿（24 小时内），提交失败跳错误页后正文不再丢失
            try {
                var saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
                if (saved && textarea && !textarea.value && saved.v
                    && (Date.now() - saved.t) < 86400000) {
                    textarea.value = saved.v;
                }
            } catch (err) {}

            if (textarea) {
                textarea.addEventListener('input', function () {
                    try {
                        if (textarea.value) {
                            localStorage.setItem(draftKey, JSON.stringify({ t: Date.now(), v: textarea.value }));
                        } else {
                            localStorage.removeItem(draftKey);
                        }
                    } catch (err) {}
                });
            }

            function unlockCommentForm() {
                commentForm.dataset.submitting = '';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            }

            commentForm.addEventListener('submit', function (e) {
                // 双击/连击只提交一次，避免 Turnstile 令牌被二次消费导致 500
                if (commentForm.dataset.submitting === '1') {
                    e.preventDefault();
                    return;
                }
                commentForm.dataset.submitting = '1';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                }
                // 长时间未跳转（网络异常）时恢复可提交，并重置人机验证令牌
                setTimeout(function () {
                    unlockCommentForm();
                    if (window.turnstile && typeof window.turnstile.reset === 'function') {
                        try { window.turnstile.reset(); } catch (err) {}
                    }
                }, 8000);
            });

            // bfcache 返回时恢复按钮状态
            window.addEventListener('pageshow', unlockCommentForm);
        }
    });
})();
