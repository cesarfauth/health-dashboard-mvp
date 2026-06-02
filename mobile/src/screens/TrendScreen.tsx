import React, { useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';

import { PrimaryButton } from '../components/PrimaryButton';
import { RecommendationsCard } from '../components/RecommendationsCard';
import { TrendMetricCard } from '../components/TrendMetricCard';
import { useHealthData } from '../context/HealthDataContext';
import { requestTrendAnalysis } from '../services/healthApi';
import { colors } from '../theme/colors';
import type { TrendResult } from '../types/health';

export function TrendScreen() {
  const { latest, records } = useHealthData();

  const [result, setResult] = useState<TrendResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function runAnalysis() {
    if (!latest) {
      return;
    }
    setLoading(true);
    setError(null);
    try {
      setResult(await requestTrendAnalysis(latest.id));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Falha na análise.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <ScrollView contentContainerStyle={styles.content}>
      <Text style={styles.title}>Análise de tendência</Text>
      <Text style={styles.subtitle}>
        Calculamos as variações exatas entre suas leituras recentes e deixamos a
        IA interpretar o padrão. Precisa de pelo menos 3 leituras.
      </Text>

      <PrimaryButton
        label={`Analisar ${records.length} leitura${records.length === 1 ? '' : 's'}`}
        onPress={runAnalysis}
        loading={loading}
        disabled={!latest}
      />

      {!latest ? (
        <Text style={styles.hint}>Adicione uma leitura primeiro para habilitar a análise.</Text>
      ) : null}

      {error ? <Text style={styles.error}>{error}</Text> : null}

      {result?.status === 'insufficient_data' ? (
        <View style={styles.notice}>
          <Text style={styles.noticeIcon}>📉</Text>
          <Text style={styles.noticeText}>{result.message}</Text>
        </View>
      ) : null}

      {result?.status === 'ok' ? (
        <View style={styles.results}>
          <Text style={styles.sectionLabel}>
            Calculado sobre {result.records_analyzed} leituras
          </Text>
          <TrendMetricCard metric={result.metrics.sleep_hours} />
          <TrendMetricCard metric={result.metrics.glucose_level} />
          <TrendMetricCard metric={result.metrics.hrv} />
          <RecommendationsCard recommendation={result.recommendation} />
        </View>
      ) : null}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  content: { padding: 16, gap: 14 },
  title: { fontSize: 24, fontWeight: '800', color: colors.text },
  subtitle: { fontSize: 13, color: colors.muted, lineHeight: 19 },
  hint: { fontSize: 13, color: colors.muted, textAlign: 'center' },
  error: { fontSize: 13, color: colors.danger, fontWeight: '600' },
  notice: {
    flexDirection: 'row',
    gap: 10,
    backgroundColor: '#FEF9C3',
    borderRadius: 12,
    padding: 14,
    alignItems: 'center',
  },
  noticeIcon: { fontSize: 20 },
  noticeText: { flex: 1, fontSize: 13, color: '#854D0E', lineHeight: 18 },
  results: { gap: 10 },
  sectionLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: colors.muted,
    textTransform: 'uppercase',
  },
});
