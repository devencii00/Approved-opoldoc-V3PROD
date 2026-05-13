<div class="bg-white border border-slate-200 rounded-[18px] p-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-slate-900">Chatbot Management</h2>
        <span class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Chatbot</span>
    </div>
    <p class="text-xs text-slate-500 mb-4">
        Greeting is static: “How can I help you today?” Configure the menu options below.
    </p>

    <div id="adminChatbotError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>

    <div class="mb-4 flex items-center justify-end">
        <button type="button" id="adminChatbotAddTopOption" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
            <span class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin admin-chatbot-btn-spinner"></span>
            <span class="admin-chatbot-btn-label">Add Option</span>
        </button>
    </div>

    <div class="mb-3 flex flex-col gap-2 md:flex-row md:items-end">
        <div class="flex-1">
            <label for="admin_chatbot_search" class="block text-[0.7rem] text-slate-600 mb-1">Search options</label>
            <input id="admin_chatbot_search" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Search by button text or response">
        </div>
        <div class="w-full md:w-40">
            <label for="admin_chatbot_sort" class="block text-[0.7rem] text-slate-600 mb-1">Sort</label>
            <select id="admin_chatbot_sort" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                <option value="order">Display order</option>
                <option value="alpha">Button text</option>
            </select>
        </div>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)]">
        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-slate-900">Options tree</h3>
                <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">List</span>
            </div>

            <div id="admin_chatbot_options_container" class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                <p class="text-[0.78rem] text-slate-400">Loading options…</p>
            </div>
        </div>

        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-slate-900">Flow preview</h3>
                <span class="text-[0.68rem] text-slate-400 uppercase tracking-widest">Preview</span>
            </div>
            <p class="text-[0.72rem] text-slate-500 mb-3">
                Click an option on the left to preview its response and children.
            </p>
            <div id="admin_chatbot_flow_preview" class="text-[0.78rem] text-slate-700">
                <p class="text-[0.78rem] text-slate-400">No option selected yet.</p>
            </div>
        </div>
    </div>
</div>

<div id="adminChatbotConfirmModal" class="hidden fixed inset-0 z-[70] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] p-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-700">
                <x-lucide-info class="w-[18px] h-[18px]" />
            </div>
            <div class="flex-1">
                <div id="adminChatbotConfirmTitle" class="text-sm font-semibold text-slate-900">Confirm</div>
                <div id="adminChatbotConfirmMessage" class="text-[0.78rem] text-slate-600 mt-0.5">Are you sure?</div>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button id="adminChatbotConfirmCancel" type="button" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
            <button id="adminChatbotConfirmOk" type="button" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-[0.78rem] font-semibold hover:bg-slate-800 disabled:opacity-60 disabled:cursor-not-allowed">Confirm</button>
        </div>
    </div>
</div>

