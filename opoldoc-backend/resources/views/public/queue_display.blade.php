<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Queue Display — Opol Doctors Clinic</title>
    @vite('resources/css/app.css')
       <link rel="stylesheet" href="{{ asset('assets/fonts/css/stylefont.css') }}">
         <link rel="icon" type="image/x-icon" href="/images/opoldoc-weblog.ico">
    <style>
        .scrollbar-hidden { scrollbar-width: none; }
        .scrollbar-hidden::-webkit-scrollbar { width: 0; height: 0; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white">
<div class="min-h-screen flex flex-col">
    <div class="flex items-center justify-between px-6 md:px-10 py-4 border-b border-slate-800">
        <div>
            <div class="text-[0.75rem] text-slate-400 uppercase tracking-widest">Opol Clinic</div>
            <div class="text-lg md:text-xl font-semibold text-white">Queue Display</div>
            <div class="text-[0.75rem] text-slate-400 mt-0.5">
                <span id="queueDisplayDateLabel"></span>
                @if ($doctorId)
                    · Doctor filter: #{{ $doctorId }}
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button id="queueDisplayFullscreen" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800 text-slate-100 text-[0.78rem] font-semibold hover:bg-slate-700">
                <x-lucide-fullscreen class="w-[18px] h-[18px]" />
                Full screen
            </button>
            <button id="queueDisplayRefresh" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-cyan-600 text-white text-[0.78rem] font-semibold hover:bg-cyan-700">
                <x-lucide-refresh-cw class="w-[18px] h-[18px]" />
                Refresh
            </button>
        </div>
    </div>

    <div class="flex-1 grid grid-cols-1 lg:grid-cols-3">
        <div class="lg:col-span-2 p-6 md:p-10 flex items-center justify-center">
            <div class="w-full max-w-2xl">
                <div class="text-[0.85rem] text-cyan-300 uppercase tracking-[0.3em] mb-3">Now serving</div>
                <div id="queueNowServingGrid" class="grid grid-cols-1 gap-4"></div>
                <div id="queueNowServingEmpty" class="hidden rounded-3xl bg-slate-900/60 border border-slate-700 px-6 md:px-8 py-8 text-center text-slate-300">
                    No queue is currently being served.
                </div>
                <div id="queueDisplayError" class="hidden mt-4 rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-[0.85rem] text-red-200"></div>
            </div>
        </div>

        <div class="border-t lg:border-t-0 lg:border-l border-slate-800 bg-slate-950/60 p-6 md:p-8">
            <div class="flex items-center justify-between mb-3">
                <div class="text-[0.85rem] text-slate-300 uppercase tracking-[0.25em]">Next patients</div>
                <div id="queueNextMeta" class="text-[0.75rem] text-slate-500"></div>
            </div>
            <div id="queueNextList" class="space-y-3 max-h-[70vh] overflow-y-auto scrollbar-hidden"></div>
        </div>
    </div>
</div>

<script>
    (function () {
        function localDateIso() {
            var now = new Date();
            var y = now.getFullYear();
            var m = String(now.getMonth() + 1).padStart(2, '0');
            var d = String(now.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        }
        var date = localDateIso();
        
        var doctorId = @json($doctorId);

        var dateLabel = document.getElementById('queueDisplayDateLabel');
        var btnRefresh = document.getElementById('queueDisplayRefresh');
        var btnFullscreen = document.getElementById('queueDisplayFullscreen');

        var errorBox = document.getElementById('queueDisplayError');
        var nowEmpty = document.getElementById('queueNowServingEmpty');
        var nowGrid = document.getElementById('queueNowServingGrid');
        var nextList = document.getElementById('queueNextList');
        var nextMeta = document.getElementById('queueNextMeta');

        function showError(message) {
            if (!errorBox) return;
            errorBox.textContent = message || '';
            errorBox.classList.toggle('hidden', !message);
        }

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function pad3(n) {
            var s = String(n == null ? '' : n);
            if (!s) return '---';
            while (s.length < 3) s = '0' + s;
            return s;
        }

        function displayQueueLabel(item) {
            if (item && item.queue_code) {
                return String(item.queue_code);
            }
            if (item && item.queue_number != null) {
                return pad3(item.queue_number);
            }
            return '---';
        }

        function roomLabel(roomNumber) {
            if (roomNumber == null) return '';
            var n = parseInt(roomNumber, 10);
            if (isNaN(n) || n < 1) return '';
            return '[ROOM ' + n + ']';
        }

        function waitLabel(minutes) {
            if (minutes == null) return '';
            var n = parseInt(minutes, 10);
            if (isNaN(n) || n < 1) return '';
            return 'Est. waiting ' + n + ' mins';
        }

        function deadlineStorageKey(queueId) {
            return 'queueDeadline:' + String(date || '') + ':' + String(queueId || '');
        }

        function getOrInitDeadline(queueId, estimatedMinutes) {
            var id = String(queueId || '');
            if (!id) return null;
            var key = deadlineStorageKey(id);
            var existing = null;
            try {
                existing = window.localStorage ? window.localStorage.getItem(key) : null;
            } catch (e) {
                existing = null;
            }

            var parsed = existing ? parseInt(existing, 10) : NaN;
            var nowMs = Date.now();
            var est = estimatedMinutes == null ? null : parseInt(estimatedMinutes, 10);
            var proposed = (est != null && !isNaN(est) && est >= 0) ? (nowMs + (est * 60 * 1000)) : null;

            var deadline = (!isNaN(parsed) && parsed > 0) ? parsed : proposed;
            if (deadline == null) return null;
            if (proposed != null && deadline > proposed) {
                deadline = proposed;
            }

            try {
                if (window.localStorage) {
                    window.localStorage.setItem(key, String(deadline));
                }
            } catch (e) {
            }

            return deadline;
        }

        function formatCountdownMinutes(msRemaining) {
            var ms = msRemaining <= 0 ? 0 : msRemaining;
            var mins = Math.ceil(ms / (60 * 1000));
            if (!mins || isNaN(mins) || mins < 0) mins = 0;
            return mins;
        }

        function updateCountdowns() {
            if (!nextList) return;
            var els = nextList.querySelectorAll('.queue-wait-timer');
            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                var qid = el.getAttribute('data-queue-id');
                var est = el.getAttribute('data-est-minutes');
                var deadline = getOrInitDeadline(qid, est);
                if (!deadline) {
                    el.textContent = '';
                    continue;
                }
                var remaining = deadline - Date.now();
                var minsLeft = formatCountdownMinutes(remaining);
                el.textContent = 'Est. waiting: ' + minsLeft + ' min';
            }
        }

        function render(payload) {
            if (dateLabel) dateLabel.textContent = 'Date: ' + (payload && payload.date ? payload.date : date);

            var serving = payload && Array.isArray(payload.now_serving) ? payload.now_serving : [];
            if (nowGrid) {
                if (!serving.length) {
                    nowGrid.innerHTML = '';
                } else {
                    nowGrid.innerHTML = serving.map(function (item) {
                        var qn = displayQueueLabel(item);
                        var patient = item && item.patient && item.patient.name ? item.patient.name : 'Patient';
                        var doctor = item && item.doctor && item.doctor.name ? item.doctor.name : '—';
                        var room = roomLabel(item && item.room_number != null ? item.room_number : null);

                      return '' +
    '<div class="rounded-3xl bg-slate-900/60 border border-slate-700 px-8 py-6 shadow-[0_0_40px_rgba(8,47,73,0.7)] flex items-center justify-between gap-6">' +
        '<div>' +
            '<div class="text-[0.75rem] text-slate-400 uppercase tracking-widest mb-1">Queue</div>' +
            '<div class="text-6xl md:text-7xl font-serif font-bold text-white tracking-[0.12em] whitespace-nowrap">' + escapeHtml(qn) + '</div>' +
        '</div>' +
        '<div class="text-right shrink-0">' +
            '<div class="text-[0.75rem] text-slate-500 uppercase tracking-widest mb-1">Room</div>' +
            '<div class="text-4xl md:text-5xl font-serif font-bold text-cyan-300">' + (item && item.room_number != null ? escapeHtml(String(item.room_number)) : '—') + '</div>' +
        '</div>' +
    '</div>';
                    }).join('');
                }
            }

            if (nowEmpty) {
                nowEmpty.classList.toggle('hidden', serving.length > 0);
            }

            var next = payload && Array.isArray(payload.next) ? payload.next : [];
            var counts = payload && payload.counts ? payload.counts : null;
            if (nextMeta) {
                var waitingCount = counts && counts.waiting != null ? String(counts.waiting) : '';
                nextMeta.textContent = waitingCount ? (waitingCount + ' waiting') : '';
            }

            if (!nextList) return;
            if (!next.length) {
                nextList.innerHTML = '<div class="rounded-2xl border border-slate-800 bg-slate-900/40 px-4 py-4 text-[0.85rem] text-slate-300">No patients waiting.</div>';
                return;
            }

            nextList.innerHTML = next.map(function (q) {
                var qn = displayQueueLabel(q);
                var patient = q && q.patient && q.patient.name ? q.patient.name : 'Patient';
                var doctor = q && q.doctor && q.doctor.name ? q.doctor.name : 'Doctor';
                var qid = q && q.queue_id != null ? String(q.queue_id) : '';
                var wait = waitLabel(q && q.estimated_wait_minutes != null ? q.estimated_wait_minutes : null);
                return '' +
                    '<div class="rounded-2xl border border-slate-800 bg-slate-900/40 px-4 py-3 flex items-center justify-between gap-4">' +
                        '<div>' +
                            '<div class="text-[0.75rem] text-slate-400 mb-1">Queue #' + escapeHtml(qn) + '</div>' +
                            '<div class="text-[0.95rem] text-white font-semibold">' + escapeHtml(patient) + '</div>' +
                            '<div class="text-[0.75rem] text-slate-400 mt-0.5">' + escapeHtml(doctor) + '</div>' +
                        '</div>' +
                        '<div class="text-right text-[0.7rem] text-slate-400">' +
                            (qid && (q && q.estimated_wait_minutes != null) ? ('<div class="queue-wait-timer" data-queue-id="' + escapeHtml(qid) + '" data-est-minutes="' + escapeHtml(String(q.estimated_wait_minutes)) + '">' + escapeHtml(wait) + '</div>') : '') +
                            (q && q.priority_level != null ? ('<div>Priority ' + escapeHtml(q.priority_level) + '</div>') : '') +
                        '</div>' +
                    '</div>';
            }).join('');

            updateCountdowns();
        }

        function load() {
            showError('');
            var url = "{{ route('queue.display.data') }}" + '?date=' + encodeURIComponent(date || '');
            if (doctorId) {
                url += '&doctor_id=' + encodeURIComponent(doctorId);
            }
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok) {
                        showError('Failed to load queue display.');
                        return;
                    }
                    render(result.data);
                })
                .catch(function () {
                    showError('Network error while loading queue display.');
                });
        }

        if (btnRefresh) {
            btnRefresh.addEventListener('click', load);
        }
        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', function () {
                try {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                } catch (_) {
                }
            });
        }

        load();
        setInterval(load, 5000);
        setInterval(updateCountdowns, 60000);
    })();
</script>
</body>
</html>
