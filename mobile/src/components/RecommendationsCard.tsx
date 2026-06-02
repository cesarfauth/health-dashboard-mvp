import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { colors } from '../theme/colors';
import type { AiRecommendation } from '../types/health';
import { DisclaimerBanner } from './DisclaimerBanner';

const CATEGORY_ICONS: Record<string, string> = {
  sleep: '🌙',
  nutrition: '🥗',
  activity: '🚶',
  stress: '🧘',
  general: '✅',
};

export function RecommendationsCard({
  recommendation,
}: {
  recommendation: AiRecommendation;
}) {
  const isFallback = recommendation.source === 'fallback';

  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <Text style={styles.title}>Recomendações da IA</Text>
        <View
          style={[
            styles.sourceBadge,
            { backgroundColor: isFallback ? '#FEF3C7' : '#DBEAFE' },
          ]}
        >
          <Text
            style={[
              styles.sourceText,
              { color: isFallback ? '#92400E' : '#1E40AF' },
            ]}
          >
            {isFallback ? 'Base offline' : `IA · ${recommendation.model ?? 'modelo'}`}
          </Text>
        </View>
      </View>

      <Text style={styles.summary}>{recommendation.summary}</Text>

      {recommendation.recommendations.map((rec, index) => (
        <View key={index} style={styles.item}>
          <Text style={styles.itemIcon}>
            {CATEGORY_ICONS[rec.category] ?? '•'}
          </Text>
          <View style={styles.itemBody}>
            <Text style={styles.itemTitle}>{rec.title}</Text>
            <Text style={styles.itemDetail}>{rec.detail}</Text>
          </View>
        </View>
      ))}

      <DisclaimerBanner text={recommendation.disclaimer} />
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: colors.surface,
    borderRadius: 16,
    padding: 16,
    gap: 12,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  title: { fontSize: 17, fontWeight: '800', color: colors.text },
  sourceBadge: { borderRadius: 999, paddingHorizontal: 10, paddingVertical: 3 },
  sourceText: { fontSize: 11, fontWeight: '700' },
  summary: { fontSize: 14, color: colors.text, lineHeight: 20 },
  item: { flexDirection: 'row', gap: 10, alignItems: 'flex-start' },
  itemIcon: { fontSize: 18, width: 24, textAlign: 'center' },
  itemBody: { flex: 1, gap: 2 },
  itemTitle: { fontSize: 14, fontWeight: '700', color: colors.text },
  itemDetail: { fontSize: 13, color: colors.muted, lineHeight: 18 },
});
