import axios from 'axios';

import type {
  BiomarkerInput,
  HealthRecord,
  TrendResult,
  ValidationErrorResponse,
} from '../types/health';
import { apiClient } from './apiClient';

interface Wrapped<T> {
  data: T;
}

/**
 * Thrown on a 422 so the form can map messages back to fields.
 */
export class ApiValidationError extends Error {
  constructor(
    message: string,
    public readonly fieldErrors: Record<string, string[]>,
  ) {
    super(message);
    this.name = 'ApiValidationError';
  }
}

export async function createHealthRecord(
  input: BiomarkerInput,
): Promise<HealthRecord> {
  try {
    const { data } = await apiClient.post<Wrapped<HealthRecord>>(
      '/health-records',
      input,
    );
    return data.data;
  } catch (error) {
    throw normalizeError(error);
  }
}

export async function fetchHistory(limit = 10): Promise<HealthRecord[]> {
  try {
    const { data } = await apiClient.get<Wrapped<HealthRecord[]>>(
      '/health-records',
      { params: { limit } },
    );
    return data.data;
  } catch (error) {
    throw normalizeError(error);
  }
}

export async function requestTrendAnalysis(
  recordId: number,
): Promise<TrendResult> {
  try {
    const { data } = await apiClient.post<Wrapped<TrendResult>>(
      `/health-records/${recordId}/trend-analysis`,
    );
    return data.data;
  } catch (error) {
    throw normalizeError(error);
  }
}

function normalizeError(error: unknown): Error {
  if (axios.isAxiosError(error)) {
    if (error.response?.status === 422) {
      const payload = error.response.data as ValidationErrorResponse;
      return new ApiValidationError(payload.message, payload.errors ?? {});
    }
    if (error.response) {
      return new Error(
        `Server error (${error.response.status}). Please try again.`,
      );
    }
    return new Error(
      'Could not reach the server. Check that the backend is running and EXPO_PUBLIC_API_URL is correct.',
    );
  }
  return error instanceof Error ? error : new Error('Unexpected error.');
}
