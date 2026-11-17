{{--
    Dialog konfirmasi pengganti confirm() bawaan browser.
    Pakai di <form> dengan atribut:
      data-confirm="Isi pesan"            (wajib, memicu dialog)
      data-confirm-title="Judul"          (opsional)
      data-confirm-ok="Teks tombol ya"    (opsional)
      data-confirm-cancel="Teks batal"    (opsional)
      data-confirm-tone="danger|primary"  (opsional, bawaan danger)
--}}
<dialog id="confirm-dialog" aria-labelledby="confirm-title" aria-describedby="confirm-message"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-surface-container-lowest p-0 text-on-surface shadow-2xl backdrop:bg-slate-900/50 backdrop:backdrop-blur-[2px]">
    <div class="p-space-lg">
        <div class="flex items-start gap-4">
            <span id="confirm-icon" class="flex size-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                <span id="confirm-icon-name" class="material-symbols-outlined">warning</span>
            </span>
            <div class="min-w-0 pt-0.5">
                <h2 id="confirm-title" class="text-title-card font-semibold">Konfirmasi</h2>
                <p id="confirm-message" class="mt-1.5 text-body-md text-on-surface-variant"></p>
            </div>
        </div>
        <div class="mt-space-lg flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="confirm-cancel" class="btn-outline">Batal</button>
            <button type="button" id="confirm-ok" class="btn-danger">Ya, lanjutkan</button>
        </div>
    </div>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('confirm-dialog');
        if (!dialog || typeof dialog.showModal !== 'function') return;

        const $ = (id) => document.getElementById(id);
        const tones = {
            danger: { icon: 'warning', box: 'bg-red-100 text-red-700', ok: 'btn-danger' },
            primary: { icon: 'help', box: 'bg-primary-fixed text-primary', ok: 'btn-primary' },
        };
        let pending = null;

        document.addEventListener('submit', (event) => {
            const form = event.target;
            const message = form.dataset?.confirm;
            if (!message || form.dataset.confirmed === '1') return;

            event.preventDefault();
            pending = form;

            const tone = tones[form.dataset.confirmTone] || tones.danger;
            $('confirm-title').textContent = form.dataset.confirmTitle || 'Konfirmasi';
            $('confirm-message').textContent = message;
            $('confirm-cancel').textContent = form.dataset.confirmCancel || 'Batal';
            $('confirm-ok').textContent = form.dataset.confirmOk || 'Ya, lanjutkan';
            $('confirm-ok').className = tone.ok;
            $('confirm-icon').className = 'flex size-11 shrink-0 items-center justify-center rounded-full ' + tone.box;
            $('confirm-icon-name').textContent = tone.icon;

            dialog.showModal();
            $('confirm-cancel').focus();
        }, true);

        $('confirm-ok').addEventListener('click', () => {
            const form = pending;
            pending = null;
            dialog.close();
            if (form) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
        $('confirm-cancel').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
        dialog.addEventListener('close', () => { pending = null; });
    })();
</script>
