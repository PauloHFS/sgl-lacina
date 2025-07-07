import Prism from 'prismjs';
import React, { useEffect, useRef } from 'react';

// Importar apenas as linguagens essenciais para evitar problemas de dependências
import 'prismjs/components/prism-bash.min.js';
import 'prismjs/components/prism-css.min.js';
import 'prismjs/components/prism-javascript.min.js';
import 'prismjs/components/prism-json.min.js';
import 'prismjs/components/prism-markup.min.js'; // HTML
import 'prismjs/components/prism-python.min.js';
import 'prismjs/components/prism-sql.min.js';
import 'prismjs/components/prism-typescript.min.js';

interface WysiwygViewerProps {
    content: string | null;
    className?: string;
}

const WysiwygViewer: React.FC<WysiwygViewerProps> = ({
    content,
    className = '',
}) => {
    const viewerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (viewerRef.current && content) {
            // Aplicar syntax highlighting a todos os blocos de código
            const codeBlocks = viewerRef.current.querySelectorAll('pre code');
            codeBlocks.forEach((block) => {
                const codeElement = block as HTMLElement;

                // Detectar linguagem automaticamente se não estiver definida
                if (!codeElement.className.includes('language-')) {
                    const detectedLanguage = detectLanguage(
                        codeElement.textContent || '',
                    );
                    codeElement.className = `language-${detectedLanguage}`;
                }

                // Aplicar highlighting com verificação de segurança
                try {
                    const language =
                        codeElement.className.match(/language-(\w+)/)?.[1] ||
                        'text';

                    // Lista de linguagens seguras que sabemos que funcionam
                    const safeLangs = [
                        'javascript',
                        'typescript',
                        'css',
                        'markup',
                        'json',
                        'sql',
                        'python',
                        'bash',
                        'text',
                    ];

                    if (
                        safeLangs.includes(language) &&
                        Prism.languages[language]
                    ) {
                        Prism.highlightElement(codeElement);
                    } else {
                        // Se a linguagem não for segura, usar texto simples
                        codeElement.className = 'language-text';
                        console.log(
                            `Linguagem '${language}' não suportada, usando text`,
                        );
                    }
                } catch (error) {
                    console.warn('Erro ao aplicar syntax highlighting:', error);
                    // Em caso de erro, manter o código sem highlighting
                    codeElement.className = 'language-text';
                }

                // Adicionar label da linguagem
                const preElement = codeElement.parentElement as HTMLPreElement;
                if (preElement && !preElement.hasAttribute('data-language')) {
                    const language =
                        codeElement.className.match(/language-(\w+)/)?.[1] ||
                        'text';
                    preElement.setAttribute('data-language', language);
                }

                // Adicionar botão de cópia
                addCopyButton(preElement);
            });

            // Aplicar highlighting a código inline também
            const inlineCodes =
                viewerRef.current.querySelectorAll('code:not(pre code)');
            inlineCodes.forEach((code) => {
                const codeElement = code as HTMLElement;
                if (!codeElement.className.includes('language-')) {
                    const detectedLanguage = detectLanguage(
                        codeElement.textContent || '',
                    );
                    codeElement.className = `language-${detectedLanguage}`;
                    if (detectedLanguage !== 'text') {
                        Prism.highlightElement(codeElement);
                    }
                }
            });
        }
    }, [content]);

    const detectLanguage = (code: string): string => {
        // Padrões simples para detecção automática de linguagem
        if (
            code.includes('const ') ||
            code.includes('let ') ||
            code.includes('var ') ||
            code.includes('=>')
        )
            return 'javascript';
        if (
            code.includes('interface ') ||
            code.includes(': string') ||
            code.includes(': number')
        )
            return 'typescript';
        if (
            /SELECT\s+.+\s+FROM\s+/i.test(code) ||
            /INSERT\s+INTO\s+/i.test(code)
        )
            return 'sql';
        if (
            code.includes('<div') ||
            code.includes('<html') ||
            code.includes('<body')
        )
            return 'markup';
        if (code.includes('{') && code.includes('"') && code.includes(':'))
            return 'json';
        if (
            code.includes('def ') ||
            (code.includes('import ') && code.includes('from '))
        )
            return 'python';
        if (
            code.includes('#!/bin/') ||
            code.includes('echo ') ||
            code.includes('ls ')
        )
            return 'bash';
        if (
            code.includes('.class') ||
            code.includes('#id') ||
            code.includes('@media')
        )
            return 'css';
        // Remover detecção de PHP por enquanto para evitar problemas

        return 'text';
    };

    const addCopyButton = (preElement: HTMLPreElement) => {
        if (preElement.querySelector('.copy-button')) return; // Já tem botão

        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';

        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button btn btn-xs btn-ghost';
        copyButton.innerHTML = '📋 Copiar';
        copyButton.setAttribute('title', 'Copiar código');

        copyButton.addEventListener('click', async () => {
            const codeElement = preElement.querySelector('code');
            if (codeElement) {
                try {
                    await navigator.clipboard.writeText(
                        codeElement.textContent || '',
                    );
                    copyButton.innerHTML = '✅ Copiado!';
                    copyButton.classList.add('copied');

                    setTimeout(() => {
                        copyButton.innerHTML = '📋 Copiar';
                        copyButton.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Erro ao copiar:', err);
                    copyButton.innerHTML = '❌ Erro';
                    setTimeout(() => {
                        copyButton.innerHTML = '📋 Copiar';
                    }, 2000);
                }
            }
        });

        // Mover o pre para dentro do wrapper
        preElement.parentNode?.insertBefore(wrapper, preElement);
        wrapper.appendChild(preElement);
        wrapper.appendChild(copyButton);
    };

    if (!content) {
        return (
            <div className={`wysiwyg-viewer-empty ${className}`}>
                <p className="text-base-content/60 py-4 text-center italic">
                    📝 Nenhum conteúdo disponível
                </p>
            </div>
        );
    }

    return (
        <div
            ref={viewerRef}
            className={`wysiwyg-viewer ${className}`}
            dangerouslySetInnerHTML={{ __html: content }}
        />
    );
};

export default WysiwygViewer;
