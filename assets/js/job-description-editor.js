document.querySelectorAll('.description-toolbar button').forEach((button) => {
    button.addEventListener('click', () => {
        const field = document.getElementById(button.dataset.target);
        const start = field.selectionStart;
        const end = field.selectionEnd;
        const selected = field.value.slice(start, end);
        if (button.dataset.format === 'bold') {
            const text = selected || 'bold text';
            field.setRangeText(`**${text}**`, start, end, 'select');
            field.setSelectionRange(start + 2, start + 2 + text.length);
        } else {
            const lineStart = field.value.lastIndexOf('\n', start - 1) + 1;
            field.setRangeText('• ', lineStart, lineStart, 'end');
        }
        field.focus();
        field.dispatchEvent(new Event('input', { bubbles: true }));
    });
});
