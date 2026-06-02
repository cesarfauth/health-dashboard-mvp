import React from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { useHealthData } from '../context/HealthDataContext';
import { colors } from '../theme/colors';
import type { HealthRecord } from '../types/health';

function HistoryRow({ record }: { record: HealthRecord }) {
  const { sleep_hours, glucose_level, hrv } = record.biomarkers;
  return (
    <View style={styles.row}>
      <Text style={styles.rowDate}>
        {new Date(record.recorded_at).toLocaleString('pt-BR')}
      </Text>
      <View style={styles.metrics}>
        {[sleep_hours, glucose_level, hrv].map((b, i) => (
          <View key={i} style={styles.metric}>
            <View style={[styles.dot, { backgroundColor: b.color }]} />
            <Text style={styles.metricText}>
              {b.label.split(' ')[0]}: {b.value}
              {b.unit}
            </Text>
          </View>
        ))}
      </View>
      {record.recommendation ? (
        <Text style={styles.summary} numberOfLines={2}>
          {record.recommendation.summary}
        </Text>
      ) : null}
    </View>
  );
}

export function HistoryScreen() {
  const { records, loading, error, refresh } = useHealthData();

  if (loading && records.length === 0) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <FlatList
      data={records}
      keyExtractor={(item) => String(item.id)}
      renderItem={({ item }) => <HistoryRow record={item} />}
      contentContainerStyle={styles.content}
      onRefresh={refresh}
      refreshing={loading}
      ListHeaderComponent={<Text style={styles.title}>Histórico</Text>}
      ListEmptyComponent={
        <Text style={styles.empty}>
          {error ?? 'Nenhuma leitura registrada ainda.'}
        </Text>
      }
    />
  );
}

const styles = StyleSheet.create({
  content: { padding: 16, gap: 12 },
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  title: { fontSize: 24, fontWeight: '800', color: colors.text, marginBottom: 4 },
  row: {
    backgroundColor: colors.surface,
    borderRadius: 14,
    padding: 14,
    gap: 8,
  },
  rowDate: { fontSize: 13, fontWeight: '700', color: colors.text },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  metric: { flexDirection: 'row', alignItems: 'center', gap: 5 },
  dot: { width: 9, height: 9, borderRadius: 999 },
  metricText: { fontSize: 12, color: colors.muted, fontWeight: '600' },
  summary: { fontSize: 12, color: colors.muted, fontStyle: 'italic' },
  empty: { textAlign: 'center', color: colors.muted, marginTop: 40 },
});
