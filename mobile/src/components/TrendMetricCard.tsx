import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { colors } from '../theme/colors';
import type { TrendMetric } from '../types/health';

const DIRECTION: Record<string, { arrow: string; color: string }> = {
  up: { arrow: '▲', color: '#B45309' },
  down: { arrow: '▼', color: '#B45309' },
  stable: { arrow: '▬', color: colors.muted },
};

export function TrendMetricCard({ metric }: { metric: TrendMetric }) {
  const dir = DIRECTION[metric.direction] ?? DIRECTION.stable;
  const sign = metric.change_pct > 0 ? '+' : '';

  return (
    <View style={styles.card}>
      <Text style={styles.label}>{metric.label}</Text>
      <View style={styles.row}>
        <Text style={styles.values}>
          {metric.first} → {metric.latest} {metric.unit}
        </Text>
        <Text style={[styles.change, { color: dir.color }]}>
          {dir.arrow} {sign}
          {metric.change_pct}%
        </Text>
      </View>
      <Text style={styles.avg}>
        média {metric.average} {metric.unit} · faixa {metric.min}–{metric.max}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: 12,
    padding: 12,
    gap: 4,
  },
  label: { fontSize: 13, fontWeight: '700', color: colors.muted },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  values: { fontSize: 16, fontWeight: '800', color: colors.text },
  change: { fontSize: 14, fontWeight: '800' },
  avg: { fontSize: 11, color: colors.muted },
});
