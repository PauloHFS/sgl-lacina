import { useCallback, useState } from 'react';

const PROJETO_STORAGE_KEY = 'horario_projeto_preferences';

interface ProjetoPreferences {
    ultimoProjetoSelecionado?: string;
}

interface UseProjetoPreferencesReturn {
    preferences: ProjetoPreferences;
    updateUltimoProjetoSelecionado: (projetoId: string) => void;
    getUltimoProjetoSelecionado: () => string | undefined;
}

export function useProjetoPreferences(): UseProjetoPreferencesReturn {
    const [preferences, setPreferences] = useState<ProjetoPreferences>(() => {
        try {
            const storedPreferences = localStorage.getItem(PROJETO_STORAGE_KEY);
            if (storedPreferences) {
                return JSON.parse(storedPreferences);
            }
        } catch (error) {
            console.warn('Erro ao carregar preferências de projeto:', error);
        }
        return {};
    });

    // Função para atualizar e salvar preferências
    const updatePreferences = useCallback((newPreferences: ProjetoPreferences) => {
        setPreferences(newPreferences);
        try {
            localStorage.setItem(PROJETO_STORAGE_KEY, JSON.stringify(newPreferences));
        } catch (error) {
            console.warn('Erro ao salvar preferências de projeto:', error);
        }
    }, []);

    const updateUltimoProjetoSelecionado = useCallback((projetoId: string) => {
        const newPreferences = {
            ...preferences,
            ultimoProjetoSelecionado: projetoId,
        };
        updatePreferences(newPreferences);
    }, [preferences, updatePreferences]);

    const getUltimoProjetoSelecionado = useCallback(() => {
        return preferences.ultimoProjetoSelecionado;
    }, [preferences]);

    return {
        preferences,
        updateUltimoProjetoSelecionado,
        getUltimoProjetoSelecionado,
    };
}
