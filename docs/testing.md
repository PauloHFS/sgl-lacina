# Configuração e Guia de Testes

## Stack de Testes

- **Framework de Testes**: Vitest 3.1.4
- **Testing Library**: React Testing Library
- **Environment**: jsdom
- **Coverage**: V8 Provider
- **UI**: Vitest UI disponível

## Scripts Disponíveis

```bash
# Executar testes em modo watch
npm run test

# Executar testes uma vez
npm run test:run

# Executar testes com interface visual
npm run test:ui

# Executar testes com relatório de coverage
npm run test:coverage
```

## Estrutura de Testes

### Localização

- Testes devem ser colocados em `__tests__/` dentro da pasta do componente
- Convenção de nomenclatura: `ComponenteName.test.tsx` ou `ComponenteName.spec.tsx`

### Setup de Testes

- **Arquivo de configuração**: `vitest.config.ts`
- **Setup global**: `resources/js/test/setup.ts`
- **Mocks do Inertia.js**: Configurados automaticamente

## Cobertura de Código

### Métricas de CamposExtrasSection

- **97.35%** Statement coverage
- **91.89%** Branch coverage
- **100%** Function coverage
- **97.35%** Line coverage

### Relatórios

- **Texto**: Console output
- **JSON**: `coverage/coverage.json`
- **HTML**: `coverage/index.html`

## Mocks Disponíveis

### Inertia.js

```typescript
// Automaticamente mockado em setup.ts
import { usePage, useForm, router } from '@inertiajs/react';

// usePage retorna dados de teste padrão
// useForm retorna formulário mock com métodos
// router tem todos os métodos mockados
```

## Exemplo de Teste

### Teste Básico de Componente

```typescript
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import { MyComponent } from '../MyComponent';

describe('MyComponent', () => {
    it('deve renderizar corretamente', () => {
        render(<MyComponent />);
        expect(screen.getByText('Meu Texto')).toBeInTheDocument();
    });

    it('deve reagir a interações do usuário', async () => {
        const user = userEvent.setup();
        const mockFn = vi.fn();

        render(<MyComponent onClick={mockFn} />);

        await user.click(screen.getByRole('button'));
        expect(mockFn).toHaveBeenCalled();
    });
});
```

### Teste de Performance

```typescript
describe('MyComponent - Performance', () => {
    it('deve evitar re-renders desnecessários', () => {
        const { rerender } = render(<MyComponent prop="value" />);

        // Mesmo props não deve causar re-render
        rerender(<MyComponent prop="value" />);

        // Verificar que não houve chamadas desnecessárias
        expect(mockCallback).not.toHaveBeenCalled();
    });
});
```

## Boas Práticas

### 1. Testes Comportamentais

- Foque no comportamento do usuário, não na implementação
- Use `screen.getByRole()` quando possível
- Teste interações reais do usuário

### 2. Mocks Mínimos

- Mock apenas o necessário
- Prefira mocks ao nível de módulo
- Use `vi.clearAllMocks()` no `beforeEach`

### 3. Assertions Claras

- Use matchers específicos do jest-dom
- Mensagens de erro descritivas
- Aguarde operações assíncronas com `waitFor`

### 4. Organização

- Um `describe` por componente
- Agrupe testes relacionados
- Use `beforeEach` para setup comum

## Comandos Úteis

```bash
# Executar teste específico
npm run test -- --reporter=verbose ComponentName

# Executar testes em modo debug
npm run test -- --no-coverage --reporter=verbose

# Executar apenas testes que falharam
npm run test -- --run --reporter=verbose --rerun-failed

# Executar com threshold de coverage
npm run test:coverage -- --coverage.statements=80
```

## Resolução de Problemas

### Imports não encontrados

- Verificar aliases no `vitest.config.ts`
- Usar imports relativos se necessário

### Mocks não funcionando

- Verificar se está no arquivo `setup.ts`
- Usar `vi.mock()` no início do arquivo de teste

### Testes lentos

- Verificar se há timers ou animations
- Usar `vi.useFakeTimers()` quando apropriado

### Environment Issues

- Confirmar que está usando `jsdom`
- Verificar se dependências de DOM estão mockadas
