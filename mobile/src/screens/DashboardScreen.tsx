import React from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { BiomarkerCard } from '../components/BiomarkerCard';
import { RecommendationsCard } from '../components/RecommendationsCard';
import { useHealthData } from '../context/HealthDataContext';
import { colors } from '../theme/colors';

interface Props {
  onAddPress: () => void;
}

export function DashboardScreen({ onAddPress }: Props) {
  const { latest, loading, error, refresh } = useHealthData();

  if (loading && !latest) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  if (error && !latest) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorTitle}>Couldn&apos;t load your data</Text>
        <Text style={styles.errorText}>{error}</Text>
      </View>
    );
  }

  if (!latest) {
    return (
      <View style={styles.centered}>
        <Text style={styles.emptyIcon}>📈</Text>
        <Text style={styles.errorTitle}>No readings yet</Text>
        <Text style={styles.errorText}>
          Add your first biomarker reading to see your dashboard.
        </Text>
        <Text style={styles.link} onPress={onAddPress}>
          + New reading
        </Text>
      </View>
    );
  }

  return (
    <ScrollView
      contentContainerStyle={styles.content}
      refreshControl={
        <RefreshControl refreshing={loading} onRefresh={refresh} />
      }
    >
      <Text style={styles.title}>Today&apos;s snapshot</Text>
      <Text style={styles.date}>
        {new Date(latest.recorded_at).toLocaleString()}
      </Text>

      <View style={styles.cardsRow}>
        <BiomarkerCard biomarker={latest.biomarkers.sleep_hours} />
        <BiomarkerCard biomarker={latest.biomarkers.glucose_level} />
        <BiomarkerCard biomarker={latest.biomarkers.hrv} />
      </View>

      {latest.recommendation ? (
        <RecommendationsCard recommendation={latest.recommendation} />
      ) : (
        <Text style={styles.errorText}>No recommendation attached.</Text>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  content: { padding: 16, gap: 14 },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 32,
    gap: 8,
  },
  title: { fontSize: 24, fontWeight: '800', color: colors.text },
  date: { fontSize: 13, color: colors.muted, marginTop: -10 },
  cardsRow: { flexDirection: 'row', gap: 10 },
  emptyIcon: { fontSize: 40 },
  errorTitle: { fontSize: 18, fontWeight: '800', color: colors.text },
  errorText: { fontSize: 14, color: colors.muted, textAlign: 'center' },
  link: {
    marginTop: 8,
    color: colors.primary,
    fontWeight: '800',
    fontSize: 15,
  },
});
