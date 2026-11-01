document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea.dispatch-content').forEach((field) => {
        const form = field.form;
        const modal = field.closest('.modal');
        const wrapper = document.createElement('div');
        wrapper.className = 'dispatch-editor';
        wrapper.innerHTML = `
            <div class="dispatch-editor-toolbar" role="group" aria-label="Announcement formatting">
                <button type="button" data-command="bold" aria-label="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" aria-label="Italic"><em>I</em></button>
                <button type="button" data-command="formatBlock" data-value="h3">Heading</button>
                <button type="button" data-command="formatBlock" data-value="p">Paragraph</button>
                <button type="button" data-command="insertUnorderedList" aria-label="Bulleted list"><i class="bi bi-list-ul" aria-hidden="true"></i> Bullets</button>
                <button type="button" data-command="insertOrderedList" aria-label="Numbered list"><i class="bi bi-list-ol" aria-hidden="true"></i> Numbered</button>
            </div>
            <div class="dispatch-editor-area form-control" contenteditable="true" role="textbox" aria-multiline="true" aria-required="true" data-placeholder="Write your announcement here..."></div>
            <div class="dispatch-editor-error" role="alert" hidden>Please write your announcement before continuing.</div>`;
        field.before(wrapper);
        field.hidden = true;
        field.required = false;
        const editor = wrapper.querySelector('.dispatch-editor-area');
        const error = wrapper.querySelector('.dispatch-editor-error');
        editor.id = `${field.id}-visual`;
        field.parentElement.querySelector(`label[for="${field.id}"]`).htmlFor = editor.id;
        let dirty = false;
        let savedRange = null;

        function safePreview(html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            doc.querySelectorAll('script, style, iframe, object, embed, svg, math, form, input, button, textarea').forEach((node) => node.remove());
            doc.body.querySelectorAll('*').forEach((node) => {
                for (const attr of Array.from(node.attributes)) {
                    const name = attr.name.toLowerCase();
                    if (name.startsWith('on') || name === 'style' || name === 'srcdoc') node.removeAttribute(attr.name);
                    if (['href', 'src'].includes(name) && (/^[\s\x00-\x1f]*[a-z][a-z0-9+.-]*:/i.test(attr.value) && !/^https?:/i.test(attr.value.trim()) || /^\s*\/\//.test(attr.value))) node.removeAttribute(attr.name);
                }
            });
            return doc.body.innerHTML;
        }

        function loadContent() {
            editor.innerHTML = safePreview(field.value);
            dirty = false;
            savedRange = null;
            error.hidden = true;
            editor.removeAttribute('aria-invalid');
        }
        modal.addEventListener('show.bs.modal', loadContent);
        editor.addEventListener('input', () => { dirty = true; error.hidden = true; editor.removeAttribute('aria-invalid'); });
        document.addEventListener('selectionchange', () => {
            const selection = window.getSelection();
            if (selection.rangeCount && editor.contains(selection.anchorNode)) savedRange = selection.getRangeAt(0).cloneRange();
        });
        wrapper.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                editor.focus();
                if (savedRange) {
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(savedRange);
                }
                document.execCommand(button.dataset.command, false, button.dataset.value || null);
                dirty = true;
                error.hidden = true;
            });
        });
        form.addEventListener('submit', (event) => {
            if (!editor.textContent.trim()) {
                event.preventDefault();
                error.hidden = false;
                editor.setAttribute('aria-invalid', 'true');
                editor.focus();
                return;
            }
            if (dirty) field.value = editor.innerHTML;
        });
    });
});
