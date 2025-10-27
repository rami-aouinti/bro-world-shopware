document.addEventListener('click', (event) => {
    const copyTrigger = event.target.closest('[data-copy-target]');
    if (!copyTrigger) {
        return;
    }

    const targetSelector = copyTrigger.getAttribute('data-copy-target');
    if (!targetSelector) {
        return;
    }

    const input = document.querySelector(targetSelector);
    if (!input) {
        return;
    }

    input.select();
    document.execCommand('copy');
});
