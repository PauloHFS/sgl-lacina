import InputError from '@/Components/InputError'; // Adicionado import
import React, {
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';

function useClickOutside(
    ref: React.RefObject<HTMLElement>,
    handler: () => void,
) {
    useEffect(() => {
        const listener = (event: MouseEvent | TouchEvent) => {
            if (!ref.current || ref.current.contains(event.target as Node)) {
                return;
            }
            handler();
        };
        document.addEventListener('mousedown', listener);
        document.addEventListener('touchstart', listener);
        return () => {
            document.removeEventListener('mousedown', listener);
            document.removeEventListener('touchstart', listener);
        };
    }, [ref, handler]);
}

const pluralize = (count: number, singular: string, plural: string) =>
    count === 1 ? singular : plural;

interface MultiSelectProps {
    id: string;
    label: string;
    options: readonly string[]; // Usar readonly para imutabilidade
    value: string[];
    onChange: (value: string[]) => void;
    error?: string;
    placeholder?: string;
    className?: string;
    maxSelections?: number;
}

export default function MultiSelect({
    id,
    label,
    options,
    value,
    onChange,
    error,
    placeholder = 'Selecione...',
    className = '',
    maxSelections,
}: MultiSelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [activeIndex, setActiveIndex] = useState(-1);

    const dropdownRef = useRef<HTMLDivElement>(null);
    const searchInputRef = useRef<HTMLInputElement>(null);
    const optionsListRef = useRef<HTMLUListElement>(null);

    useClickOutside(dropdownRef, () => setIsOpen(false));

    const isMaxedOut = useMemo(
        () => maxSelections !== undefined && value.length >= maxSelections,
        [value, maxSelections],
    );

    const filteredOptions = useMemo(
        () =>
            options.filter(
                (option) =>
                    option.toLowerCase().includes(searchTerm.toLowerCase()) &&
                    !value.includes(option),
            ),
        [options, searchTerm, value],
    );

    useEffect(() => {
        if (activeIndex >= 0 && optionsListRef.current) {
            const activeItem = optionsListRef.current.children[
                activeIndex
            ] as HTMLLIElement;
            activeItem?.scrollIntoView({ block: 'nearest' });
        }
    }, [activeIndex]);

    const handleSelect = useCallback(
        (option: string) => {
            if (!value.includes(option) && !isMaxedOut) {
                onChange([...value, option]);
                setSearchTerm('');
                setActiveIndex(-1);
                searchInputRef.current?.focus();
            }
        },
        [value, isMaxedOut, onChange],
    );

    const handleRemove = useCallback(
        (option: string) => {
            onChange(value.filter((item) => item !== option));
        },
        [value, onChange],
    );

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                setActiveIndex((prev) =>
                    Math.min(prev + 1, filteredOptions.length - 1),
                );
                break;
            case 'ArrowUp':
                e.preventDefault();
                setActiveIndex((prev) => Math.max(prev - 1, 0));
                break;
            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0 && filteredOptions[activeIndex]) {
                    handleSelect(filteredOptions[activeIndex]);
                }
                break;
            case 'Escape':
                setIsOpen(false);
                setActiveIndex(-1);
                break;
            case 'Backspace':
                if (searchTerm === '' && value.length > 0) {
                    handleRemove(value[value.length - 1]);
                }
                break;
        }
    };

    return (
        <div className={`form-control w-full ${className}`}>
            <label className="label" htmlFor={id}>
                <span className="label-text">{label}</span>
            </label>

            <div className="relative" ref={dropdownRef}>
                <div
                    className={`input input-bordered flex min-h-12 w-full flex-wrap items-center gap-2 p-2 pr-10 ${
                        error ? 'input-error' : ''
                    } ${isOpen ? 'input-focus' : ''}`}
                    onClick={() => {
                        setIsOpen(true);
                        searchInputRef.current?.focus();
                    }}
                >
                    {value.map((item) => (
                        <span
                            key={item}
                            className="badge badge-primary gap-1.5 pr-1 pl-2.5 whitespace-nowrap"
                        >
                            {item}
                            <button
                                type="button"
                                className="btn btn-ghost btn-xs btn-circle hover:bg-primary-focus/30"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleRemove(item);
                                }}
                                aria-label={`Remover ${item}`}
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth={2}
                                    stroke="currentColor"
                                    className="h-3.5 w-3.5"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </span>
                    ))}
                    <input
                        ref={searchInputRef}
                        id={id}
                        type="text"
                        className="input-ghost flex-grow bg-transparent text-sm outline-none"
                        placeholder={value.length === 0 ? placeholder : ''}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        onFocus={() => setIsOpen(true)}
                        onKeyDown={handleKeyDown}
                        disabled={isMaxedOut && searchTerm === ''}
                    />
                    <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg
                            className={`h-5 w-5 flex-shrink-0 transition-transform ${isOpen ? 'rotate-180' : ''}`}
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
                </div>

                {isOpen && (
                    <div className="border-base-300 bg-base-100 absolute z-50 mt-1 flex w-full flex-col overflow-hidden rounded-lg border shadow-xl">
                        <ul
                            ref={optionsListRef}
                            role="listbox"
                            className="max-h-56 overflow-y-auto"
                        >
                            {isMaxedOut && (
                                <li className="text-base-content/60 px-3 py-2 text-center text-sm">
                                    Limite de {maxSelections}{' '}
                                    {pluralize(
                                        maxSelections!,
                                        'seleção',
                                        'seleções',
                                    )}{' '}
                                    atingido.
                                </li>
                            )}
                            {!isMaxedOut && filteredOptions.length === 0 && (
                                <li className="text-base-content/60 px-3 py-2 text-center text-sm">
                                    {searchTerm
                                        ? 'Nenhuma opção encontrada'
                                        : 'Nenhuma opção disponível'}
                                </li>
                            )}
                            {!isMaxedOut &&
                                filteredOptions.map((option, index) => (
                                    <li
                                        key={option}
                                        role="option"
                                        aria-selected={activeIndex === index}
                                        id={`${id}-option-${index}`}
                                        className={`cursor-pointer px-3 py-2 text-sm transition-colors ${
                                            activeIndex === index
                                                ? 'bg-base-200'
                                                : 'hover:bg-base-200/50'
                                        }`}
                                        onClick={() => handleSelect(option)}
                                        onMouseEnter={() =>
                                            setActiveIndex(index)
                                        }
                                    >
                                        {option}
                                    </li>
                                ))}
                        </ul>
                    </div>
                )}
            </div>

            <InputError message={error} className="mt-2" />
        </div>
    );
}
