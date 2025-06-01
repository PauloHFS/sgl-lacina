import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { CamposExtrasSection } from '../CamposExtrasSection';

describe('CamposExtrasSection', () => {
    const defaultProps = {
        campos_extras: {},
        onCamposChange: vi.fn(),
        errors: {},
        processing: false,
        canEdit: true,
    };

    it('deve renderizar campos vazios inicialmente quando não há campos_extras', () => {
        render(<CamposExtrasSection {...defaultProps} />);

        // Use getAllByDisplayValue para múltiplos campos vazios
        const emptyInputs = screen.getAllByDisplayValue('');
        expect(emptyInputs).toHaveLength(2); // chave e valor
        expect(
            screen.getByPlaceholderText('Ex: projeto_favorito'),
        ).toBeInTheDocument();
        expect(
            screen.getByPlaceholderText('Ex: Sistema de RH'),
        ).toBeInTheDocument();
    });

    it('deve renderizar campos existentes quando há campos_extras', () => {
        const campos_extras = {
            projeto_favorito: 'Sistema de RH',
            experiencia: '2 anos',
        };

        render(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        expect(
            screen.getByDisplayValue('projeto_favorito'),
        ).toBeInTheDocument();
        expect(screen.getByDisplayValue('Sistema de RH')).toBeInTheDocument();
        expect(screen.getByDisplayValue('experiencia')).toBeInTheDocument();
        expect(screen.getByDisplayValue('2 anos')).toBeInTheDocument();
    });

    it('deve chamar onCamposChange quando um campo é alterado', async () => {
        const onCamposChange = vi.fn();

        render(
            <CamposExtrasSection
                {...defaultProps}
                onCamposChange={onCamposChange}
            />,
        );

        const keyInput = screen.getByPlaceholderText('Ex: projeto_favorito');
        const valueInput = screen.getByPlaceholderText('Ex: Sistema de RH');

        fireEvent.change(keyInput, { target: { value: 'teste' } });
        fireEvent.change(valueInput, { target: { value: 'valor' } });

        await waitFor(() => {
            expect(onCamposChange).toHaveBeenCalledWith({ teste: 'valor' });
        });
    });

    it('deve adicionar novo campo quando botão é clicado', () => {
        render(<CamposExtrasSection {...defaultProps} />);

        const addButton = screen.getByText('Adicionar Campo');
        fireEvent.click(addButton);

        const keyInputs = screen.getAllByPlaceholderText(
            'Ex: projeto_favorito',
        );
        expect(keyInputs).toHaveLength(2);
    });

    it('deve remover campo quando botão remover é clicado', () => {
        const campos_extras = {
            campo1: 'valor1',
            campo2: 'valor2',
        };

        render(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        // Buscar pelos botões que contêm o texto "Remover"
        const removeButtons = screen.getAllByRole('button', {
            name: 'Remover',
        });
        expect(removeButtons).toHaveLength(2);

        fireEvent.click(removeButtons[0]);

        // Verificar se onCamposChange foi chamado
        expect(defaultProps.onCamposChange).toHaveBeenCalled();
    });

    it('não deve mostrar botão remover quando há apenas um campo', () => {
        render(<CamposExtrasSection {...defaultProps} />);

        expect(screen.queryByText('Remover')).not.toBeInTheDocument();
    });

    it('deve desabilitar inputs quando canEdit é false', () => {
        render(<CamposExtrasSection {...defaultProps} canEdit={false} />);

        const keyInput = screen.getByPlaceholderText('Ex: projeto_favorito');
        const valueInput = screen.getByPlaceholderText('Ex: Sistema de RH');

        expect(keyInput).toBeDisabled();
        expect(valueInput).toBeDisabled();
        expect(screen.queryByText('Adicionar Campo')).not.toBeInTheDocument();
    });

    it('deve filtrar campos vazios ao chamar onCamposChange', async () => {
        const onCamposChange = vi.fn();

        render(
            <CamposExtrasSection
                {...defaultProps}
                onCamposChange={onCamposChange}
            />,
        );

        const keyInput = screen.getByPlaceholderText('Ex: projeto_favorito');
        const valueInput = screen.getByPlaceholderText('Ex: Sistema de RH');

        // Campo com chave vazia deve ser filtrado
        fireEvent.change(keyInput, { target: { value: '' } });
        fireEvent.change(valueInput, { target: { value: 'valor' } });

        await waitFor(() => {
            expect(onCamposChange).toHaveBeenCalledWith({});
        });

        // Campo com valor vazio deve ser filtrado
        fireEvent.change(keyInput, { target: { value: 'chave' } });
        fireEvent.change(valueInput, { target: { value: '' } });

        await waitFor(() => {
            expect(onCamposChange).toHaveBeenCalledWith({});
        });
    });

    it('não deve causar re-renders desnecessários quando props não mudam', () => {
        const { rerender } = render(<CamposExtrasSection {...defaultProps} />);

        const initialKeyInput = screen.getByPlaceholderText(
            'Ex: projeto_favorito',
        );

        // Re-render com as mesmas props
        rerender(<CamposExtrasSection {...defaultProps} />);

        const afterKeyInput = screen.getByPlaceholderText(
            'Ex: projeto_favorito',
        );

        // Os elementos devem ser os mesmos (React otimização)
        expect(initialKeyInput).toBe(afterKeyInput);
    });
});
