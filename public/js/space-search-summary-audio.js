const audio = document.getElementById('voice-summary-audio');
const playButton = document.getElementById('voice-summary-play');
const status = document.getElementById('voice-summary-status');

async function playSummary() {
    playButton.hidden = true;
    status.textContent = '';

    try {
        await audio.play();
    } catch (error) {
        if (error.name === 'NotAllowedError') {
            playButton.hidden = false;
            return;
        }

        status.textContent = 'تعذّر تشغيل الملخّص الصوتي.';
    }
}

playButton.addEventListener('click', playSummary);
playSummary();