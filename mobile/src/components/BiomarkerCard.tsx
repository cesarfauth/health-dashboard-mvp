import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { colors } from '../theme/colors';
import type { Biomarker } from '../types/health';

const ICONS: Record<string, string> = {
  Sleep: '🌙',
  'Glucose (fasting)': '🩸',
  HRV: '❤️',
};

export function BiomarkerCard({ biomarker }: { biomarker: Biomarker }) {
  return (
    <View style={[styles.card, { borderLeftColor: biomarker.color }]}>
      <Text style={styles.icon}>{ICONS[biomarker.label] ?? '📊'}</Text>
      <Text style={styles.label}>{biomarker.label}</Text>
      <Text style={styles.value}>
        {biomarker.value}
        <Text style={styles.unit}> {biomarker.unit}</Text>
      </Text>
      <View style={[styles.pill, { backgroundColor: biomarker.color }]}>
        <Text style={styles.pillText}>{biomarker.status_label}</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    flex: 1,
    minWidth: 100,
    backgroundColor: colors.surface,
    borderRadius: 14,
    borderLeftWidth: 5,
    padding: 14,
    gap: 4,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  icon: { fontSize: 20 },
  label: { fontSize: 13, color: colors.muted, fontWeight: '600' },
  value: { fontSize: 24, fontWeight: '800', color: colors.text },
  unit: { fontSize: 13, fontWeight: '600', color: colors.muted },
  pill: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 2,
    marginTop: 4,
  },
  pillText: { color: colors.white, fontSize: 11, fontWeight: '700' },
});
