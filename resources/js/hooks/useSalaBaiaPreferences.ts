import { useState } from 'react';

interface SalaBaiaPreferences {
    trabalhoPresencial?: {
        salaId: string;
        baiaId: string;
    };
    trabalhoRemoto?: {
        salaId: string;
    };
}

const STORAGE_KEY = 'horarios-sala-baia-preferences';

export function useSalaBaiaPreferences() {
    const [preferences, setPreferences] = useState<SalaBaiaPreferences>(() => {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                return JSON.parse(stored);
            }
        } catch (error) {
            console.error('Erro ao carregar preferências do localStorage:', error);
        }
        return {};
    });

    const savePreferences = (newPreferences: SalaBaiaPreferences) => {
        try {
            setPreferences(newPreferences);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newPreferences));
        } catch (error) {
            console.error('Erro ao salvar preferências no localStorage:', error);
        }
    };

    const updateTrabalhoPresencial = (salaId: string, baiaId: string) => {
        const newPreferences = {
            ...preferences,
            trabalhoPresencial: { salaId, baiaId },
        };
        savePreferences(newPreferences);
    };

    const updateTrabalhoRemoto = (salaId: string) => {
        const newPreferences = {
            ...preferences,
            trabalhoRemoto: { salaId },
        };
        savePreferences(newPreferences);
    };

    return {
        preferences,
        updateTrabalhoPresencial,
        updateTrabalhoRemoto,
    };
}
