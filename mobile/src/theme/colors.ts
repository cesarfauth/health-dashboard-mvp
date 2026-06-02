import type { BiomarkerStatus } from '../types/health';

export const colors = {
  background: '#F1F5F9',
  surface: '#FFFFFF',
  primary: '#2563EB',
  primaryDark: '#1D4ED8',
  text: '#0F172A',
  muted: '#64748B',
  border: '#E2E8F0',
  danger: '#DC2626',
  white: '#FFFFFF',
};

/**
 * Semantic status colors. Mirrors App\Enums\BiomarkerStatus on the backend.
 * The API already returns a `color` per biomarker, so these are a fallback /
 * used for history accents.
 */
export const statusColors: Record<BiomarkerStatus, string> = {
  normal: '#16A34A',
  attention: '#D97706',
  alert: '#DC2626',
};

export const statusSurface: Record<BiomarkerStatus, string> = {
  normal: '#ECFDF5',
  attention: '#FFFBEB',
  alert: '#FEF2F2',
};
