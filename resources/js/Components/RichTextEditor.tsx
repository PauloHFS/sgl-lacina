import Color from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Table from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import TextAlign from '@tiptap/extension-text-align';
import TextStyle from '@tiptap/extension-text-style';
import Typography from '@tiptap/extension-typography';
import Underline from '@tiptap/extension-underline';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import { useCallback } from 'react';

interface RichTextEditorProps {
    content?: string;
    onChange?: (content: string) => void;
    placeholder?: string;
    className?: string;
}

const RichTextEditor = ({
    content = '',
    onChange,
    placeholder = 'Digite aqui...',
    className = '',
}: RichTextEditorProps) => {
    const editor = useEditor({
        extensions: [
            StarterKit.configure({
                bulletList: {
                    keepMarks: true,
                    keepAttributes: false,
                },
                orderedList: {
                    keepMarks: true,
                    keepAttributes: false,
                },
            }),
            Typography,
            TextStyle,
            Color.configure({
                types: ['textStyle'],
            }),
            Highlight.configure({
                multicolor: true,
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: 'text-primary underline',
                },
            }),
            Underline,
            Table.configure({
                resizable: true,
            }),
            TableRow,
            TableHeader,
            TableCell,
            Image.configure({
                inline: true,
                allowBase64: true,
            }),
        ],
        content,
        onUpdate: ({ editor }) => {
            onChange?.(editor.getHTML());
        },
        editorProps: {
            attributes: {
                class: 'wysiwyg-content focus:outline-none min-h-[280px] p-4',
                placeholder,
            },
        },
    });



    const setLink = useCallback(() => {
        const previousUrl = editor?.getAttributes('link').href;
        const url = window.prompt('URL:', previousUrl);

        // cancelled
        if (url === null) {
            return;
        }

        // empty
        if (url === '') {
            editor?.chain().focus().extendMarkRange('link').unsetLink().run();
            return;
        }

        // update link
        editor
            ?.chain()
            .focus()
            .extendMarkRange('link')
            .setLink({ href: url })
            .run();
    }, [editor]);

    const addImage = useCallback(() => {
        const url = window.prompt('URL da imagem:');

        if (url) {
            editor?.chain().focus().setImage({ src: url }).run();
        }
    }, [editor]);

    const insertTable = useCallback(() => {
        editor
            ?.chain()
            .focus()
            .insertTable({ rows: 3, cols: 3, withHeaderRow: true })
            .run();
    }, [editor]);

    const setTextColor = useCallback(
        (color: string) => {
            editor?.chain().focus().setColor(color).run();

        },
        [editor],
    );

    const setHighlight = useCallback(
        (color: string) => {
            editor?.chain().focus().setHighlight({ color }).run();
        },
        [editor],
    );

    if (!editor) {
        return (
            <div className="bg-base-200 min-h-[250px] animate-pulse rounded-lg" />
        );
    }

    return (
        <div className={`form-control ${className}`}>
            {/* Toolbar */}
            <div className="bg-base-200 border-base-300 flex flex-wrap gap-2 rounded-t-lg border p-3">
                {/* Formatação de texto */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleBold().run()
                        }
                        disabled={
                            !editor.can().chain().focus().toggleBold().run()
                        }
                        className={`btn btn-sm ${editor.isActive('bold') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Negrito (Ctrl+B)"
                    >
                        <strong>B</strong>
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleItalic().run()
                        }
                        disabled={
                            !editor.can().chain().focus().toggleItalic().run()
                        }
                        className={`btn btn-sm ${editor.isActive('italic') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Itálico (Ctrl+I)"
                    >
                        <em>I</em>
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleUnderline().run()
                        }
                        disabled={
                            !editor
                                .can()
                                .chain()
                                .focus()
                                .toggleUnderline()
                                .run()
                        }
                        className={`btn btn-sm ${editor.isActive('underline') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Sublinhado (Ctrl+U)"
                    >
                        <u>U</u>
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleStrike().run()
                        }
                        disabled={
                            !editor.can().chain().focus().toggleStrike().run()
                        }
                        className={`btn btn-sm ${editor.isActive('strike') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Riscado"
                    >
                        <s>S</s>
                    </button>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Cores */}
                <div className="flex gap-1">
                    <div className="dropdown">
                        <button
                            type="button"
                            tabIndex={0}
                            className="btn btn-sm btn-ghost"
                            title="Cor do texto"
                        >
                            🎨
                        </button>
                        <div
                            tabIndex={0}
                            className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                        >
                            <div className="grid grid-cols-6 gap-1 p-2">
                                {[
                                    '#000000',
                                    '#ef4444',
                                    '#f97316',
                                    '#eab308',
                                    '#22c55e',
                                    '#3b82f6',
                                    '#8b5cf6',
                                    '#ec4899',
                                ].map((color) => (
                                    <button
                                        key={color}
                                        type="button"
                                        className="border-base-300 h-6 w-6 rounded border transition-transform hover:scale-110"
                                        style={{ backgroundColor: color }}
                                        onClick={() => setTextColor(color)}
                                        title={`Cor: ${color}`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                    <div className="dropdown">
                        <button
                            type="button"
                            tabIndex={0}
                            className={`btn btn-sm ${editor.isActive('highlight') ? 'btn-primary' : 'btn-ghost'}`}
                            title="Destaque"
                        >
                            🖍️
                        </button>
                        <div
                            tabIndex={0}
                            className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                        >
                            <div className="grid grid-cols-6 gap-1 p-2">
                                {[
                                    '#fef3c7',
                                    '#fed7d7',
                                    '#d1fae5',
                                    '#dbeafe',
                                    '#e0e7ff',
                                    '#f3e8ff',
                                ].map((color) => (
                                    <button
                                        key={color}
                                        type="button"
                                        className="border-base-300 h-6 w-6 rounded border transition-transform hover:scale-110"
                                        style={{ backgroundColor: color }}
                                        onClick={() => setHighlight(color)}
                                        title={`Destaque: ${color}`}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Títulos */}
                <div className="flex gap-1">
                    <div className="dropdown">
                        <button
                            type="button"
                            tabIndex={0}
                            className="btn btn-sm btn-ghost"
                            title="Formatação"
                        >
                            ¶
                        </button>
                        <ul
                            tabIndex={0}
                            className="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow"
                        >
                            <li>
                                <button
                                    type="button"
                                    onClick={() =>
                                        editor
                                            .chain()
                                            .focus()
                                            .setParagraph()
                                            .run()
                                    }
                                    className={
                                        editor.isActive('paragraph')
                                            ? 'active'
                                            : ''
                                    }
                                >
                                    Parágrafo
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    onClick={() =>
                                        editor
                                            .chain()
                                            .focus()
                                            .toggleHeading({ level: 1 })
                                            .run()
                                    }
                                    className={
                                        editor.isActive('heading', { level: 1 })
                                            ? 'active'
                                            : ''
                                    }
                                >
                                    Título 1
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    onClick={() =>
                                        editor
                                            .chain()
                                            .focus()
                                            .toggleHeading({ level: 2 })
                                            .run()
                                    }
                                    className={
                                        editor.isActive('heading', { level: 2 })
                                            ? 'active'
                                            : ''
                                    }
                                >
                                    Título 2
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    onClick={() =>
                                        editor
                                            .chain()
                                            .focus()
                                            .toggleHeading({ level: 3 })
                                            .run()
                                    }
                                    className={
                                        editor.isActive('heading', { level: 3 })
                                            ? 'active'
                                            : ''
                                    }
                                >
                                    Título 3
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Alinhamento */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().setTextAlign('left').run()
                        }
                        className={`btn btn-sm ${editor.isActive({ textAlign: 'left' }) ? 'btn-primary' : 'btn-ghost'}`}
                        title="Alinhar à esquerda"
                    >
                        ⬅️
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().setTextAlign('center').run()
                        }
                        className={`btn btn-sm ${editor.isActive({ textAlign: 'center' }) ? 'btn-primary' : 'btn-ghost'}`}
                        title="Centralizar"
                    >
                        ↔️
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().setTextAlign('right').run()
                        }
                        className={`btn btn-sm ${editor.isActive({ textAlign: 'right' }) ? 'btn-primary' : 'btn-ghost'}`}
                        title="Alinhar à direita"
                    >
                        ➡️
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().setTextAlign('justify').run()
                        }
                        className={`btn btn-sm ${editor.isActive({ textAlign: 'justify' }) ? 'btn-primary' : 'btn-ghost'}`}
                        title="Justificar"
                    >
                        ⬌
                    </button>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Listas */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleBulletList().run()
                        }
                        className={`btn btn-sm ${editor.isActive('bulletList') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Lista com marcadores"
                    >
                        • • •
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleOrderedList().run()
                        }
                        className={`btn btn-sm ${editor.isActive('orderedList') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Lista numerada"
                    >
                        1. 2. 3.
                    </button>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Elementos especiais */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={setLink}
                        className={`btn btn-sm ${editor.isActive('link') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Inserir link"
                    >
                        🔗
                    </button>
                    <button
                        type="button"
                        onClick={addImage}
                        className="btn btn-sm btn-ghost"
                        title="Inserir imagem"
                    >
                        🖼️
                    </button>
                    <button
                        type="button"
                        onClick={insertTable}
                        className="btn btn-sm btn-ghost"
                        title="Inserir tabela"
                    >
                        📋
                    </button>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Outros */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleBlockquote().run()
                        }
                        className={`btn btn-sm ${editor.isActive('blockquote') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Citação"
                    >
                        💬
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().toggleCodeBlock().run()
                        }
                        className={`btn btn-sm ${editor.isActive('codeBlock') ? 'btn-primary' : 'btn-ghost'}`}
                        title="Bloco de código"
                    >
                        &lt;/&gt;
                    </button>
                    <button
                        type="button"
                        onClick={() =>
                            editor.chain().focus().setHorizontalRule().run()
                        }
                        className="btn btn-sm btn-ghost"
                        title="Linha horizontal"
                    >
                        ___
                    </button>
                </div>

                <div className="divider divider-horizontal mx-0" />

                {/* Desfazer/Refazer */}
                <div className="flex gap-1">
                    <button
                        type="button"
                        onClick={() => editor.chain().focus().undo().run()}
                        disabled={!editor.can().chain().focus().undo().run()}
                        className="btn btn-sm btn-ghost"
                        title="Desfazer (Ctrl+Z)"
                    >
                        ↶
                    </button>
                    <button
                        type="button"
                        onClick={() => editor.chain().focus().redo().run()}
                        disabled={!editor.can().chain().focus().redo().run()}
                        className="btn btn-sm btn-ghost"
                        title="Refazer (Ctrl+Y)"
                    >
                        ↷
                    </button>
                </div>
            </div>

            {/* Editor */}
            <div className="border-base-300 bg-base-100 relative min-h-[300px] rounded-b-lg border border-t-0">
                <EditorContent editor={editor} className="wysiwyg-editor" />
                {editor.isEmpty && (
                    <div className="text-base-content/50 pointer-events-none absolute top-4 left-4">
                        {placeholder}
                    </div>
                )}
            </div>
        </div>
    );
};

export default RichTextEditor;
