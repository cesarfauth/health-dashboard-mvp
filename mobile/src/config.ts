/**
 * Runtime configuration. EXPO_PUBLIC_* vars are inlined by Expo at build time.
 * See .env.example for per-platform values (simulator vs emulator vs device).
 */
export const API_BASE_URL =
  process.env.EXPO_PUBLIC_API_URL ?? 'http://localhost:9000/api';
