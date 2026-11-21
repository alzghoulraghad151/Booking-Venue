const button = document.getElementById('voice-search');
const status = document.getElementById('voice-status');
const transcript = document.getElementById('voice-transcript');
const label = button.querySelector('[data-voice-label]') ?? button;
const idleLabel = label.textContent;
const address = button.form?.elements.namedItem('address') ?? document.getElementById('address');
const capacity = button.form?.elements.namedItem('capacity') ?? document.getElementById('capacity');

const intro = button.closest('[data-home-voice-widget]')?.querySelector('[data-voice-intro]');

if (intro) {
    const introKey = 'convene-voice-intro-dismissed';
    try {
        intro.hidden = sessionStorage.getItem(introKey) === '1';
    } catch {
        intro.hidden = false;
    }

    const hideIntro = () => {
        intro.hidden = true;
        try {
            sessionStorage.setItem(introKey, '1');
        } catch {
            // The introduction can still be dismissed on this page.
        }
    };

    intro.querySelector('[data-dismiss-voice-intro]').addEventListener('click', () => {
        hideIntro();
        button.focus();
    });
    button.addEventListener('click', hideIntro);
}

let recorder;

button.addEventListener('click', async () => {
    if (recorder?.state === 'recording') {
        button.disabled = true;
        recorder.stop();
        return;
    }

    button.disabled = true;
    transcript.textContent = '';
    status.textContent = 'جاري فتح الميكروفون…';

    let stream;

    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        recorder = new MediaRecorder(stream);
        const chunks = [];

        recorder.addEventListener('dataavailable', (event) => {
            if (event.data.size > 0) {
                chunks.push(event.data);
            }
        });

        recorder.addEventListener('stop', async () => {
            clearTimeout(timer);
            button.removeAttribute('data-recording');
            stream.getTracks().forEach((track) => track.stop());
            button.disabled = true;
            label.textContent = 'ابحث بصوتك';
            status.textContent = 'جاري تحويل التسجيل إلى نص واستخراج الفلاتر…';

            try {
                const type = recorder.mimeType || chunks[0]?.type || 'audio/webm';
                const extension = type.includes('mp4') ? 'mp4'
                    : type.includes('ogg') ? 'ogg' : 'webm';
                const audio = new Blob(chunks, { type });

                if (!audio.size) {
                    throw new Error('التسجيل فارغ. جرّبي مرة ثانية.');
                }

                const body = new FormData();
                body.append('audio', audio, `recording.${extension}`);

                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': button.dataset.csrf,
                    },
                    body,
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.errors?.audio?.[0] ||
                        result.message ||
                        'تعذّر تحويل التسجيل إلى نص.'
                    );
                }

                transcript.textContent = result.text ?? '';

                if (!result.text?.trim()) {
                    status.textContent = 'ما قدرنا نلتقط كلام واضح. جرّبي مرة ثانية.';
                    return;
                }

                if (!result.filters) {
                    status.textContent = 'عرضنا نص التسجيل. عبّي فلاتر البحث يدويًا.';
                    return;
                }

                address.value = result.filters.address ?? result.filters.city ?? '';
                capacity.value = result.filters.capacity ?? '';

                   if (address.value.trim() && capacity.value) {
    status.textContent = 'جاري البحث عن المساحات المناسبة…';

    const form = address.form;

    for (const [name, value] of Object.entries({
        voice: '1',
        lang: result.filters.language === 'en' ? 'en' : 'ar',
    })) {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = name;
        field.value = value;
        form.append(field);
    }

    try {
        sessionStorage.setItem('play-space-summary-once', '1');
    } catch {
        // The manual play button remains available.
    }

    form.requestSubmit();
    return;
}

status.textContent = 'كمّلي الحقول الفارغة، وبعدها اضغطي بحث.';
            } catch (error) {
                status.textContent = error.message;
            } finally {
                label.textContent = idleLabel;
                button.disabled = false;
            }
        }, { once: true });

        recorder.start();
        button.setAttribute('data-recording', '');

        const timer = setTimeout(() => {
            if (recorder.state === 'recording') {
                recorder.stop();
            }
        }, 10000);

        label.textContent = 'إيقاف وإرسال التسجيل';
        button.disabled = false;
            status.textContent = 'جاري التسجيل. احكي الموقع وعدد الأشخاص.';
    } catch {
        stream?.getTracks().forEach((track) => track.stop());
        status.textContent = 'تعذّر فتح الميكروفون. تأكدي من السماح باستخدامه.';
        button.disabled = false;
    }
});
