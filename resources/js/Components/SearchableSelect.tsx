import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { useDebounce } from 'use-debounce';
import TextInput from './TextInput';

interface SelectOption {
    id: string;
    nome: string;
    sigla: string;
}

interface SearchableSelectProps {
    apiUrl: string;
    value: SelectOption | null;
    onChange: (selected: SelectOption | null) => void;
    placeholder?: string;
}

export default function SearchableSelect({ apiUrl, value, onChange, placeholder = 'Digite para buscar...' }: SearchableSelectProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [options, setOptions] = useState<SelectOption[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [debouncedSearchTerm] = useDebounce(searchTerm, 500);
    const wrapperRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [wrapperRef]);

    useEffect(() => {
        if (debouncedSearchTerm) {
            setLoading(true);
            axios.get(`${apiUrl}?q=${debouncedSearchTerm}`)
                .then(response => {
                    setOptions(response.data);
                    setLoading(false);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    setLoading(false);
                });
        } else {
            setOptions([]);
        }
    }, [debouncedSearchTerm, apiUrl]);

    const handleSelect = (option: SelectOption) => {
        onChange(option);
        setSearchTerm(option.nome);
        setIsOpen(false);
    };

    const displayValue = value ? value.nome : searchTerm;

    return (
        <div className="relative" ref={wrapperRef}>
            <TextInput
                type="text"
                className="w-full"
                value={displayValue}
                onChange={(e) => {
                    setSearchTerm(e.target.value);
                    onChange(null); // Clear selection when user types
                    setIsOpen(true);
                }}
                onFocus={() => setIsOpen(true)}
                placeholder={placeholder}
            />
            {isOpen && (
                <div className="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                    {loading && <div className="p-2 text-center">Carregando...</div>}
                    {!loading && options.length === 0 && debouncedSearchTerm && (
                        <div className="p-2 text-center text-gray-500">Nenhum resultado encontrado.</div>
                    )}
                    <ul className="list-none p-0 m-0">
                        {options.map(option => (
                            <li
                                key={option.id}
                                className="p-2 hover:bg-base-200 cursor-pointer"
                                onClick={() => handleSelect(option)}
                            >
                                {option.nome} ({option.sigla})
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
