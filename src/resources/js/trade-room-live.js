// Lightweight authenticated polling for shared hosting; never stores chat text locally.
document.querySelectorAll('[data-live-room]').forEach(room => {
    const status = room.querySelector('[data-room-status]');
    const feed = room.querySelector('[data-room-feed]');
    const form = room.querySelector('[data-room-composer]');
    let polling = false, sending = false, stopped = false, timer;
    function state(ok) { status.textContent = ok ? room.dataset.live : room.dataset.offline; status.classList.toggle('is-offline', !ok); }
    async function refresh() {
        if (polling || stopped || document.hidden) return;
        polling = true;
        try {
            const response = await fetch(room.dataset.roomUrl, {headers:{Accept:'application/json'}, credentials:'same-origin', cache:'no-store', signal:AbortSignal.timeout(12000)});
            if ([401,403,404,409,419].includes(response.status)) stopped = true;
            if (!response.ok) throw new Error('refresh');
            const data = await response.json();
            const old = feed.querySelector('.room-messages');
            const nearEnd = !old || old.scrollHeight - old.scrollTop - old.clientHeight < 80;
            const position = old?.scrollTop ?? 0;
            // HTML is rendered by Blade, with escaped message bodies and author names.
            if (feed.innerHTML !== data.html) {
                feed.innerHTML = data.html;
                const list = feed.querySelector('.room-messages');
                if (list) list.scrollTop = nearEnd ? list.scrollHeight : position;
            }
            if (form && !sending) form.querySelector('button').disabled = !data.writable;
            state(true);
        } catch { state(false); }
        finally { polling = false; }
    }
    if (form) form.addEventListener('submit', async event => {
        event.preventDefault();
        if (sending || !form.reportValidity()) return;
        sending = true;
        const button = form.querySelector('button');
        button.disabled = true;
        const input = form.querySelector('textarea');
        const body = input.value;
        try {
            const response = await fetch(form.action, {method:'POST',body:new FormData(form),headers:{Accept:'application/json'},credentials:'same-origin',signal:AbortSignal.timeout(20000)});
            if (!response.ok) throw new Error('send');
            const data = await response.json();
            form.elements.request_key.value = data.request_key;
            if (input.value === body) input.value = '';
            await refresh();
        } catch { status.textContent = room.dataset.error; status.classList.add('is-offline'); }
        finally { sending = false; button.disabled = false; }
    });
    function start() { clearInterval(timer); timer = setInterval(refresh, 5000); refresh(); }
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => clearInterval(timer));
    window.addEventListener('pageshow', start);
    start();
});
