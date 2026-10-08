import type { E2EConfig } from 'e2e';
import { web } from '@e2e-dev/web';
import { createOpenAI } from '@ai-sdk/openai';

const logfare = createOpenAI({
  baseURL: 'https://logfare.ai/v1',
  apiKey: process.env.LOGFARE_API_KEY ?? 'lfu_u6doQaHlmNIdQ0sUHjdWOii83F2BXUzG',
});

const app = {
  url: 'http://127.0.0.1:8000',
  command: {
    executable: 'C:\\xampp\\php\\php.exe',
    args: ['-c', 'C:\\xampp\\php\\php.ini', '-S', '127.0.0.1:8000'],
    startupTimeout: 30_000,
  },
};

export default {
  targets: [
    {
      name: 'desktop',
      engine: web({ viewport: { width: 1280, height: 800 } }),
      app,
    },
    {
      name: 'mobile',
      engine: web({ viewport: { width: 375, height: 667 } }),
      app,
    },
  ],
  agents: {
    default: {
      model: logfare('deepseek-v4.1-flash'),
      system: 'You are a meticulous QA agent testing a campus job posting platform.',
    },
  },
} satisfies E2EConfig;