<div id="adminChatbotEditOverlay" class="hidden fixed inset-0 z-[75] bg-slate-900/40 items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white border border-slate-200 shadow-[0_12px_30px_rgba(15,23,42,0.24)] overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <div id="adminChatbotEditTitle" class="text-sm font-semibold text-slate-900">Edit</div>
                <div id="adminChatbotEditSubtitle" class="text-[0.72rem] text-slate-500">Update details.</div>
            </div>
            <button type="button" id="adminChatbotEditClose" class="text-slate-400 hover:text-slate-600">
                <x-lucide-x class="w-[20px] h-[20px]" />
            </button>
        </div>
        <div class="p-5">
            <div id="adminChatbotEditError" class="hidden mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[0.75rem] text-red-700"></div>
            <form id="adminChatbotEditForm" class="space-y-3">
                <div>
                    <label for="adminChatbotEditParent" class="block text-[0.7rem] text-slate-600 mb-1">Parent option</label>
                    <select id="adminChatbotEditParent" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none">
                        <option value="">None (top level)</option>
                    </select>
                </div>
                <div>
                    <label for="adminChatbotEditButtonText" class="block text-[0.7rem] text-slate-600 mb-1">Button text</label>
                    <input id="adminChatbotEditButtonText" type="text" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="e.g. Services">
                </div>
                <div>
                    <label for="adminChatbotEditResponseText" class="block text-[0.7rem] text-slate-600 mb-1">Bot response</label>
                    <textarea id="adminChatbotEditResponseText" rows="4" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="Bot response text"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex items-center gap-2 pt-1">
                        <input id="adminChatbotEditStarting" type="checkbox" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                        <label for="adminChatbotEditStarting" class="text-[0.78rem] text-slate-700 font-semibold">Starting option</label>
                    </div>
                    <div>
                        <label for="adminChatbotEditSortOrder" class="block text-[0.7rem] text-slate-600 mb-1">Display order</label>
                        <input id="adminChatbotEditSortOrder" type="number" min="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 outline-none" placeholder="0">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" id="adminChatbotEditCancel" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-[0.78rem] font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" id="adminChatbotEditSave" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700 transition-colors disabled:opacity-60 disabled:hover:bg-cyan-600">
                        <span id="adminChatbotEditSpinner" class="hidden w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        <span id="adminChatbotEditSaveLabel">Save changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('admin_chatbot_search')
        var sortSelect = document.getElementById('admin_chatbot_sort')
        var optionsContainer = document.getElementById('admin_chatbot_options_container')
        var flowPreview = document.getElementById('admin_chatbot_flow_preview')

        var errorBox = document.getElementById('adminChatbotError')
        var addTopOptionBtn = document.getElementById('adminChatbotAddTopOption')

        var confirmModal = document.getElementById('adminChatbotConfirmModal')
        var confirmTitle = document.getElementById('adminChatbotConfirmTitle')
        var confirmMessage = document.getElementById('adminChatbotConfirmMessage')
        var confirmCancel = document.getElementById('adminChatbotConfirmCancel')
        var confirmOk = document.getElementById('adminChatbotConfirmOk')

        var editOverlay = document.getElementById('adminChatbotEditOverlay')
        var editClose = document.getElementById('adminChatbotEditClose')
        var editCancel = document.getElementById('adminChatbotEditCancel')
        var editTitle = document.getElementById('adminChatbotEditTitle')
        var editSubtitle = document.getElementById('adminChatbotEditSubtitle')
        var editError = document.getElementById('adminChatbotEditError')
        var editForm = document.getElementById('adminChatbotEditForm')
        var editParent = document.getElementById('adminChatbotEditParent')
        var editButtonText = document.getElementById('adminChatbotEditButtonText')
        var editResponseText = document.getElementById('adminChatbotEditResponseText')
        var editStarting = document.getElementById('adminChatbotEditStarting')
        var editSortOrder = document.getElementById('adminChatbotEditSortOrder')
        var editSave = document.getElementById('adminChatbotEditSave')
        var editSpinner = document.getElementById('adminChatbotEditSpinner')
        var editSaveLabel = document.getElementById('adminChatbotEditSaveLabel')

        var optionsFlat = []
        var optionsTree = []
        var selectedOptionId = null
        var confirmCleanup = null
        var editingId = null
        var creatingParentId = null
        var collapsedNodeMap = {}

        function showChatbotError(message) {
            if (!errorBox) return
            errorBox.textContent = message || ''
            if (message) {
                errorBox.classList.remove('hidden')
            } else {
                errorBox.classList.add('hidden')
            }
        }

        function setButtonLoading(button, isLoading, loadingText) {
            if (!button) return
            var spinner = button.querySelector('.admin-chatbot-btn-spinner')
            var labelEl = button.querySelector('.admin-chatbot-btn-label')

            if (!button.dataset.adminChatbotRestoreText) {
                button.dataset.adminChatbotRestoreText = labelEl ? String(labelEl.textContent || '') : String(button.textContent || '')
            }

            if (spinner) spinner.classList.toggle('hidden', !isLoading)
            if (labelEl) {
                labelEl.textContent = isLoading ? String(loadingText || 'Saving...') : String(button.dataset.adminChatbotRestoreText || '')
            } else {
                button.textContent = isLoading ? String(loadingText || 'Saving...') : String(button.dataset.adminChatbotRestoreText || '')
            }
            button.disabled = !!isLoading
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;')
        }

        function showInlineBox(el, message) {
            if (!el) return
            if (!message) {
                el.textContent = ''
                el.classList.add('hidden')
                return
            }
            el.textContent = String(message || '')
            el.classList.remove('hidden')
        }

        function setEditSubmitting(isSubmitting) {
            if (editSave) editSave.disabled = !!isSubmitting
            if (editSpinner) editSpinner.classList.toggle('hidden', !isSubmitting)
            if (editSaveLabel) editSaveLabel.textContent = 'Save changes'
        }

        function showConfirm(config) {
            if (!confirmModal || !confirmOk || !confirmCancel) {
                return Promise.resolve(window.confirm((config && config.message) ? config.message : 'Are you sure?'))
            }

            if (confirmCleanup) {
                try { confirmCleanup() } catch (_) {}
                confirmCleanup = null
            }

            var title = (config && config.title) ? String(config.title) : 'Confirm'
            var message = (config && config.message) ? String(config.message) : 'Are you sure?'
            var confirmText = (config && config.confirmText) ? String(config.confirmText) : 'Confirm'
            var countdownSeconds = (config && typeof config.countdownSeconds === 'number') ? Math.max(0, Math.floor(config.countdownSeconds)) : 0

            if (confirmTitle) confirmTitle.textContent = title
            if (confirmMessage) confirmMessage.textContent = message

            confirmModal.classList.remove('hidden')
            confirmModal.classList.add('flex')

            return new Promise(function (resolve) {
                var resolved = false
                var remaining = countdownSeconds
                var intervalId = null

                function cleanup(result) {
                    if (resolved) return
                    resolved = true
                    if (intervalId) clearInterval(intervalId)
                    confirmModal.classList.add('hidden')
                    confirmModal.classList.remove('flex')
                    confirmOk.disabled = false
                    confirmOk.textContent = confirmText
                    confirmCancel.disabled = false
                    document.removeEventListener('keydown', onKeyDown)
                    confirmModal.removeEventListener('click', onBackdropClick)
                    confirmOk.removeEventListener('click', onOk)
                    confirmCancel.removeEventListener('click', onCancel)
                    confirmCleanup = null
                    resolve(result)
                }

                function onOk(e) {
                    e.preventDefault()
                    if (confirmOk.disabled) return
                    cleanup(true)
                }

                function onCancel(e) {
                    e.preventDefault()
                    cleanup(false)
                }

                function onBackdropClick(e) {
                    if (e.target === confirmModal) cleanup(false)
                }

                function onKeyDown(e) {
                    if (e.key === 'Escape') cleanup(false)
                }

                confirmOk.textContent = confirmText
                if (remaining > 0) {
                    confirmOk.disabled = true
                    confirmOk.textContent = confirmText + ' (' + remaining + 's)'
                    intervalId = setInterval(function () {
                        remaining -= 1
                        if (remaining <= 0) {
                            clearInterval(intervalId)
                            intervalId = null
                            confirmOk.disabled = false
                            confirmOk.textContent = confirmText
                        } else {
                            confirmOk.textContent = confirmText + ' (' + remaining + 's)'
                        }
                    }, 1000)
                }

                confirmCancel.disabled = false
                confirmOk.addEventListener('click', onOk)
                confirmCancel.addEventListener('click', onCancel)
                confirmModal.addEventListener('click', onBackdropClick)
                document.addEventListener('keydown', onKeyDown)

                confirmCleanup = function () {
                    cleanup(false)
                }
            })
        }

        function closeEditModal() {
            if (!editOverlay) return
            editOverlay.classList.add('hidden')
            editOverlay.classList.remove('flex')
            showInlineBox(editError, '')
            setEditSubmitting(false)
            editingId = null
            creatingParentId = null
            if (editParent) editParent.value = ''
            if (editButtonText) editButtonText.value = ''
            if (editResponseText) editResponseText.value = ''
            if (editStarting) editStarting.checked = false
            if (editSortOrder) editSortOrder.value = '0'
        }

        function findOptionById(id) {
            return (optionsFlat || []).find(function (o) {
                return String(o.id) === String(id)
            })
        }

        function syncCollapsedStateFromTree(nodes) {
            function visit(list) {
                (Array.isArray(list) ? list : []).forEach(function (node) {
                    var id = String(node.id)
                    var hasChildren = Array.isArray(node.children) && node.children.length > 0
                    if (hasChildren) {
                        if (typeof collapsedNodeMap[id] === 'undefined') {
                            collapsedNodeMap[id] = false
                        }
                    } else if (Object.prototype.hasOwnProperty.call(collapsedNodeMap, id)) {
                        delete collapsedNodeMap[id]
                    }

                    if (hasChildren) {
                        visit(node.children)
                    }
                })
            }

            visit(nodes)
        }

        function hydrateParentSelect(excludeId) {
            if (!editParent) return
            var current = String(editParent.value || '')
            var html = '<option value="">None (top level)</option>'
            var list = (optionsFlat || []).slice().sort(function (a, b) {
                var ta = String(a.button_text || '').toLowerCase()
                var tb = String(b.button_text || '').toLowerCase()
                if (ta < tb) return -1
                if (ta > tb) return 1
                return (Number(a.id) || 0) - (Number(b.id) || 0)
            })
            list.forEach(function (o) {
                if (excludeId && String(o.id) === String(excludeId)) return
                html += '<option value="' + String(o.id) + '">#' + String(o.id) + ' — ' + escapeHtml(o.button_text || '') + '</option>'
            })
            editParent.innerHTML = html
            editParent.value = current
        }

        function openCreateModal(parentId) {
            if (!editOverlay) return
            editingId = null
            creatingParentId = parentId != null ? String(parentId) : null
            if (editTitle) editTitle.textContent = 'Add option'
            if (editSubtitle) editSubtitle.textContent = creatingParentId ? ('Child of option #' + creatingParentId) : 'Top-level option'
            hydrateParentSelect()
            if (editParent) editParent.value = creatingParentId ? creatingParentId : ''
            if (editButtonText) editButtonText.value = ''
            if (editResponseText) editResponseText.value = ''
            if (editStarting) editStarting.checked = !creatingParentId
            if (editSortOrder) editSortOrder.value = '0'
            showInlineBox(editError, '')
            setEditSubmitting(false)
            editOverlay.classList.remove('hidden')
            editOverlay.classList.add('flex')
            if (editButtonText) editButtonText.focus()
        }

        function openEditModal(option) {
            if (!editOverlay || !option) return
            editingId = String(option.id)
            creatingParentId = null
            if (editTitle) editTitle.textContent = 'Edit option'
            if (editSubtitle) editSubtitle.textContent = 'Option #' + String(option.id)
            hydrateParentSelect(editingId)
            if (editParent) editParent.value = option.parent_id ? String(option.parent_id) : ''
            if (editButtonText) editButtonText.value = option.button_text || ''
            if (editResponseText) editResponseText.value = option.response_text || ''
            if (editStarting) editStarting.checked = !!option.is_starting_option && !option.parent_id
            if (editSortOrder) editSortOrder.value = String(option.sort_order != null ? option.sort_order : 0)
            showInlineBox(editError, '')
            setEditSubmitting(false)
            editOverlay.classList.remove('hidden')
            editOverlay.classList.add('flex')
            if (editButtonText) editButtonText.focus()
        }

        function renderPreview(optionId) {
            if (!flowPreview) return
            var opt = optionId ? findOptionById(optionId) : null
            if (!opt) {
                flowPreview.innerHTML = '<p class="text-[0.78rem] text-slate-400">No option selected yet.</p>'
                return
            }

            var children = (optionsFlat || []).filter(function (o) {
                return String(o.parent_id || '') === String(opt.id)
            }).sort(function (a, b) {
                var ao = Number(a.sort_order) || 0
                var bo = Number(b.sort_order) || 0
                if (ao !== bo) return ao - bo
                return (Number(a.id) || 0) - (Number(b.id) || 0)
            })

            var html = ''
            html += '<div class="mb-3">'
            html += '<div class="flex items-center gap-2 mb-2">'
            html += '<div class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-[0.68rem] text-cyan-800 font-semibold">Option #' + String(opt.id) + '</div>'
            if (opt.is_starting_option) {
                html += '<div class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[0.68rem] text-emerald-700 font-semibold">Starting</div>'
            }
            html += '</div>'
            html += '<p class="text-[0.8rem] font-semibold text-slate-900 mb-1">' + escapeHtml(opt.button_text || '') + '</p>'
            html += '<p class="text-[0.75rem] text-slate-600">' + escapeHtml(opt.response_text || '') + '</p>'
            html += '</div>'

            if (!children.length) {
                html += '<p class="text-[0.75rem] text-slate-500">No child options.</p>'
            } else {
                html += '<div class="space-y-2">'
                children.forEach(function (c) {
                    html += '<div class="rounded-xl border border-slate-200 bg-white px-3 py-2">'
                    html += '<div class="flex items-center justify-between mb-1">'
                    html += '<span class="text-[0.75rem] font-semibold text-slate-800">' + escapeHtml(c.button_text || '') + '</span>'
                    html += '<span class="text-[0.68rem] text-slate-400">#' + String(c.id) + '</span>'
                    html += '</div>'
                    html += '<p class="text-[0.72rem] text-slate-500">' + escapeHtml(c.response_text || '') + '</p>'
                    html += '</div>'
                })
                html += '</div>'
            }

            flowPreview.innerHTML = html
        }

        function renderTree() {
            if (!optionsContainer) return
            var query = searchInput ? String(searchInput.value || '').toLowerCase().trim() : ''
            var sort = sortSelect ? String(sortSelect.value || 'order') : 'order'

            if (!optionsTree || !optionsTree.length) {
                optionsContainer.innerHTML = '<p class="text-[0.78rem] text-slate-400">No options configured yet.</p>'
                renderPreview(null)
                return
            }

            function matches(node) {
                if (!query) return true
                var text = (String(node.button_text || '') + ' ' + String(node.response_text || '')).toLowerCase()
                if (text.indexOf(query) !== -1) return true
                var children = Array.isArray(node.children) ? node.children : []
                return children.some(matches)
            }

            function sortChildren(list) {
                if (!Array.isArray(list)) return []
                var arr = list.slice()
                arr.sort(function (a, b) {
                    if (sort === 'alpha') {
                        var ta = String(a.button_text || '').toLowerCase()
                        var tb = String(b.button_text || '').toLowerCase()
                        if (ta < tb) return -1
                        if (ta > tb) return 1
                        return (Number(a.id) || 0) - (Number(b.id) || 0)
                    }
                    var ao = Number(a.sort_order) || 0
                    var bo = Number(b.sort_order) || 0
                    if (ao !== bo) return ao - bo
                    return (Number(a.id) || 0) - (Number(b.id) || 0)
                })
                return arr
            }

            function renderNode(node, depth) {
                if (!matches(node)) return ''
                var startingBadge = node.is_starting_option ? '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[0.62rem] text-emerald-700 font-semibold">Starting</span>' : ''
                var children = sortChildren(Array.isArray(node.children) ? node.children : [])
                var hasChildren = children.length > 0
                var isCollapsed = !!collapsedNodeMap[String(node.id)]
                var childCountBadge = hasChildren
                    ? '<span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[0.62rem] text-slate-500 font-semibold">' + String(children.length) + ' child' + (children.length === 1 ? '' : 'ren') + '</span>'
                    : ''
                var toggleButton = hasChildren
                    ? '<button type="button" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[0.68rem] font-semibold text-slate-600 hover:bg-slate-100 admin-chatbot-toggle-children" data-option-id="' + String(node.id) + '" aria-expanded="' + String(!isCollapsed) + '">' + (isCollapsed ? 'Show children' : 'Hide children') + '</button>'
                    : ''
                var html = ''
                html += '<div class="rounded-2xl border border-slate-200 bg-white px-3 py-2.5" data-option-id="' + String(node.id) + '" style="margin-left:' + String(Math.max(0, depth) * 10) + 'px">'
                html += '<div class="flex items-start justify-between gap-2">'
                html += '<div class="flex-1">'
                html += '<p class="text-[0.78rem] font-semibold text-slate-900 mb-0.5">' + escapeHtml(node.button_text || '') + '</p>'
                html += '<div class="flex items-center gap-2">'
                html += '<p class="text-[0.68rem] text-slate-400">#' + String(node.id) + ' • Order ' + String(node.sort_order != null ? node.sort_order : 0) + '</p>'
                html += startingBadge
                html += childCountBadge
                html += '</div>'
                html += '</div>'
                html += '<div class="flex items-center gap-2">'
                html += toggleButton
                html += '<button type="button" class="inline-flex items-center gap-1 text-[0.7rem] text-cyan-700 hover:text-cyan-800 font-semibold admin-chatbot-add-child" data-option-id="' + String(node.id) + '"><span class="hidden w-3 h-3 border-2 border-cyan-700/30 border-t-cyan-700 rounded-full animate-spin admin-chatbot-btn-spinner"></span><span class="admin-chatbot-btn-label">Add child</span></button>'
                html += '<button type="button" class="inline-flex items-center gap-1 text-[0.7rem] text-amber-700 hover:text-amber-800 font-semibold admin-chatbot-edit" data-option-id="' + String(node.id) + '"><span class="hidden w-3 h-3 border-2 border-current/30 border-t-current rounded-full animate-spin admin-chatbot-btn-spinner"></span><span class="admin-chatbot-btn-label">Edit</span></button>'
                html += '<button type="button" class="inline-flex items-center gap-1 text-[0.7rem] text-rose-700 hover:text-rose-800 font-semibold admin-chatbot-delete" data-option-id="' + String(node.id) + '"><span class="hidden w-3 h-3 border-2 border-current/30 border-t-current rounded-full animate-spin admin-chatbot-btn-spinner"></span><span class="admin-chatbot-btn-label">Delete</span></button>'
                html += '</div>'
                html += '</div>'
                html += '</div>'

                if (!isCollapsed) {
                    children.forEach(function (c) {
                        html += renderNode(c, depth + 1)
                    })
                }
                return html
            }

            var html = ''
            sortChildren(optionsTree).forEach(function (n) {
                html += renderNode(n, 0)
            })

            optionsContainer.innerHTML = html || '<p class="text-[0.78rem] text-slate-400">No options matched your search.</p>'

            optionsContainer.querySelectorAll('[data-option-id]').forEach(function (card) {
                card.addEventListener('click', function (e) {
                    if (e.target.closest('button')) return
                    var id = card.getAttribute('data-option-id')
                    selectedOptionId = id
                    renderPreview(id)
                })
            })

            optionsContainer.querySelectorAll('.admin-chatbot-add-child').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation()
                    var id = btn.getAttribute('data-option-id')
                    openCreateModal(id)
                })
            })

            optionsContainer.querySelectorAll('.admin-chatbot-toggle-children').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation()
                    var id = btn.getAttribute('data-option-id')
                    if (!id) return
                    collapsedNodeMap[String(id)] = !collapsedNodeMap[String(id)]
                    renderTree()
                })
            })

            optionsContainer.querySelectorAll('.admin-chatbot-edit').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation()
                    var id = btn.getAttribute('data-option-id')
                    var opt = findOptionById(id)
                    if (!opt) return
                    openEditModal(opt)
                })
            })

            optionsContainer.querySelectorAll('.admin-chatbot-delete').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation()
                    var id = btn.getAttribute('data-option-id')
                    if (!id) return
                    showConfirm({
                        title: 'Delete option?',
                        message: 'Delete this option and all child options under it?',
                        confirmText: 'Delete',
                        countdownSeconds: 3
                    }).then(function (ok) {
                        if (!ok) return
                        setButtonLoading(btn, true, 'Deleting...')
                        apiFetch("{{ url('/api/chatbot/options') }}/" + encodeURIComponent(id), { method: 'DELETE' })
                            .then(function (response) {
                                return response.json().catch(function () { return null }).then(function (data) {
                                    return { ok: response.ok, data: data }
                                })
                            })
                            .then(function (result) {
                                if (!result.ok) {
                                    showChatbotError('Failed to delete option.')
                                    return
                                }
                                if (String(selectedOptionId || '') === String(id)) selectedOptionId = null
                                loadOptions()
                            })
                            .catch(function () {
                                showChatbotError('Network error while deleting option.')
                            })
                            .finally(function () {
                                setButtonLoading(btn, false)
                            })
                    })
                })
            })
        }

        function loadOptions() {
            if (!optionsContainer) return
            optionsContainer.innerHTML = '<p class="text-[0.78rem] text-slate-400">Loading options…</p>'
            apiFetch("{{ url('/api/chatbot/options') }}", { method: 'GET' })
                .then(function (response) {
                    return response.json().catch(function () { return null }).then(function (data) {
                        return { ok: response.ok, data: data }
                    })
                })
                .then(function (result) {
                    if (!result.ok) {
                        optionsContainer.innerHTML = '<p class="text-[0.78rem] text-red-500">Failed to load chatbot options.</p>'
                        return
                    }
                    optionsFlat = Array.isArray(result.data && result.data.flat) ? result.data.flat : []
                    optionsTree = Array.isArray(result.data && result.data.tree) ? result.data.tree : []
                    syncCollapsedStateFromTree(optionsTree)
                    hydrateParentSelect(editingId)
                    renderTree()
                    if (selectedOptionId) renderPreview(selectedOptionId)
                })
                .catch(function () {
                    optionsContainer.innerHTML = '<p class="text-[0.78rem] text-red-500">Network error while loading chatbot options.</p>'
                })
        }

        function bindEditModal() {
            if (editClose) editClose.addEventListener('click', closeEditModal)
            if (editCancel) editCancel.addEventListener('click', closeEditModal)
            if (editOverlay) {
                editOverlay.addEventListener('click', function (e) {
                    if (e.target === editOverlay) closeEditModal()
                })
            }

            if (!editForm) return
            editForm.addEventListener('submit', function (e) {
                e.preventDefault()
                if (editSave && editSave.disabled) return
                showInlineBox(editError, '')

                var parentRaw = editParent ? String(editParent.value || '').trim() : ''
                var parentId = parentRaw === '' ? null : parseInt(parentRaw, 10)
                if (!parentId) parentId = null

                var buttonText = editButtonText ? String(editButtonText.value || '').trim() : ''
                var responseText = editResponseText ? String(editResponseText.value || '').trim() : ''
                var isStarting = editStarting ? !!editStarting.checked : false
                var sortOrderRaw = editSortOrder ? String(editSortOrder.value || '').trim() : '0'
                var sortOrder = parseInt(sortOrderRaw, 10)
                if (isNaN(sortOrder) || sortOrder < 0) sortOrder = 0

                if (!buttonText) {
                    showInlineBox(editError, 'Button text is required.')
                    return
                }
                if (!responseText) {
                    showInlineBox(editError, 'Bot response is required.')
                    return
                }

                if (parentId) isStarting = false

                var body = {
                    parent_id: parentId,
                    button_text: buttonText,
                    response_text: responseText,
                    is_starting_option: isStarting,
                    sort_order: sortOrder
                }

                setEditSubmitting(true)
                var method = editingId ? 'PUT' : 'POST'
                var url = editingId
                    ? ("{{ url('/api/chatbot/options') }}/" + encodeURIComponent(editingId))
                    : "{{ url('/api/chatbot/options') }}"

                apiFetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                })
                    .then(function (response) {
                        return response.json().catch(function () { return null }).then(function (data) {
                            return { ok: response.ok, status: response.status, data: data }
                        })
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            var msg = result.data && result.data.message ? result.data.message : 'Failed to save option.'
                            showInlineBox(editError, msg)
                            return
                        }
                        closeEditModal()
                        loadOptions()
                    })
                    .catch(function () {
                        showInlineBox(editError, 'Network error while saving option.')
                    })
                    .finally(function () {
                        setEditSubmitting(false)
                    })
            })
        }

        if (addTopOptionBtn) {
            addTopOptionBtn.addEventListener('click', function () {
                openCreateModal(null)
            })
        }

        bindEditModal()

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                renderTree()
            })
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                renderTree()
            })
        }

        loadOptions()
    })
</script>
