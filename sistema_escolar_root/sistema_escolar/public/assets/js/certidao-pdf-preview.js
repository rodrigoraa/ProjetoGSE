(() => {
    function formatarTamanho(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return 'Tamanho não informado';
        }

        const unidades = ['bytes', 'KB', 'MB', 'GB'];
        const indice = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), unidades.length - 1);
        const valor = bytes / Math.pow(1024, indice);
        const casas = indice === 0 ? 0 : 1;

        return valor.toFixed(casas).replace('.', ',') + ' ' + unidades[indice];
    }

    function iniciarPreviewPdf(input) {
        const container = input.closest('.form-group');
        const preview = container ? container.querySelector('[data-pdf-preview]') : null;

        if (!preview) {
            return;
        }

        const frame = preview.querySelector('[data-pdf-frame]');
        const nome = preview.querySelector('[data-pdf-name]');
        const meta = preview.querySelector('[data-pdf-meta]');
        const botaoRemover = preview.querySelector('[data-pdf-clear]');
        const linkAbrir = preview.querySelector('[data-pdf-open]');
        let urlTemporaria = null;

        function limparPreview() {
            if (urlTemporaria) {
                URL.revokeObjectURL(urlTemporaria);
                urlTemporaria = null;
            }

            frame.removeAttribute('src');
            if (linkAbrir) {
                linkAbrir.removeAttribute('href');
                linkAbrir.hidden = true;
            }
            nome.textContent = '';
            meta.textContent = '';
            preview.hidden = true;
        }

        input.addEventListener('change', () => {
            limparPreview();

            const arquivo = input.files && input.files[0];
            if (!arquivo) {
                input.setCustomValidity('');
                return;
            }

            const extensaoPdf = arquivo.name.toLowerCase().endsWith('.pdf');
            const mimePdf = arquivo.type === 'application/pdf' || arquivo.type === 'application/x-pdf';

            if (!extensaoPdf && !mimePdf) {
                input.value = '';
                input.setCustomValidity('Selecione um arquivo no formato PDF.');
                input.reportValidity();
                return;
            }

            input.setCustomValidity('');
            urlTemporaria = URL.createObjectURL(arquivo);
            frame.src = urlTemporaria;
            if (linkAbrir) {
                linkAbrir.href = urlTemporaria;
                linkAbrir.hidden = false;
            }
            nome.textContent = arquivo.name;
            meta.textContent = formatarTamanho(arquivo.size);
            preview.hidden = false;
        });

        botaoRemover.addEventListener('click', () => {
            input.value = '';
            input.setCustomValidity('');
            limparPreview();
            input.focus();
        });

        window.addEventListener('pagehide', () => {
            if (urlTemporaria) {
                URL.revokeObjectURL(urlTemporaria);
            }
        });
    }

    document.querySelectorAll('[data-pdf-input]').forEach(iniciarPreviewPdf);
})();
