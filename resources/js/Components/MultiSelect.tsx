import React, { useState, useRef, useEffect } from 'react';

interface MultiSelectProps {
    id: string;
    label: string;
    options: string[];
    value: string[];
    onChange: (value: string[]) => void;
    error?: string;
    placeholder?: string;
    className?: string;
}

export default function MultiSelect({
    id,
    label,
    options,
    value,
    onChange,
    error,
    placeholder = 'Selecione opções...',
    className = '',
}: MultiSelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const dropdownRef = useRef<HTMLDivElement>(null);

    const filteredOptions = options.filter(option =>
        option.toLowerCase().includes(searchTerm.toLowerCase()) &&
        !value.includes(option)
    );

    const handleSelect = (option: string) => {
        if (!value.includes(option)) {
            onChange([...value, option]);
        }
        setSearchTerm('');
    };

    const handleRemove = (option: string) => {
        onChange(value.filter(item => item !== option));
    };

    const handleClickOutside = (event: MouseEvent) => {
        if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
            setIsOpen(false);
            setSearchTerm('');
        }
    };

    const handleKeyDown = (event: React.KeyboardEvent) => {
        if (event.key === 'Escape') {
            setIsOpen(false);
            setSearchTerm('');
        } else if (event.key === 'Enter' || event.key === ' ') {
            // Allow opening/closing with Enter/Space only if not typing in search
            if (event.target === dropdownRef.current?.querySelector(`#${id}`)) {
                 event.preventDefault();
                 setIsOpen(!isOpen);
            }
        }
    };

    useEffect(() => {
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    return (
        <div className={`form-control w-full ${className}`}>
            <label className="label" htmlFor={id}>
                <span className="label-text">{label}</span>
            </label>
            
            <div className="relative" ref={dropdownRef}>
                {/* Select input */}
                <div
                    id={id}
                    className={`input input-bordered w-full h-12 cursor-pointer flex items-center justify-between px-3 ${
                        error ? 'input-error' : ''
                    } ${isOpen ? 'input-focus' : ''}`}
                    onClick={() => setIsOpen(!isOpen)}
                    onKeyDown={handleKeyDown}
                    tabIndex={0}
                    role="combobox"
                    aria-expanded={isOpen}
                    aria-haspopup="listbox"
                    aria-controls={`${id}-dropdown`}
                >
                    <span className="text-sm">
                        {value.length === 0 ? (
                            <span className="text-base-content/50">{placeholder}</span>
                        ) : (
                            `${value.length} ${value.length === 1 ? 'opção' : 'opções'} ${value.length === 1 ? 'selecionada' : 'selecionadas'}`
                        )}
                    </span>
                    <svg
                        className={`w-5 h-5 flex-shrink-0 transition-transform ${
                            isOpen ? 'rotate-180' : ''
                        }`}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>

                {/* Dropdown */}
                {isOpen && (
                    <div id={`${id}-dropdown`} className="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-xl max-h-72 overflow-hidden flex flex-col">
                        {/* Search input */}
                        <div className="p-2 border-b border-base-300">
                            <input
                                id={`${id}-search`}
                                type="text"
                                className="input input-sm input-bordered w-full"
                                placeholder="Buscar opções..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                onClick={(e) => e.stopPropagation()} // Prevent closing dropdown when clicking search
                                autoFocus
                                aria-controls={`${id}-options-list`}
                            />
                        </div>

                        {/* Options list */}
                        <div id={`${id}-options-list`} className="flex-grow max-h-48 overflow-y-auto">
                            {filteredOptions.length === 0 ? (
                                <div className="p-3 text-center text-base-content/60 text-sm">
                                    {searchTerm ? (
                                        <>
                                            <div className="font-medium mb-1">Nenhuma opção encontrada</div>
                                            <div className="text-xs">Tente buscar por outros termos</div>
                                        </>
                                    ) : (
                                        <>
                                            <div className="font-medium mb-1">
                                                {options.length === value.length ? "Todas as opções selecionadas" : "Nenhuma opção disponível"}
                                            </div>
                                            <div className="text-xs">
                                                {options.length === value.length ? "Remova alguma opção para ver mais." : "Verifique as opções fornecidas."}
                                            </div>
                                        </>
                                    )}
                                </div>
                            ) : (
                                filteredOptions.map((option) => (
                                    <button
                                        key={option}
                                        type="button"
                                        className="w-full text-left px-3 py-2 transition-colors border-b border-base-200 last:border-b-0 text-sm focus:outline-none hover:bg-base-200 focus:bg-base-200"
                                        onClick={() => handleSelect(option)}
                                        tabIndex={0}
                                    >
                                        <span className="font-medium">{option}</span>
                                    </button>
                                ))
                            )}
                        </div>
                        
                        {/* Footer info */}
                        {filteredOptions.length > 0 && (
                            <div className="px-3 py-1.5 bg-base-50 border-t border-base-300 text-xs text-base-content/60">
                                {`${filteredOptions.length} ${filteredOptions.length === 1 ? 'opção' : 'opções'} ${filteredOptions.length === 1 ? 'disponível' : 'disponíveis'}`}
                                {value.length > 0 && ` • ${value.length} ${value.length === 1 ? 'selecionada' : 'selecionadas'}`}
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Selected items - displayed below the select */}
            {value.length > 0 && (
                <div className="mt-2">
                    <div className="flex flex-wrap gap-2">
                        {value.map((item) => (
                            <span
                                key={item}
                                className="badge badge-primary gap-1.5 text-sm pl-2.5 pr-1 py-1 h-auto"
                            >
                                <span className="truncate max-w-[150px] sm:max-w-[200px]">{item}</span>
                                <button
                                    type="button"
                                    className="btn btn-ghost btn-xs btn-circle hover:bg-primary-focus hover:bg-opacity-30"
                                    onClick={() => handleRemove(item)}
                                    title={`Remover ${item}`}
                                    aria-label={`Remover ${item}`}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor" className="w-3.5 h-3.5">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        ))}
                    </div>
                </div>
            )}

            {error && (
                <label className="label">
                    <span className="label-text-alt text-error">{error}</span>
                </label>
            )}
        </div>
    );
}
