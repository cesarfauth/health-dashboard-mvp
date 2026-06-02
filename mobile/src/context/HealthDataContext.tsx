import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';

import { fetchHistory } from '../services/healthApi';
import type { HealthRecord } from '../types/health';

interface HealthDataValue {
  records: HealthRecord[];
  latest: HealthRecord | null;
  loading: boolean;
  error: string | null;
  refresh: () => Promise<void>;
}

const HealthDataContext = createContext<HealthDataValue | undefined>(undefined);

export function HealthDataProvider({ children }: { children: React.ReactNode }) {
  const [records, setRecords] = useState<HealthRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const refresh = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      setRecords(await fetchHistory(10));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to load history.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const value = useMemo<HealthDataValue>(
    () => ({
      records,
      latest: records[0] ?? null,
      loading,
      error,
      refresh,
    }),
    [records, loading, error, refresh],
  );

  return (
    <HealthDataContext.Provider value={value}>
      {children}
    </HealthDataContext.Provider>
  );
}

export function useHealthData(): HealthDataValue {
  const ctx = useContext(HealthDataContext);
  if (!ctx) {
    throw new Error('useHealthData must be used within a HealthDataProvider');
  }
  return ctx;
}
