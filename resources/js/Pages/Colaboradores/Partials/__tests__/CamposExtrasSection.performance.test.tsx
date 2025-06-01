import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { CamposExtrasSection } from '../CamposExtrasSection';

// Teste para verificar as otimizações de performance
describe('CamposExtrasSection - Performance Tests', () => {
    const mockOnChange = vi.fn();

    const defaultProps = {
        campos_extras: {},
        onCamposChange: mockOnChange,
        errors: {},
        processing: false,
        canEdit: true,
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('deve usar memoização para evitar re-renders desnecessários', () => {
        const { rerender } = render(<CamposExtrasSection {...defaultProps} />);

        // Primeira renderização com os mesmos props
        rerender(<CamposExtrasSection {...defaultProps} />);

        // Segunda renderização com props diferentes (mas que não afetam o array de campos)
        rerender(<CamposExtrasSection {...defaultProps} processing={true} />);

        // Verifica que o componente não chama desnecessariamente as funções
        expect(mockOnChange).not.toHaveBeenCalled();
    });

    it('deve manter IDs únicos dos campos através de re-renders', () => {
        const campos_extras = {
            campo1: 'valor1',
            campo2: 'valor2',
        };

        const { rerender } = render(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        // Pegar os IDs dos inputs antes do re-render
        const keyInputs = screen.getAllByPlaceholderText(
            'Ex: projeto_favorito',
        );
        const firstKeyInputId = keyInputs[0].id;

        // Re-render com os mesmos dados
        rerender(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        // Verificar que o ID permanece o mesmo
        const keyInputsAfterRerender = screen.getAllByPlaceholderText(
            'Ex: projeto_favorito',
        );
        expect(keyInputsAfterRerender[0].id).toBe(firstKeyInputId);
    });

    it('deve chamar onCamposChange apenas quando necessário', async () => {
        const user = userEvent.setup();

        render(<CamposExtrasSection {...defaultProps} />);

        const keyInput = screen.getByPlaceholderText('Ex: projeto_favorito');

        // Digitar uma chave
        await user.type(keyInput, 'teste');

        // Verificar que onCamposChange foi chamado
        expect(mockOnChange).toHaveBeenCalled();

        // Limpar mock
        vi.clearAllMocks();

        // Digitar apenas espaços (valor vazio após trim)
        await user.clear(keyInput);
        await user.type(keyInput, '   ');

        // onCamposChange deve ser chamado, mas com objeto vazio (campos vazios filtrados)
        expect(mockOnChange).toHaveBeenCalledWith({});
    });

    it('deve filtrar corretamente campos vazios', async () => {
        const user = userEvent.setup();

        render(<CamposExtrasSection {...defaultProps} />);

        // Adicionar um campo
        const addButton = screen.getByRole('button', {
            name: 'Adicionar Campo',
        });
        await user.click(addButton);

        const keyInputs = screen.getAllByPlaceholderText(
            'Ex: projeto_favorito',
        );
        const valueInputs = screen.getAllByPlaceholderText('Ex: Sistema de RH');

        // Preencher apenas o primeiro campo
        await user.type(keyInputs[0], 'campo_preenchido');
        await user.type(valueInputs[0], 'valor_preenchido');

        // Deixar o segundo campo vazio
        // (não precisa fazer nada, já está vazio)

        // Verificar que apenas o campo preenchido foi passado para onCamposChange
        const lastCall =
            mockOnChange.mock.calls[mockOnChange.mock.calls.length - 1];
        expect(lastCall[0]).toEqual({
            campo_preenchido: 'valor_preenchido',
        });
    });

    it('deve manter consistência de dados entre re-renders', () => {
        const campos_extras = {
            projeto: 'Sistema de RH',
            cliente: 'UFCG',
        };

        const { rerender } = render(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        // Verificar valores iniciais
        expect(screen.getByDisplayValue('projeto')).toBeInTheDocument();
        expect(screen.getByDisplayValue('Sistema de RH')).toBeInTheDocument();
        expect(screen.getByDisplayValue('cliente')).toBeInTheDocument();
        expect(screen.getByDisplayValue('UFCG')).toBeInTheDocument();

        // Re-render com campos extras modificados
        const novos_campos = {
            projeto: 'Sistema de RH v2',
            cliente: 'UFCG',
            status: 'ativo',
        };

        rerender(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={novos_campos}
            />,
        );

        // Verificar que os valores foram atualizados corretamente
        expect(
            screen.getByDisplayValue('Sistema de RH v2'),
        ).toBeInTheDocument();
        expect(screen.getByDisplayValue('status')).toBeInTheDocument();
        expect(screen.getByDisplayValue('ativo')).toBeInTheDocument();
    });

    it('deve lidar corretamente com mudanças de props de campos_extras', () => {
        const { rerender } = render(<CamposExtrasSection {...defaultProps} />);

        // Iniciar sem campos
        let emptyInputs = screen.getAllByDisplayValue('');
        expect(emptyInputs).toHaveLength(2); // chave e valor vazios

        // Adicionar campos via props
        const campos_extras = { teste: 'valor' };
        rerender(
            <CamposExtrasSection
                {...defaultProps}
                campos_extras={campos_extras}
            />,
        );

        // Verificar que o campo foi adicionado
        expect(screen.getByDisplayValue('teste')).toBeInTheDocument();
        expect(screen.getByDisplayValue('valor')).toBeInTheDocument();

        // Remover campos via props
        rerender(<CamposExtrasSection {...defaultProps} campos_extras={{}} />);

        // Verificar que voltou ao estado inicial
        emptyInputs = screen.getAllByDisplayValue('');
        expect(emptyInputs).toHaveLength(2);
    });
});
