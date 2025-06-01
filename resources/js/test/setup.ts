import '@testing-library/jest-dom';
import { vi } from 'vitest';

// Setup global test configuration
global.console = {
  ...console,
  // Uncomment to ignore a specific log level
  // log: jest.fn(),
  // debug: jest.fn(),
  // info: jest.fn(),
  // warn: jest.fn(),
  // error: jest.fn(),
};

// Mock InertiaJS router
const mockRouter = {
  visit: vi.fn(),
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
  delete: vi.fn(),
  reload: vi.fn(),
  replace: vi.fn(),
  remember: vi.fn(),
  restore: vi.fn(),
  on: vi.fn(),
  cancelActiveVisits: vi.fn(),
  clearHistory: vi.fn(),
};

vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react') as Record<string, unknown>;
  return {
    ...actual,
    router: mockRouter,
    usePage: vi.fn(() => ({
      props: {
        auth: {
          user: {
            id: '1',
            name: 'Test User',
            email: 'test@example.com',
          },
        },
        errors: {},
        flash: {},
      },
      url: '/test',
      component: 'Test',
      version: null,
      rememberedState: {},
      scrollRegions: [],
    })),
    useForm: vi.fn(() => ({
      data: {},
      setData: vi.fn(),
      post: vi.fn(),
      put: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
      reset: vi.fn(),
      clearErrors: vi.fn(),
      setError: vi.fn(),
      transform: vi.fn(),
      processing: false,
      errors: {},
      hasErrors: false,
      progress: null,
      wasSuccessful: false,
      recentlySuccessful: false,
      isDirty: false,
      cancel: vi.fn(),
    })),
  };
});

// Mock Inertia Head component
vi.mock('@inertiajs/react', async () => {
  const actual = await vi.importActual('@inertiajs/react') as Record<string, unknown>;
  return {
    ...actual,
    Head: ({ children }: { children: React.ReactNode }) => children,
  };
});
